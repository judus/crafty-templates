<?php

namespace Maduser\Craft\CraftyTemplates;

use Craft;
use craft\base\Component;

class TemplatePathResolution implements ResolutionInterface
{
    private $hints = [];
    private $resolved = null;
    private $fallbackDirs = [];
    private $twig;

    public function __construct()
    {
        $this->fallbackDirs = array_keys(Plugin::$plugin->getConfig()['templateRoots']);
        $this->twig = Craft::$app->view->getTwig();
    }

    public function resolve(array $templatePaths = []): object
    {
        foreach ($templatePaths as $originalPath) {
            $this->checkPath($originalPath);

            foreach ($this->fallbackDirs as $dir) {
                $this->checkPath($dir . '/' . $originalPath, true);
            }
        }

        return $this->createResolutionResult();
    }

    private function checkPath($path, $isFallback = false): void
    {
        $check = false;

        if (!$this->resolved && $this->twig->getLoader()->exists($path)) {
            $this->resolved = $path;
            $check = true;
        }

        $this->recordHint($path, $check, $isFallback);
    }

    private function recordHint($path, $check, $isFallback): void
    {
        $prefix = $isFallback ? $path : str_replace(Craft::getAlias('@root'), '',
                Craft::getAlias('@templates')) . "/" . $path;
        $this->hints[] = "<!-- [" . ($check ? 'X' : ' ') . "] " . $prefix . " -->";
    }

    private function createResolutionResult(): object
    {
        $resolution = new class {
            public ?string $resolved = '';
            public string $hints = '';

            public function __toString(): string
            {
                return $this->resolved;
            }
        };

        $resolution->hints = implode("\n", $this->hints);
        $resolution->resolved = $this->resolved ?? '';

        return $resolution;
    }
}
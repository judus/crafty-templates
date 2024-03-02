<?php

namespace Maduser\Craft\CraftyTemplates;

use Craft;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;

class TwigExtensions extends AbstractExtension implements ExtensionInterface
{
    /**
     * Get a list of custom Twig functions.
     *
     * @return array An array of Twig functions.
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('theme', [$this, 'theme']),
            new TwigFunction('resolve', [$this, 'resolve']),
            new TwigFunction('decamelize', [$this, 'decamelize']),
            new TwigFunction('mergeRecursive', [$this, 'mergeRecursive']),
        ];
    }

    /**
     * Generate theme paths for a given template path.
     *
     * @param string|null $path The template path.
     *
     * @return array An array of theme paths.
     */
    public function theme(string $path = null): array
    {
        $paths = [];

        $fallbackDirs = array_keys(Craft::$app->config->getConfigFromFile('crafty')['templateRoots']);

        $paths[] = $path;

        foreach ($fallbackDirs as $root) {
            $paths[] = $root. '/' . $path;
        }

        return $paths;
    }

    /**
     * Convert a string from camel case to kebab case.
     *
     * @param string|null $string The input string.
     *
     * @return string|null The converted string in kebab case.
     */
    public function decamelize(string $string = null): ?string
    {
        if (!$string) return null;

        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^-])([A-Z][a-z])/'], '$1-$2', $string));
    }

    public function mergeRecursive(array $attr1, array $attr2): array
    {
        return array_merge_recursive($attr1, $attr2);
    }

    /**
     * Resolve a template path using hints and fallback directories.
     *
     * @param array $templatePaths An array of template paths to check.
     */
    public function resolve(array $templatePaths = [])
    {
        $hints = [];
        $resolved = null;
        $fallbackDirs = array_keys(Craft::$app->config->getConfigFromFile('crafty')['templateRoots']);

        $twig = Craft::$app->view->getTwig();

        foreach ($templatePaths as $originalPath) { // Use $originalPath to keep original $path intact
            $check = false;

            if (!$resolved && $twig->getLoader()->exists($originalPath)) {
                $resolved = $originalPath;
                $check = true;
            }

            $hints[] = "<!-- [" . ($check ? 'X' : ' ') . "] " . str_replace(Craft::getAlias('@root'), '', Craft::getAlias('@templates'))."/" . $originalPath . " -->";

            foreach ($fallbackDirs as $dir) {
                $check = false;
                $modifiedPath = $dir . '/' . $originalPath; // Use a modified path variable

                if (!$resolved && $twig->getLoader()->exists($modifiedPath)) {
                    $resolved = $modifiedPath;
                    $check = true;
                }
                $hints[] = "<!-- [" . ($check ? 'X' : ' ') . "] " . $modifiedPath . " -->";
            }
        }

        $resolution = new class {
            public ?string $resolved = '';
            public string $hints = '';

            public function __toString(): string
            {
                return $this->resolved;
            }
        };

        $resolution->hints = implode("\n", $hints);
        $resolution->resolved = $resolved ?? '';

        return $resolution;
    }
}
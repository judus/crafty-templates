<?php

namespace Maduser\Craft\CraftyTemplates;

use Craft;
use craft\base\Component;
use craft\helpers\StringHelper;
use yii\caching\TagDependency;

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

    public static function invalidateCache(): void
    {
        // Specify the tag associated with the cache entries you want to invalidate
        $cacheTag = 'templateResolution';

        // Invalidate all cache entries tagged with 'templateResolution'
        TagDependency::invalidate(Craft::$app->cache, $cacheTag);

        // Optionally, log or perform additional actions upon cache invalidation
        Craft::info('Template path resolution cache invalidated.', __METHOD__);
    }

    public function resolve2(array $templatePaths = []): object
    {
        foreach ($templatePaths as $originalPath) {
            $this->checkPath($originalPath);

            foreach ($this->fallbackDirs as $dir) {
                $this->checkPath($dir . '/' . $originalPath, true);
            }
        }

        return $this->createResolutionResult();
    }

    public function resolve(array $templatePaths = []): array
    {
        $cacheKey = $this->generateCacheKey($templatePaths);
        $result = $this->getCachedResult($cacheKey);

        if ($result === null) {
            $result = $this->performResolution($templatePaths);
            $this->cacheResult($cacheKey, $result);
        }

        return $result;
    }

    private function generateCacheKey(array $templatePaths): string
    {
        return 'templatePathResolution_' . md5(implode('|', $templatePaths));
    }

    private function getCachedResult(string $cacheKey): ?array
    {
        return Craft::$app->cache->get($cacheKey) ?: null;
    }

    private function performResolution(array $templatePaths): array
    {
        foreach ($templatePaths as $originalPath) {
            $this->checkPath($originalPath);

            foreach ($this->fallbackDirs as $dir) {
                $this->checkPath($dir . '/' . $originalPath, true);
            }
        }

        return $this->createResolutionResult();
    }

    private function cacheResult(string $cacheKey, array $result): void
    {
        $cacheTag = 'templateResolution';
        Craft::$app->cache->set($cacheKey, $result, null, new TagDependency(['tags' => $cacheTag]));
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

    private function createResolutionResult(): array
    {
        return [
            'resolved' => $this->resolved ?? '',
            'hints' => implode("\n", $this->hints),
        ];
    }
}
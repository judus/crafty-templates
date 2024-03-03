<?php

namespace Maduser\Craft\CraftyTemplates;

use Craft;
use craft\helpers\ArrayHelper;
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

        $fallbackDirs = array_keys(Plugin::$plugin->getConfig()['templateRoots']);

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

    /**
     * @param array $attr1
     * @param array $attr2
     *
     * @return array
     */
    public function mergeRecursive(array $attr1, array $attr2): array
    {
        return ArrayHelper::merge($attr1, $attr2);
    }

    /**
     * Resolve a template path using hints and fallback directories.
     *
     * @param array $templatePaths An array of template paths to check.
     */
    public function resolve(array $templatePaths = []): object
    {
        return (new TemplatePathResolution())->resolve($templatePaths);
    }
}
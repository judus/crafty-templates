<?php
namespace Maduser\Craft\CraftyTemplates;

use Craft;
use craft\events\TemplateEvent;
use Illuminate\Support\Str;
use Twig\Error\RuntimeError;

/**
 * @author    Julien Duseyau
 * @package   ThemeHooks
 * @since     1.0.0
 */
class TemplateHooks
{
    private static string $hookFile;

    private static string $prefix;

    /**
     * @return bool
     * @throws RuntimeError
     */
    public static function isValidRequest(): bool
    {
        if (empty(self::$prefix)) {
            throw new RuntimeError('Hook prefix is not set');
        }

        if (empty(self::$hookFile)) {
            throw new RuntimeError('Hook file path is not set');
        }

        return
            Craft::$app->request->isSiteRequest && self::hasHookFile();
    }

    /**
     * @param string|null $prefix
     *
     * @return string
     */
    public static function prefix(string $prefix = null): string
    {
        $prefix && self::$prefix = Str::slug($prefix, '_');

        return self::$prefix;
    }

    /**
     * @param string|null $path
     *
     * @return string
     */
    public static function hookFile(string $path = null): string
    {
        $path && self::$hookFile = Craft::getAlias($path);

        return self::$hookFile;
    }

    /**
     * @param TemplateEvent $event
     *
     * @return void
     */
    public static function handle(TemplateEvent $event): void
    {
        self::loadHookFile(self::$hookFile);
        self::preRenderDefaults($event);
        self::preRenderCustom($event);
    }

    /**
     * Load the hook file if it exists
     *
     * @return bool
     */
    protected static function hasHookFile(): bool
    {
        return file_exists(self::$hookFile);
    }

    /**
     * Load the hook file if it exists
     *
     * @param string $path
     *
     * @return void
     */
    protected static function loadHookFile(string $path): void
    {
        require_once $path;
    }

    /**
     * Calls function (alias)@projectHandle() from the hook file
     *
     * @param TemplateEvent $event
     */
    protected static function preRenderDefaults(TemplateEvent $event): void
    {
        $function = rtrim(self::$prefix, '_');

        if (function_exists($function)) {
            $variables = $event->variables;
            call_user_func_array($function, [&$variables, $event]);
            $event->variables = $variables;
        }
    }

    /**
     * Calls function (alias)@projectHandle[element]() from the hook file
     *
     * @param TemplateEvent $event
     */
    protected static function preRenderCustom(TemplateEvent $event): void
    {
        $hook = self::getHookName($event->template);
        if (function_exists($hook)) {
            $variables = $event->variables;
            call_user_func_array($hook, [&$variables, $event]);
            $event->variables = $variables;
        }
    }

    /**
     * Get the corresponding hook function name from the template name
     *
     * @param string $templateName
     *
     * @return string
     */
    protected static function getHookName(
        string $templateName
    ): string {
        return self::$prefix . Str::slug(
            Str::replaceLast('.twig', '', $templateName), '_'
        );
    }
}

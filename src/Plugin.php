<?php

namespace Maduser\Craft\CraftyTemplates;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\console\Application;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\TemplateEvent;
use craft\helpers\ArrayHelper;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use Maduser\Craft\CraftyTemplates\Console\Controllers\CraftyController;
use Twig\Error\RuntimeError;
use yii\base\Application as ApplicationAlias;
use yii\base\Event;

class Plugin extends BasePlugin
{
    public array $config = [];

    /**
     * @var Plugin
     */
    public static Plugin $plugin;

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool
     */
    public bool $hasCpSettings = false;

    /**
     * @var bool
     */
    public bool $hasCpSection = false;

    /**
     * @inheritdoc
     * @throws RuntimeError
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        $this->mergeDefaultAndUserConfig();

        Craft::setAlias('@crafty-assets', dirname(__DIR__) . '/assets');
        Craft::setAlias('@crafty-templates', dirname(__DIR__) . '/assets/templates');

        // Register the default path resolver
        Craft::$app->setComponents([
            'pathResolver' => [
                'class' => TemplatePathResolution::class,
            ],
        ]);

        $this->registerTwigExtension();
        $this->registerConsoleCommands();
        $this->registerPluginTemplateRoots();
        $this->registerTemplateHooksService();
        $this->registerOurVariable();
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Merge default and user config
     *
     * @return void
     */
    protected function mergeDefaultAndUserConfig(): void
    {

        $defaultConfig = require __DIR__ . '/config.php';
        $userConfig = Craft::$app->getConfig()->getConfigFromFile('crafty');
        $this->config = ArrayHelper::merge($defaultConfig, $userConfig);
    }

    /**
     * Register Twig extensions
     *
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    protected function registerTwigExtension(): void
    {
        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            $pathResolver = Craft::$app->get('pathResolver');
            $twigExtension = new TwigExtensions($pathResolver);
            Craft::$app->getView()->registerTwigExtension($twigExtension);
        }

    }

    /**
     * Register console commands
     *
     * @return void
     */
    protected function registerConsoleCommands(): void
    {
        Event::on(
            Application::class,
            ApplicationAlias::EVENT_BEFORE_REQUEST,
            function () {
                if (Craft::$app instanceof Application) {
                    Craft::$app->controllerMap['crafty'] = [
                        'class' => CraftyController::class,
                    ];
                }
            }
        );
    }

    /**
     * Register plugin template roots
     *
     * @return void
     */
    protected function registerPluginTemplateRoots(): void
    {
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $event) {
                foreach ($this->config['templateRoots'] as $key => $path) {
                    $event->roots[$key] = Craft::getAlias($path);
                }
            }
        );
    }

    /**
     * @return void
     * @throws RuntimeError
     */
    protected function registerTemplateHooksService(): void
    {
        // Register TemplateHooks service
        if (Craft::$app->config->custom->enablePreRenderHooks) {
            TemplateHooks::prefix('hook_');
            TemplateHooks::hookFile('@templates/hooks.php');
            if (TemplateHooks::isValidRequest()) {
                Event::on(
                    View::class,
                    View::EVENT_BEFORE_RENDER_TEMPLATE,
                    function (TemplateEvent $event) {
                        TemplateHooks::handle($event);
                    }
                );
            }
        }
    }

    /**
     * @return void
     */
    protected function registerOurVariable(): void
    {
        // Register our Variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('crafty', Variable::class);
            }
        );
    }

}
<?php

namespace Maduser\Craft\CraftyTemplates;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\console\Application;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\TemplateEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use Maduser\Craft\CraftyTemplates\Console\Controllers\CraftyController;
use Twig\Error\RuntimeError;
use yii\base\Application as ApplicationAlias;
use yii\base\Event;

class Plugin extends BasePlugin
{
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

        Craft::setAlias('@crafty-assets', dirname(__DIR__) . '/assets');
        Craft::setAlias('@crafty-templates', dirname(__DIR__) . '/assets/templates');

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

        // Register Twig extensions
        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            Craft::$app->getView()->registerTwigExtension(new TwigExtensions());
        }

        // Register plugin template roots
        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $event) {
                $event->roots['crafty-stk'] = Craft::getAlias('@root/templates/crafty-stk');
                $event->roots['crafty'] = Craft::getAlias('@crafty-templates');
            }
        );

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

        // Register our Variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('module', Variable::class);
            }
        );
    }

}
<?php

namespace Maduser\Craft\CraftyTemplates\Console\Controllers;

use Craft;
use craft\helpers\Console;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;


class CraftyController extends Controller
{

    public function actionDeploy()
    {
        $sourcePath = Craft::getAlias('@crafty-templates');
        $targetPath = Craft::getAlias('@root/templates/crafty');
        $this->copy($sourcePath, $targetPath);

        return ExitCode::OK;
    }

    private function copy($source, $destination)
    {
        $dir = opendir($source);
        @mkdir($destination);

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($source . '/' . $file)) {
                    $this->copy($source . '/' . $file, $destination . '/' . $file);
                } else {
                    $copyResult = copy($source . '/' . $file, $destination . '/' . $file);

                    $shortSource = str_replace(Craft::getAlias('@root'), '', $source);
                    $shortDestination = str_replace(Craft::getAlias('@root'), '', $destination);

                    if ($copyResult) {
                        $this->stdout("Successfully copied {$shortSource}/{$file} to {$shortDestination}/{$file}\n",
                            BaseConsole::FG_GREEN);
                    } else {
                        $this->stderr("Failed to copy {$shortSource}/{$file}\n", BaseConsole::FG_RED);
                    }
                }
            }
        }
        closedir($dir);
    }
}

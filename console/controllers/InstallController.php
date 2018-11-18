<?php

namespace console\controllers;

use yii\console\Controller;
use yii2tech\crontab\CronJob;
use yii2tech\crontab\CronTab;
use Yii;

class InstallController extends Controller
{
    /**
     * Запуск разбора логов с параметрами из settings.php.
     */
    public function actionRun()
    {
        $cronCommand = self::cronCommand();

        $cronJob = new CronJob();
        $cronJob->min = Yii::$app->params['parsePeriod']['min'];
        $cronJob->hour = Yii::$app->params['parsePeriod']['hour'];
        $cronJob->day = Yii::$app->params['parsePeriod']['dayMonth'];
        $cronJob->month = Yii::$app->params['parsePeriod']['month'];
        $cronJob->weekDay = Yii::$app->params['parsePeriod']['weekDay'];
        $cronJob->command = $cronCommand;

        $cronTab = new CronTab();
        $cronTab->mergeFilter = $cronCommand;
        $cronTab->setJobs([
            $cronJob
        ]);
        $cronTab->apply();
    }

    /**
     * Остановка разбора логов.
     */
    public function actionStop()
    {
        $cronCommand = self::cronCommand();
        $cronTab = new CronTab();
        $cronTab->mergeFilter = $cronCommand;
        $cronTab->apply();
    }

    /**
     * Команда для крона.
     */
    public static function cronCommand()
    {
        $webroot = Yii::getAlias('@webroot');
        $command = 'cd %s && php yii log/logs-handler';
        return sprintf($command, $webroot);
    }
}
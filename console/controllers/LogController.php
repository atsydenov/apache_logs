<?php
namespace console\controllers;

use common\models\Log;
use DateTime;
use DateTimeZone;
use Yii;
use yii\console\Controller;
use yii\helpers\FileHelper;

class LogController extends Controller
{
    CONST FORMAT_LOGS_COMBINED = 'combined';
    CONST FORMAT_LOGS_COMMON = 'common';

    CONST PATTERN_COMBINED = '#(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")#';
    CONST PATTERN_COMMON = '#(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+)#"';

    /**
     * Парсинг и запись в БД всех лог файлов, указанных в settings.php.
     * Скрипт запускается по крону, частота запуска также указывается в settings.php.
     */
	public function actionParse()
	{
	    $paths = Yii::$app->params['logs'];
	    $fileMask = Yii::$app->params['fileMask'];

	    $pattern = self::getPattern();

        foreach ($paths as $path) {
            $file = FileHelper::findFiles($path, [
                'only' => [ $fileMask ]
            ]);

            $handle = @fopen($file[0], 'r');

            if ($handle) {
                while (($line = fgets($handle, 4096)) !== false) {
                    preg_match_all($pattern, $line, $matches);

                    /*
                     * $matches[1][0] - IP
                     * $matches[7][0] - Request type (GET, POST, etc.)
                     * $matches[8][0] - URL
                     * $matches[10][0] - Response code
                     * $matches[11][0] - Bytes
                     * $matches[12][0] - Referrer
                     * $matches[13][0] - User agent
                     */

                    $date = self::timeTransform($matches);

                    if (!self::logIsExists($matches)) {
                        $log = new Log();
                        $log->ip = substr($matches[1][0], 0, 15);
                        $log->time = intval($date->format('U'));
                        $log->method = substr($matches[7][0],0,10);
                        $log->url = substr($matches[8][0],0,255);
                        $log->response = intval($matches[10][0]);
                        $log->byte = intval($matches[11][0]);
                        $log->referrer = substr($matches[12][0], 0,255);
                        $log->user_agent = substr($matches[13][0], 0, 255);
                        if (!$log->save()) {
                            echo 'Error: log not saved!' . "\n";
                        }
                    }
                }
                if (!feof($handle)) {
                    echo 'Error: unexpected fgets() fail.' . "\n";
                }
                fclose($handle);
            }
        }
	}

    /**
     * @return string pattern
     *
     * Возвращает регулярное выражение в зависимости от выбора формата логов.
     */
	public static function getPattern()
    {
        $formatLogs = Yii::$app->params['formatLogs'];
        $pattern = ($formatLogs == self::FORMAT_LOGS_COMBINED) ? self::PATTERN_COMBINED : self::PATTERN_COMMON;
        return $pattern;
    }

    /**
     * @param $matches array
     * @return boolean
     *
     * Проверка существования лога в БД.
     */
    public static function logIsExists($matches)
    {
        /*
         * $matches - see above
         */

        $date = self::timeTransform($matches);
        $logExists = Log::find()
            ->where([
                'ip' => $matches[1][0],
                'time' => intval($date->format('U')),
                'method' => $matches[7][0],
                'url' => $matches[8][0],
                'response' => intval($matches[10][0]),
                'byte' => intval($matches[11][0]),
                'referrer' => $matches[12][0],
                'user_agent' => $matches[13][0],
            ])->exists();
        return (bool) $logExists;
    }

    /**
     * @param $matches array
     * @return DateTime
     *
     * Преобразуем время в объект DateTime для удобства работы.
     */
    public static function timeTransform($matches)
    {
        /*
         * $matches[6][0] - Timezone
         * $matches[4][0] - Date
         * $matches[5][0] - Time
         */

        $timezone = new DateTimeZone($matches[6][0]);
        $date = DateTime::createFromFormat('d/M/Y H:i:s', implode(' ', [$matches[4][0], $matches[5][0]]), $timezone);
        return $date;
    }

    public static function writeParseLog(array $matches = [])
    {
        if (!empty($matches)) {

        } else {
            $out = 'Error: unexpected fgets() fail.' . "\n";
            file_put_contents('ParseLog.txt', $out, FILE_APPEND);
        }

    }
}
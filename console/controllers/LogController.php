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
    /**
     * Формат логов.
     */
    CONST FORMAT_LOGS_COMBINED = 'combined';
    CONST FORMAT_LOGS_COMMON = 'common';

    /**
     * Шаблоны для поиска в строке.
     */
    CONST PATTERN_COMBINED = '#(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+) (\".*?\") (\".*?\")#';
    CONST PATTERN_COMMON = '#(\S+) (\S+) (\S+) \[([^:]+):(\d+:\d+:\d+) ([^\]]+)\] \"(\S+) (.*?) (\S+)\" (\S+) (\S+)#';

    /**
     * Метод запускается по крону из InstallController.
     * Интервал задаётся в settings.php.
     */
    public function actionLogsHandler()
    {
        $paths = Yii::$app->params['logs'];

        foreach ($paths as $path) {
            $handle = @fopen(self::getFileByPath($path), 'r');
            if ($handle) {
                while (($line = fgets($handle, 4096)) !== false) {
                    $data = self::logParse($line);
                    self::saveLogFromData($data);
                }
                if (!feof($handle)) {
                    self::writeParseLog();
                }
                fclose($handle);
            }
        }
    }

    /**
     * @param $path string
     * @return string
     *
     * Вовзращает полный путь до файла с названием.
     */
    public static function getFileByPath($path)
    {
        $fileMask = Yii::$app->params['fileMask'];
        $file[0] = '';

        if (is_dir($path)) {
            $file = FileHelper::findFiles($path, [
                'only' => [ $fileMask ]
            ]);
        }
        return $file[0];
    }

    /**
     * @param $data
     *
     * Сохраняем строку из лога в БД.
     * В случае ошибки пишем в собственный лог.
     */
    public static function saveLogFromData($data)
    {
        if (!self::logIsExists($data)) {
            $log = new Log($data);
            if (!$log->save()) {
                self::writeParseLog($data);
            }
        }
    }

    /**
     * @param $line string
     * @return array
     *
     * Парсинг одной строки из логов.
     */
	public static function logParse($line)
	{
        $pattern = self::getPattern();
        preg_match_all($pattern, $line, $matches);

        /*
         * $matches[1][0] - IP
         * $matches[4][0] - Date
         * $matches[5][0] - Time
         * $matches[6][0] - Timezone
         * $matches[7][0] - Request type (GET, POST, etc.)
         * $matches[8][0] - URL
         * $matches[10][0] - Response code
         * $matches[11][0] - Bytes
         * $matches[12][0] - Referrer
         * $matches[13][0] - User agent
         */

        $date = self::timeTransform($matches[4][0], $matches[5][0], $matches[6][0]);

        $data = [];
        $data['ip'] = substr($matches[1][0], 0, 15);
        $data['time'] = intval($date->format('U'));
        $data['method'] = substr($matches[7][0],0,10);
        $data['url'] = substr($matches[8][0],0,255);
        $data['response'] = intval($matches[10][0]);
        $data['byte'] = intval($matches[11][0]);

        # Существование переменных зависит от $pattern
        $data['referrer'] = (isset($matches[12][0]) && strlen($matches[12][0]) > 0) ? substr($matches[12][0], 0,255) : null;
        $data['user_agent'] = (isset($matches[12][0]) && strlen($matches[12][0]) > 0) ? substr($matches[13][0], 0, 255) : null;

        return $data;
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
     * @param $data array
     * @return boolean
     *
     * Проверка существования лога в БД.
     */
    public static function logIsExists($data)
    {
        $logExists = Log::find()
            ->where([
                'ip' => $data['ip'],
                'time' => $data['time'],
                'method' => $data['method'],
                'url' => $data['url'],
                'response' => $data['response'],
                'byte' => $data['byte'],
                'referrer' => $data['referrer'],
                'user_agent' => $data['user_agent'],
            ])->exists();
        return (bool) $logExists;
    }

    /**
     * @param $date
     * @param $time
     * @param $timezone
     * @return bool|DateTime
     *
     * Преобразуем время в объект DateTime для удобства работы.
     */
    public static function timeTransform($date, $time, $timezone)
    {
        $timezone = new DateTimeZone($timezone);
        $dateTime = DateTime::createFromFormat('d/M/Y H:i:s', implode(' ', [$date, $time]), $timezone);
        return $dateTime;
    }

    /**
     * @param array $data
     *
     * Записываем ошибки парсинга и сохранения в БД в ParseLog.txt.
     * Если $data не пусто, то это означает ошибку сохранения лога в БД, иначе ошибку fgets().
     * P.S. В случае, если ParseLog.txt не пуст, то он будет располагаться в корне сайта.
     */
    public static function writeParseLog(array $data = [])
    {
        $dateTime = new DateTime();
        $time = $dateTime->format('d-m-Y H:i:s O');

        if (!empty($data)) {
            $errorMessage = ' Error: log not saved. Log description: ';

            $error = [];
            $error['common'] = implode([$time, $errorMessage, PHP_EOL]);
            $error['ip'] = implode(['IP: ', $data['ip'], PHP_EOL]);
            $error['time'] = implode(['Time: ', $data['time'], PHP_EOL]);
            $error['method'] = implode(['Method: ', $data['method'], PHP_EOL]);
            $error['url'] = implode(['Method: ', $data['url'], PHP_EOL]);
            $error['response'] = implode(['Response: ', $data['url'], PHP_EOL]);
            $error['byte'] = implode(['Bytes: ', $data['byte'], PHP_EOL]);
            $error['referrer'] = implode(['Referrer: ', $data['referrer'], PHP_EOL]);
            $error['user_agent'] = implode(['User Agent: ', $data['user_agent'], PHP_EOL, PHP_EOL]);

            $out = implode($error);
        } else {
            $errorMessage = ' Error: unexpected fgets() fail.';
            $out = implode(' ', [$time, $errorMessage, PHP_EOL, PHP_EOL]);
        }
        file_put_contents('ParseLog.txt', $out, FILE_APPEND);
    }
}
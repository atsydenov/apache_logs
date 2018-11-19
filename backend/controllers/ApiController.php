<?php

namespace backend\controllers;

use common\models\Log;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ApiController extends ActiveController
{
    /**
     * Левый конец временного интервала.
     */
    CONST PARAM_FROM_DATE = 'fd';

    /**
     * Правый конец временного интервала.
     */
    CONST PARAM_TO_DATE = 'td';

    /**
     * Параметр для группировки по IP (?group=ip).
     */
    CONST PARAM_GROUP_IP_KEY = 'group';
    CONST PARAM_GROUP_IP_VALUE = 'ip';

    /**
     * @var string
     *
     * Модель для API.
     */
    public $modelClass = 'common\models\Log';

    /**
     * @return array
     *
     * Вывод в формате JSON.
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            [
                'class' => 'yii\filters\ContentNegotiator',
                'only' => ['index'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ]);
    }

    /**
     * @return array
     *
     * Для actionIndex задаём группировку по IP и выборку по временному интервалу.
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function () {
                    $intervalTime = self::intervalTime();
                    $query = Log::find()->where($intervalTime);
                    $data = new ActiveDataProvider([
                        'query' => $query,
                        'pagination' => false,
                        'sort' => [
                            'attributes' => ['ip'],
                        ],
                    ]);
                    $data = $data->getModels();
                    $result = self::group($data);
                    return $result;
                }
            ],
        ];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     *
     * Возвращает временной интервал поиска в виде массива условия.
     */
    public static function intervalTime()
    {
        $fromDate = self::findTimeParamInRequest(self::PARAM_FROM_DATE);
        $toDate = self::findTimeParamInRequest(self::PARAM_TO_DATE);

        if ($fromDate === null && $toDate === null) {
            return ['>', 'time', 0];
        }

        if ($fromDate === null && $toDate !== null) {
            $fromDate = 0;
        }

        if ($fromDate !== null && $toDate === null) {
            return ['>=', 'time', $fromDate];
        }

        if ($fromDate > $toDate) {
            throw new BadRequestHttpException('Bad Request: fd should be less than td.');
        } else {
            return ['between', 'time', $fromDate, $toDate];
        }
    }

    /**
     * @param $param
     * @return mixed|null
     * @throws BadRequestHttpException
     *
     * Поиск целочисленного, положительного параметра в запросе для определения временного интервала.
     * Если временной параметр отсутствует, возвращает null.
     */
    public static function findTimeParamInRequest($param)
    {
        $queryParams = Yii::$app->request->queryParams;
        $result = null;

        if (ArrayHelper::keyExists($param, $queryParams, false)) {
            $result = filter_var($queryParams[$param], FILTER_VALIDATE_INT);
            if ($result < 0) {
                $errorMessage = 'Bad Request: parameter %s must be positive.';
                throw new BadRequestHttpException(sprintf($errorMessage, $param));
            }
        }
        return $result;
    }

    /**
     * @return bool
     *
     * Проверка наличия параметра группировки по IP в запросе.
     */
    public static function isGroupRequest()
    {
        $queryParams = Yii::$app->request->queryParams;
        $result = false;

        if (ArrayHelper::keyExists(self::PARAM_GROUP_IP_KEY, $queryParams, false)) {
            if (strtolower($queryParams[self::PARAM_GROUP_IP_KEY]) == self::PARAM_GROUP_IP_VALUE) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     *
     * Группировка ActiveDataProvider по IP.
     */
    public static function group($data)
    {
        $result = $data;
        $uniqueIPs = array_unique(ArrayHelper::getColumn($data, 'ip'));

        if (self::isGroupRequest() && !empty($uniqueIPs)) {
            $result = [];
            foreach ($uniqueIPs as $ip) {
                foreach ($data as $model) {
                    if ($ip == $model->ip) {
                        $result[$ip][] = $model;
                    }
                }
            }
        }
        return $result;
    }
}
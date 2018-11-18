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
    CONST PARAM_FROM_DATE = 'fd';
    CONST PARAM_TO_DATE = 'td';

    CONST PARAM_GROUP_IP_KEY = 'group';
    CONST PARAM_GROUP_IP_VALUE = 'ip';

    public $modelClass = 'common\models\Log';

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

    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareDataProvider' => function () {
                    $condition = self::condition();
                    $query = Log::find()->where($condition);
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
     */
    public static function condition()
    {
        $fromDate = self::getIntParamFromRequest(self::PARAM_FROM_DATE);
        if ($fromDate === -1) {
            throw new BadRequestHttpException('Bad Request: parameter fd is incorrect.');
        }

        $toDate = self::getIntParamFromRequest(self::PARAM_TO_DATE);
        if ($toDate === -1) {
            throw new BadRequestHttpException('Bad Request: parameter td is incorrect.');
        }

        if ($fromDate === null && $toDate === null) {
            return ['>', 'time', 0];
        }

        if ($fromDate === null && $toDate !== null) {
            $fromDate = 0;
        }

        if ($fromDate !== null && $toDate === null) {
            return ['>', 'time', $fromDate];
        }

        if ($fromDate > $toDate) {
            throw new BadRequestHttpException('Bad Request: fd should be less than td.');
        } else {
            return ['between', 'time', $fromDate, $toDate];
        }
    }

    /**
     * @param $param string
     * @return int
     */
    public static function getIntParamFromRequest($param)
    {
        $queryParams = Yii::$app->request->queryParams;

        if (ArrayHelper::keyExists($param, $queryParams, false)) {
            $filter = filter_var($queryParams[$param], FILTER_VALIDATE_INT);
            if ($filter === 0 || $filter > 0) {
                return $filter;
            } else {
                return -1;
            }
        } else return null;
    }

    /**
     * @return bool
     *
     * Проверка наличия параметра группировки по ip в запросе.
     */
    public static function getGroupParamFromRequest()
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
     * Группировка по ip.
     */
    public static function group($data)
    {
        $result = $data;
        $ips = self::getIPsFromArrayObjects($data);

        if (self::getGroupParamFromRequest() && !empty($ips)) {
            $result = [];
            foreach ($ips as $ip) {
                foreach ($data as $model) {
                    if ($ip == $model->ip) {
                        $result[$ip][] = $model;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     *
     * Получаем все ip из массива объектов.
     */
    public static function getIPsFromArrayObjects($data)
    {
        $ips = [];
        /** @var Log $object */
        foreach ($data as $object) {
            if ($object->hasAttribute('ip')) {
                $ips[] = $object->ip;
            }
        }
        return array_unique($ips);
    }
}
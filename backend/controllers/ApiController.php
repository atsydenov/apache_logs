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
                    return new ActiveDataProvider([
                        'query' => $query,
                        'pagination' => false,
                    ]);
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
}
<?php

namespace common\models;

use DateTime;
use DateTimeZone;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LogSearch represents the model behind the search form of `common\models\Log`.
 */
class LogSearch extends Log
{
    /**
     * @var string
     * Левый конец временного интервала.
     */
    public $from_date;

    /**
     * @var string
     * Правый конец временного интервала.
     */
    public $to_date;

    /**
     * Формат даты из формы поиска.
     */
    CONST DATE_FORMAT = 'd.m.Y H:i:s';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'response', 'byte', 'created_at'], 'integer'],
            [['ip', 'method', 'url', 'referrer', 'user_agent', 'from_date', 'to_date'], 'safe'],
            [['from_date', 'to_date'], 'trim'],
            [['from_date'], 'validateDates'],
            [
                ['from_date', 'to_date'],
                'match', 'pattern' => '#^[0-9]{2}\.[0-9]{2}\.2[0-9]{3} [0-9]{2}:[0-9]{2}:[0-9]{2}$#',
                'message' => 'Please input correct date, example: 15.11.2018 10:34:53.'
            ],
        ];
    }

    /**
     *
     * Валидация для временного интервала.
     */
    public function validateDates()
    {
        $dateFormat = self::DATE_FORMAT;
        if (!empty($this->from_date) && !empty($this->to_date)) {
            $fromDate = DateTime::createFromFormat($dateFormat, $this->from_date)->format('U');
            $toDate = DateTime::createFromFormat($dateFormat, $this->to_date)->format('U');
            if ($fromDate > $toDate) {
                $this->addError('*', '\'From Date\' should be less than \'To Date\'.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Log::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        $intervalTime = self::intervalTime($this->from_date, $this->to_date);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere($intervalTime);

        $query->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @return array
     *
     * Возвращает временной интервал поиска в виде массива условия.
     */
    public static function intervalTime($fromDate, $toDate)
    {
        $dateFormat = self::DATE_FORMAT;
        # Тайм зона из settings.php
        $timezone = new DateTimeZone(Yii::$app->params['timezone']);

        if (empty($fromDate) && empty($toDate)) {
            return ['>', 'time', 0];
        }

        if (empty($fromDate) && !empty($toDate)) {
            $fromDate = 0;
            $toDate = DateTime::createFromFormat($dateFormat, $toDate, $timezone)->format('U');
        }

        if (!empty($fromDate) && empty($toDate)) {
            $fromDate = DateTime::createFromFormat($dateFormat, $fromDate, $timezone)->format('U');
            return ['>=', 'time', $fromDate];
        }

        if (!empty($fromDate) && !empty($toDate)) {
            $fromDate = DateTime::createFromFormat($dateFormat, $fromDate, $timezone)->format('U');
            $toDate = DateTime::createFromFormat($dateFormat, $toDate, $timezone)->format('U');
        }

        return ['between', 'time', $fromDate, $toDate];
    }
}

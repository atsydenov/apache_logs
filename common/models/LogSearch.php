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
    public $from_date;
    public $to_date;

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
            [['from_date', 'to_date'], 'match', 'pattern' => '#^[0-9]{2}\.[0-9]{2}\.[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}$#'],
        ];
    }

    /**
     * @param $attribute
     *
     * Валидация для временного интервала.
     */
    public function validateDates($attribute)
    {
        if (!empty($this->from_date) && !empty($this->to_date)) {
            if ($this->from_date > $this->to_date) {
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
        $dates = self::dateHandler($this->from_date, $this->to_date);
        $condition = self::condition($dates);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'response' => $this->response,
            'byte' => $this->byte,
            'created_at' => $this->created_at,
        ])->andFilterWhere($condition);

        $query->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'method', $this->method])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'referrer', $this->referrer])
            ->andFilterWhere(['like', 'user_agent', $this->user_agent]);

        return $dataProvider;
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @return array
     *
     * Получаем временной интервал из параметров запроса.
     */
    public static function dateHandler($fromDate, $toDate)
    {
        # Тайм зона из settings.php
        $timezone = new DateTimeZone(Yii::$app->params['timezone']);

        if (!empty($fromDate)) {
            $fromDate = DateTime::createFromFormat('d.m.Y H:i:s', $fromDate, $timezone)->format('U');
            //$fromDate = ($fromDate) ? $fromDate->format('U') : false;
        }

        if (!empty($toDate)) {
            $toDate = DateTime::createFromFormat('d.m.Y H:i:s', $toDate, $timezone)->format('U');
            //$toDate = ($toDate) ? $toDate->format('U') : false;
        }

        return ['from_date' => $fromDate, 'to_date' => $toDate];
    }

    /**
     * @param $dates
     * @return array
     *
     * Формируем условие на временной интервал.
     */
    public static function condition($dates)
    {
        $fromDate = $dates['from_date'];
        $toDate = $dates['to_date'];

        if (empty($fromDate) && empty($toDate)) {
            return ['>', 'time', 0];
        }

        if (empty($fromDate) && !empty($toDate)) {
            $fromDate = 0;
        }

        if (!empty($fromDate) && empty($toDate)) {
            return ['>', 'time', $fromDate];
        }

        return ['between', 'time', $fromDate, $toDate];
    }
}

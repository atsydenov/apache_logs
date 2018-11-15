<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Log;

/**
 * LogSearch represents the model behind the search form of `common\models\Log`.
 */
class LogSearch extends Log
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'time', 'response', 'byte', 'created_at'], 'integer'],
            [['ip', 'method', 'url', 'referrer', 'user_agent'], 'safe'],
        ];
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

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'time' => $this->time,
            'response' => $this->response,
            'byte' => $this->byte,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'method', $this->method])
            ->andFilterWhere(['like', 'url', $this->url])
            ->andFilterWhere(['like', 'referrer', $this->referrer])
            ->andFilterWhere(['like', 'user_agent', $this->user_agent]);

        return $dataProvider;
    }
}

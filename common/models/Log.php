<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "log".
 *
 * @property int $id
 * @property string $ip
 * @property int $time
 * @property string $method
 * @property string $url
 * @property int $response
 * @property int $byte
 * @property string $referrer
 * @property string $user_agent
 * @property int $created_at
 */
class Log extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'log';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ip', 'time', 'method', 'url', 'response', 'byte'], 'required'],
            [['time', 'response', 'byte', 'created_at'], 'integer'],
            [['ip'], 'string', 'max' => 15],
            [['method'], 'string', 'max' => 10],
            [['url', 'referrer', 'user_agent'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'time' => 'Time',
            'method' => 'Method',
            'url' => 'Url',
            'response' => 'Response',
            'byte' => 'Byte',
            'referrer' => 'Referrer',
            'user_agent' => 'User Agent',
            'created_at' => 'Created At',
        ];
    }
}

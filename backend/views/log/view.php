<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Log */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'timeZone' => Yii::$app->params['timezone']
        ],
        'model' => $model,
        'attributes' => [
            'id',
            'ip',
            [
                'attribute' => 'time',
                'format' =>  ['date', 'dd.MM.Y H:i:s O'],
            ],
            'method',
            'url',
            'response',
            'byte',
            'referrer',
            'user_agent',
            [
                'attribute' => 'created_at',
                'format' =>  ['date', 'dd.MM.Y H:i:s O'],
            ],
        ],
    ]) ?>

</div>

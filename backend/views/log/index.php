<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Log', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'ip',
            'time:datetime',
            'method',
            'url:url',
            //'response',
            //'byte',
            //'referrer',
            //'user_agent',
            //'created_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\LogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ip') ?>

    <?= $form->field($model, 'time') ?>

    <?= $form->field($model, 'method') ?>

    <?= $form->field($model, 'url') ?>

    <?php // echo $form->field($model, 'response') ?>

    <?php // echo $form->field($model, 'byte') ?>

    <?php // echo $form->field($model, 'referrer') ?>

    <?php // echo $form->field($model, 'user_agent') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

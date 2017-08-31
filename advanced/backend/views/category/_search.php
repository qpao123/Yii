<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\CategorySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="category-search" style="padding-top:10px;">
    <?php $form = ActiveForm::begin([
        'action'  => ['index'],
        'method'  => 'get',
        'options' => ['class' => 'form-inline'],
        'id'      => 'category-search-form',
    ]); ?>

    <div style="margin-bottom:15px;">
        <div class="form-group">
            <?= Html::textInput('id', Yii::$app->request->get('id'), [
                'class'       => 'input-sm input-s form-control',
                'placeholder' => 'ID',
            ]) ?>
        </div>
        <div class="form-group">
            <?= Html::textInput('name', Yii::$app->request->get('name'), [
                'class'       => 'input-sm input-s form-control',
                'placeholder' => 'Name',
            ]) ?>
        </div>
        <div class="form-group">
            <?= Html::textInput('pid', Yii::$app->request->get('pid'), [
                'class'       => 'input-sm input-s form-control',
                'placeholder' => 'Pid',
            ]) ?>
        </div>
        <div class="form-group">
            <?= Html::textInput('path', Yii::$app->request->get('path'), [
                'class'       => 'input-sm input-s form-control',
                'placeholder' => 'Path',
            ]) ?>
        </div>
        <div class="form-group">
            <?= Html::dropDownList(
                'status',
                Yii::$app->request->get('status'),
                [
                    '1' => '显示',
                    '0' => '不显示',
                ],
                [
                    'class'  => 'input-sm input-s form-control',
                    'prompt' => '状态',
                ]
            ) ?>
        </div>
        <div class="form-group">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary btn-sm btn-search']) ?>
            <?= Html::resetButton('重置搜索条件', ['class' => 'btn btn-default btn-sm btn-reset']) ?>
        </div>
    </div>

    <div style="margin-bottom:10px;">
        <?= Html::a('新增', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
        <?= Html::a('下载', 'javascript:void(0);', [
            'class'  => 'btn btn-sm btn-info btn-download',
            'target' => '_blank',
        ]); ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script src="<?= Yii::getAlias("@web").'/js/common-search.js' ?>" ></script>

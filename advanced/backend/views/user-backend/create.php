<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model backend\models\UserBackend */

$this->title = '添加新用户';
$this->params['breadcrumbs'][] = ['label' => 'User Backends', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-backend-create">
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'form-create']); ?>

            <?= $form->field($model, 'username')->label('登陆名')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'email')->label('邮箱') ?>

            <?= $form->field($model, 'password')->label('密码')->passwordInput() ?>

            <div class="form-group">
                <?= Html::submitButton('添加', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

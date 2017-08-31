<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Video */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="video-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'imgUrl')->fileInput() ?>

    <div class="form-group field-video-url">
        <label class="control-label" for="video-url">视频</label>
        <input type="file" id="video-file" name="Video[file]">
        <div class="help-block"></div>
    </div>

    <div class="form-group field-video-url">
        <input type="hidden" id="video-url" name="Video[url]" value="">
        <div class="help-block"></div>
    </div>

    <?= $form->field($model, 'desc')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::button($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'id' => 'js-btn']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<script>
    var file_url = '<?= Url::toRoute(['video/batch-file']); ?>';

    $('#js-btn').on('click',function(){
        var file = $('#video-file')[0].files[0];  //得到文件对象

        if ($('#video-title').val().trim().length <= 0) {
            alert('请输入标题');
            return false;
        }

        if (!file) {
            alert('请上传视频');
            return false;
        }

        if ($('#video-desc').val().trim().length <= 0) {
            alert('请输入描述');
            return false;
        }

        var type = file.type.split('/'); //文件后缀
        var fileSize = file.size; //获取文件大小
        var length = 1*1024*1024; //每次截取1M
        var num = Math.ceil(fileSize/length); //发送次数
        var start = 0;
        var end	= start+length;
        var i = 1;

        while (start < fileSize) {
            var chunk_file = file.slice(start,end);
            ajaxUpload(chunk_file,type,i,num);
            i++;
            start = end;
            end = start+length;
            end = end > fileSize ? fileSize : end;
        }

        $('#video-file').val('');
        $('.video-form form').submit();
        return true;
    });

    var ajaxUpload = function(file,type,i,num){
        var xml = new XMLHttpRequest();
        //切割上传要用同步，不然最后得到的结果可能不是按顺序拼接的。切记
        //如果要用异步，那后台接受的结果，一定要编号，并且按编号，追加
        xml.open('POST',file_url,false); //同步

        xml.onreadystatechange = function(){
            if (this.readyState == 4 && this.status == 200) {
                if (i == num) {
                    $('#video-url').val(this.responseText);
                }
                console.log(this.responseText);
            }
        };

        var data = new FormData();
        data.append('type',type[1]);
        data.append('i',i);
        data.append('file',file);
        xml.send(data);
    };
</script>

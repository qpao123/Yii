<?php

namespace backend\controllers;

use backend\models\UploadForm;
use Yii;
use backend\models\Video;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class VideoController extends \yii\web\Controller
{
    public $enableCsrfValidation = false; //关闭csrf验证。上传视频如果一次过大，可能导致数据中不存在csrf值

    public function actionIndex()
    {
        $searchModel = new Video();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate()
    {
        $model = new Video();

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $fileModel = new UploadForm('Video');
            $fileModel->imageFile = UploadedFile::getInstance($fileModel, 'imgUrl');
            $model->imgUrl = $fileModel->upload();
            $model->uid = Yii::$app->user->id;

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionBatchFile()
    {
        if ($_POST['i'] == 1) {
            $fileName = date('Ymd').'-'.uniqid().'.'.$_POST['type'];
            $url = Yii::getAlias('@webroot').'/uploads/'.$fileName;
            Yii::$app->session->set('file_url',$url);
        }

        if ($_POST['i'] != 1) {
            $url = Yii::$app->session->get('file_url');
        }

        if ($_FILES['file']['error'] == 0) {
            //接收小块文件，并合并
            if (!file_exists($url)) {
                move_uploaded_file($_FILES['file']['tmp_name'], $url); //不存在就先移动过来
            }else{
                //追加只能追加二进制，所以要先用file_get_contents()，先获取上传结果，再追加
                file_put_contents($url, file_get_contents($_FILES['file']['tmp_name']), FILE_APPEND); //存在就追加
            }
            echo str_replace(Yii::getAlias('@webroot').'/uploads/','',$url);
        } else {
            echo '上传错误';
        }
    }

    public function actionUpdate()
    {
        echo 'codeing...';
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        @unlink(Yii::getAlias('@webroot').'/uploads/'.$model->imgUrl);
        @unlink(Yii::getAlias('@webroot').'/uploads/'.$model->url);
        $model->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Video::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}

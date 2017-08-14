<?php

namespace backend\controllers;

use Yii;
use backend\models\Category;
use backend\models\CategorySearch;
use common\helpers\Download;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        //记录一个类别为category的日志，一般在try catch中使用
//        try {
//
//        } catch (\Exception $e) {
//            Yii::info(sprintf(
//                '测试日志,原因:%s,日志:%s',
//                $e->getMessage(),
//                $e->getTraceAsString()
//            ), 'category');
//        }
        $msg = '条件出错！';
        Yii::info(sprintf('测试日志，消息：%s',$msg), 'category');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Category model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Category();
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $resArr = $model::findOne($model->pid);
            if (!empty($resArr)) {
                $model->path = $resArr->path.$resArr->id.',';
            } else {
                $model->path = '0,';
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        } else {
            $model->status = 1; //设置radio单选框默认选中
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionCsv()
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        Download::downloadCsv(sprintf('Category%s.csv', date('Y-m-d')), $dataProvider->query, [
            [
                'label' => 'ID',
                'value' => 'id',
            ],
            [
                'label' => 'Name',
                'value' => 'name',
            ],
            [
                'label' => 'Pid',
                'value' => 'pid',
            ],
            [
                'label' => 'Path',
                'value' => 'path',
            ],
            [
                'label' => 'Status',
                'value' => function ($item) {
                    return $item['status'] == 1 ? '显示' : '不显示';
                },
            ]
        ]);
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Categories';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .my-page {
        margin:0;
    }
    .my-tab-width {
        width:60px;
    }
</style>
<div class="category-index">
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        <?= Html::a('Create Category', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'class'    => 'yii\grid\SerialColumn',
                'header'   => '序号',
                'headerOptions'  => ['class' => 'vertical-middle my-tab-width'],
                'contentOptions' => ['class' => 'vertical-middle my-tab-width'],
            ],
            [
                'class'    => 'yii\grid\ActionColumn',
                'header'   => 'ID',
                'template' => '{link}',
                'buttons'  => [
                    'link' => function ($url, $item, $key) {
                        return Html::a($item['id'], ['/category/view', 'id' => $item['id']], [
                            'title'  => '查看',
                            'target' => '_blank',
                        ]);
                    },
                ],
            ],
            'name',
            'pid',
            'path',
            [
                'label' => '状态',
                'value' => function ($item) {
                    return $item['status'] == 1 ? '显示' : '不显示';
                },
            ],
            [
                'class'  => 'yii\grid\ActionColumn',
                'header' => '操作',
            ],
        ],
        'layout'         => "{summary}\n{items}",
        'summaryOptions' => [
            'class' => 'summary m-l',
        ],
        'options'        => ['style' => 'padding-top:5px;',],
        'tableOptions'   => [
            'class' => 'table table-hover table-bordered',
            'style' => 'min-width:100%;border:',
        ],
    ]); ?>

    <?= LinkPager::widget([
        'pagination'     => $dataProvider->pagination,
        'nextPageLabel'  => '下一页',
        'prevPageLabel'  => '上一页',
        'firstPageLabel' => '首页',
        'lastPageLabel'  => '末页',
        'options'        => [
            'class' => 'pagination pagination-sm m-t-none m-b-none my-page',
        ],
    ]) ?>
</div>

<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Video';
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
    <h1><?= Html::encode($this->title) ?></h1>

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
                        return Html::a($item['id'], ['/video/view', 'id' => $item['id']], [
                            'title'  => '查看',
                            'target' => '_blank',
                        ]);
                    },
                ],
            ],
            'title',
            [
              'label' => '封面图',
              'format' => 'raw',
              'value' => function ($item) {
                    return '<img width=50 height=50 src="'.Yii::getAlias('@web').'/uploads/'.$item['imgUrl'].'" />';
                },
            ],
            'url',
            'desc',
            'uid',
            [
                'label' => '状态',
                'value' => function ($item) {
                    return $item['status'] == 0 ? '显示' : '不显示';
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
<script>
//    $('.btn-download').on('click',function(){
//        if (!validate()) {
//            return false;
//        }
//        $("#category-search-form").attr("action", "/category/csv").submit();
//    })
//
//    $('.btn-search').on('click',function () {
//        if (!validate()) {
//            return false;
//        }
//        $("#category-search-form").attr("action", "/category/index").submit();
//    });
//
//    function validate() {
//        return true;
//    }
</script>

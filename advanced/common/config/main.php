<?php
return [
    'on beforeAction' => ['common\helpers\Log', 'addAccess'],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],
    // 配置语言
    'language'=>'zh-CN',
    // 配置时区
    'timeZone'=>'Asia/Shanghai',
];

<?php
return [
    'on beforeAction' => ['common\helpers\Log', 'addAccess'],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
//        'cache'   => [
//            'class'        => 'yii\caching\MemCache',
//            'keyPrefix'    => 'zx_',
//            'useMemcached' => true,
//            'servers'      => [
//                [
//                    'host' => '127.0.0.1',
//                    'port' => 11211,
//                    //'weight' => 60, 如果有多个可以设置权重
//                ],
//            ],
//        ],
//        'cache'   => [
//            'class' => 'yii\redis\Cache',
//        ],
//        'redis'   => [
//            'class'    => 'yii\redis\Connection',
//            'hostname' => 'localhost',
//            'port'     => 6379,
//            'database' => 0,
//        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
    ],
    // 配置语言
    'language'=>'zh-CN',
    // 配置时区
    'timeZone'=>'Asia/Shanghai',
];

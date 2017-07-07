<?php

namespace console\controllers;

use yii\console\Controller;
use Yii;

/**
* Class WsmController
*
* @package console\controllers
*/
class DataController extends Controller
{
    public function actionBatchCategoryInsert()
    {
        //模拟批量插入category
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $client->connect('127.0.0.1', 9888) || exit("connect fail error".$client->errCode."\r\n");
        $content[] = [
            'name' => '汽车',
            'pid'  => '0',
            'path' => '0,'
        ];
        $content[] = [
            'name' => '化妆品',
            'pid'  => '0',
            'path' => '0,'
        ];
        for ($i = 0; $i <= 1; $i++) {
            $client->send(json_encode(['content' => $content[$i], 'event' => 'fn']));
            $response = $client->recv();
            echo $response . PHP_EOL;
        }
        $client->close();
    }

}
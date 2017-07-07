<?php

namespace console\controllers;

use yii\console\Controller;
use Yii;

/**
 * Class WsmController
 *
 * @package console\controllers
 */
class TestController extends Controller
{
    private $server;

    public function prepare()
    {
        $this->server = new \swoole_server('127.0.0.1', 9888);
        $this->server->set([
            'worker_num' => 2,
            'task_worker_num' => 10,
        ]);
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('connect', [$this, 'onConnect']);
        $this->server->on('receive', [$this, 'onReceive']);
        $this->server->on('task', [$this, 'onTask']);
        $this->server->on('finish', [$this, 'onFinish']);
        $this->server->on('close', [$this, 'onClose']);
    }

    public function actionStart()
    {
        $this->prepare();
        $this->server->start();
    }

    public function onStart()
    {
        echo 'start ...' . PHP_EOL;
    }

    public function onConnect($server, $fd)
    {
        echo "new client connected" . PHP_EOL;
    }

    public function onReceive($server, $fd, $formId, $data)
    {
        echo 'server receive ---' . PHP_EOL;

        $server->task($data);
        $server->send($fd, '数据已经收到---'.$data.PHP_EOL);
    }

    public function onTask($server, $taskId, $fromId, $data)
    {
        $data = json_decode($data, true);

        $event = $data['event'];
        $content = $data['content'];

        switch ($event) {
            case 'fn':
                //可以根据参数执行任何耗时事件，发邮件，批量数据处理，导入，导出。复杂的业务逻辑
                $sql = "INSERT INTO category (id,name,pid,path) VALUES (null,'$content[name]','$content[pid]','$content[path]')";
                $res = Yii::$app->db->createCommand($sql)->execute();
                echo $res ? '成功' : '失败';
                break;
            default:
                echo '无效的事件';
                break;
        }
    }

    public function onFinish()
    {

    }

    public function onClose()
    {

    }
}

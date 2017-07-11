<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "access_log".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $user_ip
 * @property string $access_url
 * @property string $create_time
 */
class AccessLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'access_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['user_ip'], 'string', 'max' => 20],
            [['access_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user_ip' => 'User Ip',
            'access_url' => 'Access Url',
            'create_time' => 'Create Time',
        ];
    }

    public function addone($url, $id, $ip)
    {
        if($this->ignoreUrl($url)) {
            return;
        }
        $this->access_url = $url;
        $this->user_id = $id;
        $this->user_ip = $ip;
        $this->create_time = date('Y-m-d H:i:s', time());
        if(!$this->save()) {
            throw new \Exception('error_add_log_access');
        }
    }

    /**
     * 不需要记录访问日志的路径
     * @param type $url
     * @return boolean
     */
    private function ignoreUrl($url)
    {
        $result = false;
        $baseUrl = explode('?', $url)[0];
        $patitions = explode('/', trim($baseUrl, '/'));
        if($patitions[0] === 'gii' || $patitions[0] === 'debug') {
            $result = true;
        }

        return $result;
    }
}

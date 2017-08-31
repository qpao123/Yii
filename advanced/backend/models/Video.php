<?php

namespace backend\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "video".
 *
 * @property integer $id
 * @property string $title
 * @property string $url
 * @property string $desc
 * @property integer $uid
 * @property integer $status
 */
class Video extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'video';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'status'], 'integer'],
            [['title'], 'string', 'max' => 100],
            [['url','imgUrl','desc'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'url' => '文件',
            'imgUrl' => '封面图',
            'desc' => '描述',
            'uid' => '上传用户',
            'status' => '状态',
        ];
    }

    public function search($params)
    {
        $query = self::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 6] //分页条数
        ]);

        $this->load($params, ''); //搜索提交时候没有带控制器名称，这样写才能赋值给$this

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
        ]);

        return $dataProvider;
    }
}

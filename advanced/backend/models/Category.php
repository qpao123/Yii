<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property string $name
 * @property integer $pid
 * @property string $path
 * @property integer $status
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'status'], 'integer'],
            [['name', 'path'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'pid' => 'Pid',
            'path' => 'Path',
            'status' => 'Status',
        ];
    }

    public static function getCategoryTypeList()
    {
        $arr = self::find()
            ->select([
            "*",
            "concat(path,id) as sign"
            ])
            ->orderBy([
                'sign' => SORT_ASC
            ])
            ->asArray()->all();

        $result = ['0' => '顶级分类'];
        foreach ($arr as $k => $val) {
            $num = substr_count($val['sign'], ',');
            $result[$val['id']] = str_repeat('--', $num).$val['name'];
        }

        return $result;
    }
}

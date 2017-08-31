<?php
namespace backend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;
    public $modelName;

    public function __construct($name)
    {
        parent::__construct();
        $this->modelName = $name;
    }

    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg,jpeg'],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            do {
                $imgName = $this->imgName();
            } while (file_exists(Yii::getAlias('@webroot').'/uploads/'.$imgName));
            $this->imageFile->saveAs('uploads/' . $imgName);
            return $imgName;
        } else {
            return false;
        }
    }

    public function imgName()
    {
        return date('YmdHis') . uniqid() . '.' . $this->imageFile->extension;
    }

    public function formName()
    {
        return $this->modelName;
    }
}
<?php

namespace common\helpers;

use Yii;
use common\models\AccessLog;

class Log
{
    public static function addAccess()
    {
        if (Yii::$app->params['is_console'] || Yii::$app->user->isGuest || !YII_ENV_PROD) {
            return true;
        }

        $transaction = AccessLog::getDb()->beginTransaction();
        try {
            $access = new AccessLog();
            $access->addone(Yii::$app->request->url, Yii::$app->user->identity->id, Yii::$app->request->userIP);
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
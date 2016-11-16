<?php

namespace app\classes\common;

use yii\web\NotFoundHttpException;

class Common {
    protected function findModel($id) {
        if (($model = \app\models\ew\ExpressWaybill::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
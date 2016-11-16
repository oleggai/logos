<?php


namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

class InvoiceTypes extends CommonModel {

    /**
     * @var VISIBLE статус видимый
     */
    const VISIBLE = 1;
    /**
     * @var INVISIBLE статус невидимый
     */
    const INVISIBLE = 0;


    public static function tableName()
    {
        return '{{%invoice_types}}';
    }


    public static function getList($field, $empty = false) {
        if ($field=='name') $field=$field.'_'.Yii::$app->language;
        $arr = InvoiceTypes::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }


}
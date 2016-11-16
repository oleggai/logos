<?php
namespace app\models\common;

use Yii;
use app\models\common\CommonQuery;
use creocoder\nestedsets\NestedSetsQueryBehavior;

/**
 * Переопределенный CommonQuery
 * @author Aleksey Samokhvalov
 */

class MenuItemQuery extends CommonQuery
{
    public function behaviors()
    {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }
}

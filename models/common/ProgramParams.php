<?php

namespace app\models\common;

use yii\db\Query;

class ProgramParams
{

    public static function tableName()
    {
        return '{{%params}}';
    }


    /**
     * Получение параметра по его ID
     * @param $param_id
     * @return string
     */
    public static function get_parameter_by_id($param_id)
    {
        $query = new Query;
        if (isset($param_id)&&$param_id!=null&&trim($param_id)!='')
        {
            $selres=$query->select('value')->from(ProgramParams::tableName())->where(['name'=>$param_id])->one();
            return($selres['value']);
        }
        else return '';

    }

}
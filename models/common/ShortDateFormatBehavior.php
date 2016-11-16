<?php

/**
 * Created by PhpStorm.
 * User: goga
 * Date: 14.04.2015
 * Time: 22:53
 */

namespace app\models\common;

use DateTime;

class ShortDateFormatBehavior extends ConverterBehavior
{
    protected function convertToStoredFormat($value)
    {
        if (empty($value)|| strpos($value,'_')!==false) {
            return null;
        }

        $date = DateTime::createFromFormat(Setup::DATE_FORMAT, $value);
        if ($date!==false)
            return $date->format(Setup::MYSQL_DATE_FORMAT_EMPTYTIME);

        return null;
    }

    protected function convertFromStoredFormat($value)
    {
        if (empty($value)) {
            return null;
        }

        $date = DateTime::createFromFormat(Setup::MYSQL_DATE_FORMAT, $value);
        if ($date!==false)
            return $date->format(Setup::DATE_FORMAT);

        return null;
    }

    public static function validate($value){

        if (empty($value)|| strpos($value,'_'))
            return true;

        if (!DateTime::createFromFormat(Setup::DATE_FORMAT, $value))
            return false;

        return true;
    }
}

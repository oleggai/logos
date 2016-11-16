<?php
/**
 * Created by PhpStorm.
 * User: Tochonyi DM
 * Date: 15.07.2015
 * Работает с типом данных DATE
 */

namespace app\models\common;
use DateTime;


class DateShortStringBehavior extends ConverterBehavior
{
    protected function convertToStoredFormat($value)
    {
        if (empty($value)|| strpos($value,'_')!==false) {
            return null;
        }

        $date = DateTime::createFromFormat(Setup::DATE_FORMAT, $value);
        if ($date!==false)
            return $date->format(Setup::MYSQL_SHORTDATE_FORMAT);

        return null;
    }

    protected function convertFromStoredFormat($value)
    {
        if (empty($value)) {
            return null;
        }

        $date = DateTime::createFromFormat(Setup::MYSQL_SHORTDATE_FORMAT, $value);
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

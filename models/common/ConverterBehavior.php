<?php
/**
 * Created by PhpStorm.
 * User: goga
 * Date: 14.04.2015
 * Time: 22:55
 */

namespace app\models\common;


use yii\base\Behavior;

abstract class ConverterBehavior extends Behavior
{
    public $attributes = [];

    public function canGetProperty($name, $checkVars = true)
    {
        return isset($this->attributes[$name]) || parent::canGetProperty($name, $checkVars);
    }

    public function canSetProperty($name, $checkVars = true)
    {
        return isset($this->attributes[$name]) || parent::canSetProperty($name, $checkVars);
    }

    public function __get($param)
    {
        if (isset($this->attributes[$param])) {
            return $this->convertFromStoredFormat($this->owner->{$this->attributes[$param]});
        } else {
            return parent::__get($param);
        }
    }

    public function __set($param, $value)
    {
        if (isset($this->attributes[$param])) {
            $this->owner->{$this->attributes[$param]} = $this->convertToStoredFormat($value);
        } else {
            parent::__set($param, $value);
        }
    }

    abstract protected function convertToStoredFormat($value); //

    abstract protected function convertFromStoredFormat($value);

}
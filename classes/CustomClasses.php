<?php
/**
 * Created by PhpStorm.
 * User: ypalaguta
 * Date: 18.11.2015
 * Time: 12:16
 */

namespace app\classes;


use Faker\Provider\cs_CZ\DateTime;

class CustomClasses
{
public static function placeJSGlobals(){
    $jsGlobals=[
        'globalDate'=>self::makeDate(date('d.m.Y')),
        'globalMaxDate'=>self::makeDate('18.01.2038'),
    ];
    return self::dataToJS($jsGlobals);
}

    function dataToJS($jsGlobals){
        $res='';
        foreach($jsGlobals as $key=>$value)
            $res.=$key.'='.$value.';';
        return $res;
    }

    function makeDate($value){
        return 'new Date.parse(\''.$value.'\');';
    }

    function getWebDir(){

    }

}
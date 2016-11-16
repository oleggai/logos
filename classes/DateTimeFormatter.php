<?php
/**
 * Класс для форматирования дат
 */

namespace app\classes;

use yii\base\Exception;
use Yii;

class DateTimeFormatter {

    /**
     * Метод для получения дат типа 10 января 2015
     * @param $date
     * @param string $lang
     * @return string
     */
    public static function format($date, $lang = 'uk') {
        $language = Yii::$app->language;
        try{
            Yii::$app->language = $lang;
            $dateFormat = Yii::$app->formatter->asDate($date, 'd MMMM yyyy');
            Yii::$app->language = $language;
            return $dateFormat;
        }
        catch(Exception $e) {
            Yii::$app->language = $language;
        }
    }

    /**
     * Метод для преобразования даты в формат типа 10.11.2015 15:16:17
     * @param $date
     * @return string
     */
    public static function npiFormat($date) {
        if(!$date) return '';
        $date = new \DateTime($date);
        return $date->format('d.m.Y H:i:s');
    }

    /**
     * Метод для перобразование даты  без часов минут и секунд
     * @param $date
     * @return string
     */
    public static function formatDateWithoutHis($date) {
        $date = new \DateTime($date);
        return $date->format('d.m.Y');
    }
}
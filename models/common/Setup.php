<?php
/**
 * В файле описаны общие настройки
 *
 * @author Мельник И.А.
 * @category Настройки
 */

namespace app\models\common;


class Setup {
    const MYSQL_DATE_FORMAT = 'Y-m-d H:i:s';
    const MYSQL_DATE_FORMAT_EMPTYTIME = 'Y-m-d 00:00:00';
    const MYSQL_DATE_FORMAT_FIRSTDAY = 'Y-m-01 00:00:00';
    const MYSQL_SHORTDATE_FORMAT = 'Y-m-d';

    const DATE_FORMAT = 'd.m.Y';
    const DATE_FORMAT_FIRSTDAY = '01.m.Y';
    const DATETIME_FORMAT = 'd.m.Y H:i:s';
    const DATETIME_FORMAT_FIRSTDAY = '01.m.Y 00:00:00';
    const TIME_FORMAT = 'H:i';

    const DATE_MASK = '39.19.9999';
    const DATETIME_MASK = '39.19.9999 29:59:59';
    const TIME_MASK = '29:59:59';

    public static function convert($dateStr, $type='datetime', $format = null) {
        if ($type === 'datetime') {
            $fmt = ($format == null) ? self::DATETIME_FORMAT : $format;
        }
        elseif ($type === 'time') {
            $fmt = ($format == null) ? self::TIME_FORMAT : $format;
        }
        else {
            $fmt = ($format == null) ? self::DATE_FORMAT : $format;
        }
        return \Yii::$app->formatter->asDate($dateStr, $fmt);
    }
}
<?php

namespace app\models\common;

use Yii;
use yii\base\Exception;
use yii\db\Query;

/**
 * Класс используется для работы с переводами, которые находятся в связанных таблицах
 * @author Richok FG
 */

class Translator
{
    /**
     * Получить значение конкретного перевода конкретного свойства (вроде уже не надо)
     * @param string $table имя таблицы с переводом
     * @param string $field название поля для перевода
     * @param string $idName название внешнего ключа в талюице переводов
     * @param int $id идентификатор главной сущности
     * @param string $lang язык ('en', 'uk' ...)
     * @return string перевод
     **/
    public static function getTranslation($table, $field, $idName, $id, $lang) {
        if ($table == null || $table == '' || $field == null || $field == '' || $idName == null || $idName == ''
            || $id == null || $lang == null || $lang == '')
            return null;

        try {
            $result = (new Query())->select([$field])->from([$table])->where([$idName => $id, 'lang' => $lang])->scalar(Yii::$app->db);
        }
        catch (Exception $ex) {
            return 'Error! '.$ex->getMessage();
        }
        return $result;
    }

    /**
     * Получить все переводы свойства (возвращает ассоциативный массив вида 'en' => 'перевод')
     * @param string $table имя таблицы с переводом
     * @param string $field название поля для перевода
     * @param string $idName название внешнего ключа в талюице переводов
     * @param int $id идентификатор главной сущности
     * @return string перевод
     **/
    public static function getAll($table, $field, $idName, $id) {
        if ($table == null || $table == '' || $field == null || $field == '' || $idName == null || $idName == '' || $id == null)
            return null;
        try {
            $rows = (new Query())->select([$field, 'lang'])->from($table)->where([$idName => $id])->all(Yii::$app->db);
        }
        catch (Exception $ex) {
            return null;
        }
        $res = array();

        //foreach (array_keys(Yii::$app->languagepicker->languages) as $lang)
        //foreach (Yii::$app-> as $lang => $val)
        //    $res[$lang] = '';
        foreach ($rows as $row) {
            $res[$row['lang']] = $row[$field];
        }

        return $res;
    }

    /**
     * сохранить конкретный перевод конкретного свойства
     **/
    public static function setTranslation($table, $field, $idName, $id, $lang, $value) {
        if ($table == null || $table == '' || $field == null || $field == '' || $idName == null || $idName == ''
            || $lang == null || $lang == '' || $id == null)
            return null;

        try {
                $count = (new Query())->select(['COUNT(*)'])->from([$table])->where([$idName => $id, 'lang' => $lang])->scalar(Yii::$app->db);
        }
        catch (Exception $ex) {
            return 'Error! '.$ex->getMessage();
        }

        if ($count == 0)
            return null;

        $command = Yii::$app->db->createCommand();
        try {
            return $command->update($table, [$field => $value], [$idName => $id, 'lang' => $lang], [])->execute();
        }
        catch (Exception $ex) {
            return 'Error! '.$ex->getMessage();
        }
    }

    /**
     * сохранить все свойства на одном языке. $values - ассоциативный массив вида 'имя_свойства' => 'значение'
     **/
    public static function setAllFields($table, $idName, $id, $lang, $values) {
        if ($table == null || $table == '' || $idName == null || $idName == '' || $id == null || $lang == null || $lang == '' || !is_array($values))
            return 'Empty';

        try {
            $count = (new Query())->select(['COUNT(*)'])->from([$table])->where([$idName => $id, 'lang' => $lang])->scalar(Yii::$app->db);
        }
        catch (Exception $ex) {
            return 'Error! '.$ex->getMessage();
        }

        $command = Yii::$app->db->createCommand();
        try {
            if ($count == 0) {
                $values['lang'] = $lang;
                $values[$idName] = $id;
                $command->insert($table, $values)->execute();
            }
            else {
                $command->update($table, $values, [$idName => $id, 'lang' => $lang])->execute();
            }
        }
        catch (Exception $ex) {
            return 'Error! '.$ex->getMessage();
        }
    }

    /**
     * сохранить все переводы всех свойств сущности.
     * $values - двумерный ассоциативный массив вида
     * ['имя свойства1' => ['en' => 'Перевод en', 'uk' => 'Перевод uk', ...], 'имя_свойства2' => ... и т.д.].
     * из модели передавать удобно в таком виде, тут он трансформируется в вид для сохранения
     **/
    public static function setAll($table, $idName, $id, $values) {
        if ($table == null || $table == '' || $idName == null || $idName == '' || $id == null || !is_array($values))
            return;

        $vals = [];
        foreach ($values as $fieldCode => $field) {
            foreach ($values[$fieldCode] as $langCode => $value) {
                $vals[$langCode][$fieldCode] = $value;
            }
        }

        foreach ($vals as $langCode => $fields) {
            Translator::setAllFields($table, $idName, $id, $langCode, $fields);
        }
    }

    /**
     * удаляет переводы сущности
     **/
    public static function delTranslation($table, $idName, $id) {
        if ($table == null || $table == '' || $idName == null || $idName == '' || $id == null)
            return null;

        $command = Yii::$app->db->createCommand();
        try {
            $command->delete($table, [$idName => $id])->execute();
        }
        catch (Exception $ex) {
            return 'Error! '.$ex->getMessage();
        }
    }
}
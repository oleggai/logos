<?php

namespace app\models\common;

use Yii;
use yii\db\ActiveRecord;

/**
 * Класс автоматический выбирает и сохраняет данные в таблицу с интернационализацией.
 * Для использования обязательно:
 * - имя таблицы с интернационализацией должны быть формата <имя основной таблицы>_translate
 * - имя первичного ключа в таблице интернационализации должно быть формата <имя основной таблицы>_id
 * - все поля из таблицы интернационализации (кроме ключа) нужно добавить в translateAttributes(), и в rules раздел safe
 * - добавить public свойства для каждого поля
 * Для принудительного переключения языка использовать changeLanguage() (отрабатывает только на объект)
 */
class TranslateActiveRecord extends ActiveRecord
{

    protected $language;
    public $translateTable = '_translate';

    /**
     * Метод нужно переопределить в модели указав в массиве список атрибутов находящихся в таблице интернационализации
     */
    public function translateAttributes(){
        return [''];
    }


    public function __construct($config=[]){
        $this->language = Yii::$app->language;
        parent::__construct($config);
    }

    /**
     * Изменяет язык вывода и сохранения свойств в текущем екземпляре модели
     */
    public function changeLanguage($language){
        $this->language = $language;
        $this->afterFind();
    }

    /**
     * Возвращает имя таблицы модели без спецсимволов
     */
    public function getModelTableName(){
        $name = str_replace("{{%","",$this->tableName());
        return $name = str_replace("}}","",$name);
    }

    /**
     * Возвращает имя таблицы интернационализации текущей модели
     */
    public function getTranslateTableName(){
        return Yii::$app->db->tablePrefix.$this->getModelTableName().$this->translateTable;
    }


    /**
     * Отрабатывает после сохранения модели.
     * Извлекает из модели свойства указанные в translateAttributes и сохраняет в таблицу интернационализации
     */
    public function afterSave(){

        $select = implode(",",$this->translateAttributes());

        $row = (new \yii\db\Query())
            ->select($select)
            ->from($this->getTranslateTableName())
            ->where([$this->getModelTableName()."_id" => $this->id,"lang" => $this->language])
            ->one();

        $data = [
            "lang" => $this->language,
            $this->getModelTableName()."_id" => $this->id
        ];

        foreach($this->translateAttributes() as $attr){
            $data[$attr] = $this->{$attr};
        }

        if($row){

            Yii::$app->db->createCommand()
                ->update($this->getTranslateTableName(), $data, [$this->getModelTableName()."_id" => $this->id, "lang" => $this->language])
                ->execute();

        }else{
            Yii::$app->db->createCommand()->insert($this->getTranslateTableName(), $data)->execute();
        }

    }

    /**
     * Отрабатывает после получения данных модели.
     * Извлекает из таблицы интернационализации модели свойства указанные в translateAttributes и записывает в свойства модели
     */
    public function afterFind(){


        $select = implode(",",$this->translateAttributes());


        $row = (new \yii\db\Query())
            ->select($select)
            ->from($this->getTranslateTableName())
            ->where([$this->getModelTableName()."_id" => $this->id,"lang" => $this->language])
            ->one();


        if($row){
            foreach($this->translateAttributes() as $attr){
                $this->{$attr} = $row[$attr];
            }
        }else{
            $row = (new \yii\db\Query())
                ->select($select)
                ->from($this->getTranslateTableName())
                ->where([$this->getModelTableName()."_id" => $this->id])
                ->one();

            foreach($this->translateAttributes() as $attr){
                $this->{$attr} = $row[$attr];
            }
        }

    }

}
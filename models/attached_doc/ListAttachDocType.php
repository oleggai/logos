<?php

/**
 * Файл класса ListAttachDocType
 * Справочник типов прикрепленных документов
 */

namespace app\models\attached_doc;

use Yii;
use app\models\common\CommonModel;
use yii\helpers\ArrayHelper;

/**
 * Класс ListAttachDocType
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property string $name_short_ru
 * @property string $name_short_en
 * @property string $name_short_uk
 * @property integer $visible
 * @property integer $state
 * @property string $name. Название типа документа в зависимости от языка приложения
 * @property string $name_short. Сокращенное название типа документа в зависимости от языка приложения
 */
class ListAttachDocType extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%list_attach_doctype}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['visible', 'state'], 'integer'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 255],
            [['name_short_ru', 'name_short_en', 'name_short_uk'], 'string', 'max' => 10],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('tariff', 'Code'),
            'name_ru' => Yii::t('tariff', 'Name (Rus.)'),
            'name_en' => Yii::t('tariff', 'Name (Eng.)'),
            'name_uk' => Yii::t('tariff', 'Name (Ukr.)'),
            'name_short_ru' => Yii::t('tariff', 'Name Short (Rus.)'),
            'name_short_en' => Yii::t('tariff', 'Name Short (Eng.)'),
            'name_short_uk' => Yii::t('tariff', 'Name Short (Ukr.)'),
            'visible' => Yii::t('tariff', 'Availability of choice'),
            'state' => Yii::t('tariff', 'State'),
        ];
    }

    /**
     * Получение списка типов прикрепленных документов в виде ассоциативного массива, где ключ - id,
     * значение - значение поля переданного параметром ('name_en' по-умолчанию)
     * @param bool $empty false
     * @param string $field name
     * @param string $lang current language
     * @return array
     */
    public static function getList($empty = false, $field = 'name', $lang = '') {

        if (empty($lang))
            $lang = \Yii::$app->language;

        if ($field == 'name')
            $field = $field . '_' . $lang;

        $arr = ListAttachDocType::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Возвращает название типа документа в зависимости от языка приложения
     * @return string
     */
    public function getName() {
        return $this->{'name_'.Yii::$app->language};
    }

    /**
     * Возвращает сокращенное название типа документа в зависимости от языка приложения
     * @return string
     */
    public function getName_short() {
        return $this->{'name_short_'.Yii::$app->language};
    }

    /**
     * Метод вызывается после сохранения сущности
     */
/*    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);
    }*/

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ru' => $this->name_ru,
            'name_uk' => $this->name_uk,
            'name_short_en' => $this->name_short_en,
            'name_short_ru' => $this->name_short_ru,
            'name_short_uk' => $this->name_short_uk,
            'state' => $this->state,
            'visibilityText' => $this->visibilityText
        ];
    }

}

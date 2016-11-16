<?php

/**
 * Файл класса модели EwState (Справочник состояний ЭН)
 */

namespace app\models\ew;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;

/**
 * Класс модели EwState
 * @author Гайдаенко Олег
 * @category ew
 * @property integer $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $visible
 */
class EwState extends CommonModel {
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%ew_state}}';
    }

    /**
     * Правила для атрибутов модели
     * @return array
     */
    public function rules() {
        return [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['name_en', 'name_uk', 'name_ru'], 'string', 'max' => 500],
            [['name_en', 'name_uk', 'name_ru'], 'unique'],
            [['state', 'visible'], 'integer']
        ];
    }

    public function attributeLabels() {
        return [
            'name_en' => \Yii::t('ew', 'Name En'),
            'name_ru' => \Yii::t('ew', 'Name Ru'),
            'name_uk' => \Yii::t('ew', 'Name Uk'),
        ];
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyEwState($params);
    }

    public function copyEwState($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $state = EwState::findOne(['id' => $params['id']]);
            if($state) {
                $this->attributes = $state->getAttributes();
            }
        }
    }

    /**
     * Возвращает список Ew State
     * @param bool $empty
     * @param string $field
     * @return array
     */
    public static function getList($empty = false, $field = 'name', $lang = '') {
        
        if(empty($lang))
            $lang = \Yii::$app->language;
        
        if($field == 'name') {
            $field=$field.'_'.$lang;
        }
        $arr = EwState::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;

    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);
        $this->saveSServiceData($insert);
    }

    /**
     * Возвращает данные для грида
     * @return array
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ru' => $this->name_ru,
            'name_uk' => $this->name_uk,
            'state' => $this->state,
            'visible' => $this->visible,
            'visibilityText' => $this->visibilityText
        ];
    }

    public function getGridOperations() {

        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app', 'Copy'),
        ];
    }

    public function getGridOperationsOptions() {

        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before' => true, 'tab_name_sufix' => 'copy'],
        ];
    }
}
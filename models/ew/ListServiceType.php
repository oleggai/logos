<?php
/**
 * Файл класса модели ListServiceType
 */

namespace app\models\ew;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;

/**
 * Класс модели ServiceTypeList
 * @author Гайдаенко Олег
 * @category ew
 * @property integer $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $name_short_en
 * @property string $name_short_ru
 * @property string $name_short_uk
 * @property string $visible
 */
class ListServiceType extends CommonModel {
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%list_service_type}}';
    }

    /**
     * Правила для атрибутов модели
     * @return array
     */
    public function rules() {
        return [
            [['name_en', 'name_ru', 'name_uk', 'name_short_en', 'name_short_uk', 'name_short_ru'], 'required'],
            [['name_en', 'name_uk', 'name_ru',], 'string', 'max' => 500],
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
            $this->copyServiceType($params);
    }

    public function copyServiceType($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $type = ListServiceType::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
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
            'name_short_en' => $this->name_short_en,
            'name_short_ru' => $this->name_short_ru,
            'name_short_uk' => $this->name_short_uk,
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
<?php

namespace app\models\ew;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;

/**
 * Модель вида доставки
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property string $name
 *
 * @property ExpressWaybill[] $expressWaybills
 */
class DeliveryType extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%delivery_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['visible', 'state'], 'integer'],
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 100],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ew', 'ID'),
            'name_ru' => Yii::t('ew', 'Name Ru'),
            'name_en' => Yii::t('ew', 'Name En'),
            'name_uk' => Yii::t('ew', 'Name Uk'),
        ];
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyDeliveryType($params);
    }

    public function copyDeliveryType($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $state = DeliveryType::findOne(['id' => $params['id']]);
            if($state) {
                $this->attributes = $state->getAttributes();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpressWaybills()
    {
        return $this->hasMany(ExpressWaybill::className(), ['delivery_type' => 'id']);
    }


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
            'state' => $this->state,
            'visibilityText' => $this->visibilityText
        ];
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);
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

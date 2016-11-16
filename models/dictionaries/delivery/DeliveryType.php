<?php

/**
 * Файл класса Delivery
 */

namespace app\models\dictionaries\delivery;

use app\models\ew\ExpressWaybill;
use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;
use app\models\dictionaries\tariff\ListTariff;

/**
 * Класс Delivery
 * 
 * @author Дмитрий Чеусов
 * @category delivery
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property integer $visible
 * @property integer $state
 *
 * @property ExpressWaybill[] $expressWaybills
 * @property ListTariffDeliveryType[] $listTariffDeliveryTypes
 */
class DeliveryType extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%delivery_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['visible', 'state'], 'integer'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 100],
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
            'visible' => Yii::t('tariff', 'Availability of choice'),
            'state' => Yii::t('tariff', 'State'),
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
            $type = DeliveryType::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpressWaybills() {
        return $this->hasMany(ExpressWaybill::className(), ['delivery_type' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListTariffDeliveryTypes() {
        return $this->hasMany(ListTariff::className(), ['tariff_id' => 'id']);
    }

    /**
     * Получение списка тарифных зон в виде ассоциативного массива, где ключ - id, 
     * значение - значение поля переданного параметром ('name_en' по-умолчанию)
     * @param string $field поле для отображения
     * @param boolean $empty
     * @return array массив зон
     */
    public static function getList($field = null, $empty = false) {

        if ($field == null) {
            $field = 'name_' . Yii::$app->language;
        }

        $arr = self::find()->where('visible = :visible', [':visible' => self::VISIBLE])->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty) {
            $r = [null => ''] + $r;
        }
        return $r;
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

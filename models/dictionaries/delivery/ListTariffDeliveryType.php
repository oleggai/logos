<?php

/**
 * Файл класса связей Tariff--Delivery
 */

namespace app\models\dictionaries\delivery;

use app\models\dictionaries\tariff\ListTariff;
use Yii;
use app\models\common\CommonModel;
use yii\helpers\ArrayHelper;

/**
 * Класс связей Tariff--Delivery
 * 
 * @author Дмитрий Чеусов
 * @category delivery
 * 
 * @property string $id
 * @property string $tariff_id
 * @property string $delivery_type
 *
 * @property ListTariff $tariff
 * @property DeliveryType $deliveryType
 */
class ListTariffDeliveryType extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yii2_list_tariff_delivery_type';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['delivery_type'], 'required'],
            [['tariff_id', 'delivery_type'], 'integer'],
            [['delivery_type'], 'validateUnique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('tariff', 'Code'),
            'tariff_id' => Yii::t('tariff', 'Related tariff'),
            'delivery_type' => Yii::t('tariff', 'Related delivery type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff() {
        return $this->hasOne(ListTariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryType() {
        return $this->hasOne(DeliveryType::className(), ['id' => 'delivery_type']);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'delivery_type' => $this->delivery_type,
            'state' => self::STATE_CREATED,
        ];
    }

    public function validateUnique($attribute, $params) {

        $types = self::find()
            ->where('id != :id AND tariff_id = :tariff_id AND delivery_type = :delivery_type',
                [':id' => $this->id, ':tariff_id' => $this->tariff_id, ':delivery_type' => $this->delivery_type,])
            ->all();
        if ($types) {
            $this->addError($attribute, Yii::t('error', $this->getAttributeLabel('delivery_type') . ' ' . 'должно быть уникальным значением'));
        }
    }
}

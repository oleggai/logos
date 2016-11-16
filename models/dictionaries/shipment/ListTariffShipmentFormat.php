<?php

/**
 * Файл класса связей Tariff--Shipment
 */

namespace app\models\dictionaries\shipment;

use app\models\dictionaries\tariff\ListTariff;
use Yii;
use app\models\common\CommonModel;

/**
 * Класс связей Tariff--Shipments
 * 
 * @author Дмитрий Чеусов
 * @category shipment
 *
 * @property string $id
 * @property string $tariff_id
 * @property string $shipment_format
 *
 * @property ListTariff $tariff
 * @property ShipmentFormat $shipmentFormat
 */
class ListTariffShipmentFormat extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yii2_list_tariff_shipment_format';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['shipment_format'], 'required'],
            [['tariff_id', 'shipment_format'], 'integer'],
            [['shipment_format'], 'validateUnique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('tariff', 'Code'),
            'tariff_id' => Yii::t('tariff', 'Related tariff'),
            'shipment_format' => Yii::t('tariff', 'Related shipment format'),
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
    public function getShipmentFormat() {
        return $this->hasOne(ShipmentFormat::className(), ['id' => 'shipment_format']);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'shipment_format' => $this->shipment_format,
            'state' => self::STATE_CREATED,
        ];
    }

    public function validateUnique($attribute, $params) {

        $types = self::find()
            ->where('id != :id AND tariff_id = :tariff_id AND shipment_format = :shipment_format',
                [':id' => $this->id, ':tariff_id' => $this->tariff_id, ':shipment_format' => $this->shipment_format,])
            ->all();
        if ($types) {
            $this->addError($attribute, Yii::t('error', $this->getAttributeLabel('shipment_format') . ' ' . 'должно быть уникальным значением'));
        }
    }
}

<?php

/**
 * файл класса связей Tariff--Service
 */

namespace app\models\dictionaries\service;

use app\models\dictionaries\tariff\ListTariff;
use app\models\ew\ListServiceType;
use Yii;
use app\models\common\CommonModel;

/**

 * Класс связей Tariff--Service
 * 
 * @author Дмитрий Чеусов
 * @category service
 * 
 * @property string $id
 * @property string $tariff_id
 * @property string $service_type
 *
 * @property ListTariff $tariff
 * @property ListServiceType $serviceType
 */
class ListTariffServiceType extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yii2_list_tariff_service_type';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['service_type'], 'required'],
            [['tariff_id', 'service_type'], 'integer'],
            [['service_type'], 'validateUnique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('tariff', 'Code'),
            'tariff_id' => Yii::t('tariff', 'Related tariff'),
            'service_type' => Yii::t('tariff', 'Related service type'),
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
    public function getServiceType() {
        return $this->hasOne(ListServiceType::className(), ['id' => 'service_type']);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'service_type' => $this->service_type,
            'state' => self::STATE_CREATED,
        ];
    }

    public function validateUnique($attribute, $params) {

        $types = self::find()
            ->where('id != :id AND tariff_id = :tariff_id AND service_type = :service_type',
                [':id' => $this->id, ':tariff_id' => $this->tariff_id, ':service_type' => $this->service_type,])
            ->all();
        if ($types) {
            $this->addError($attribute, Yii::t('error', $this->getAttributeLabel('service_type') . ' ' . 'должно быть уникальным значением'));
        }
    }
}

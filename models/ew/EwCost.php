<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use app\models\counterparty\Counterparty;
use Yii;

/**
 * Модель стоимости накладной
 *
 * @property string $ew_id Ссылка на накладную
 * @property string $int_delivery_cost_full_usd Полная, доллар
 * @property string $int_delivery_cost_full Полная
 * @property string $int_delivery_full_currency Валюта
 * @property string $int_delivery_cost_full_uah Полная, грн
 * @property string $int_delivery_cost_css_uah От ЦСС, грн
 * @property string $int_delivery_cost_css_usd До ЦСС, доллар
 * @property integer $int_delivery_payer Плательщик по международной доставке
 * @property integer $int_delivery_payment_type Тип оплаты международной доставки
 * @property string $clearance_cost Стоимость растаможивания, грн
 * @property string $customs_clearance_charge Стоимость таможенных пошлин, грн
 * @property integer $clearance_payer Плательщик по растаможиванию
 * @property integer $clearance_payment_type Тип оплаты растаможивания
 * @property string $total_pay_cost_uah Итого стоимость к оплате, грн
 * @property string $third_party Третье лицо
 * @property integer $fact_pay_int_deliv Факт оплаты по международной доставке,
 * @property string $third_party_ccs Третье лицо ТБУ,
 * @property integer $fact_pay_ccs Факт оплаты по ТБУ,
 * @property string $third_party_id Третье лицо
 * @property string $third_party_ccs_id Третье лицо ТБУ,
 *
 * @property ExpressWaybill $ew Модель накладной
 * @property FactPayment $factPayIntDeliv Модель названия оплаты
 * @property FactPayment $factPayCcs Модель факта оплаты ТБУ
 * @property PayerType $clearancePayer Модель плательщика по растаможиванию
 * @property FormPayment $clearancePaymentType Модель типа оплаты растаможивания
 * @property string $thirdPartyName Третье лицо
 * @property string $thirdPartyCcsName Третье лицо ТБУ
 * @property integer $inner_shipment Внутренняя отправка
 * @property Counterparty $thirdParty Третье лицо
 * @property Counterparty $thirdPartyCcs Третье лицо ТБУ
 */
class EwCost extends CommonModel {

    public function init() {

        parent::init();

        $this->int_delivery_full_currency = 2;
    }

    /**
     * Имя таблицы в базе данных
     */
    public static function tableName() {
        return '{{%ew_cost}}';
    }

    /**
     * Правила для полей
     */
    public function rules() {
        return [
            [['ew_id', 'int_delivery_cost_full', 'int_delivery_full_currency'], 'required'],
            [['ew_id', 'int_delivery_payer', 'int_delivery_payment_type', 'clearance_payer', 'clearance_payment_type', 'fact_pay_int_deliv', 'fact_pay_ccs', 'inner_shipment'], 'integer'],
            [['int_delivery_cost_full_usd', 'int_delivery_cost_full_uah', 'int_delivery_cost_css_uah', 'int_delivery_cost_css_usd', 'clearance_cost', 'customs_clearance_charge', 'total_pay_cost_uah'], 'number'],
            [['third_party_id', 'third_party_ccs_id'], 'integer'],
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels() {
        return [
            'ew_id' => Yii::t('ew', 'Ew ID'),
            'int_delivery_cost_full_usd' => Yii::t('ew', 'Int Delivery Cost Full Usd'),
            'int_delivery_cost_full_uah' => Yii::t('ew', 'Int Delivery Cost Full Uah'),
            'int_delivery_cost_css_uah' => Yii::t('ew', 'Int Delivery Cost Css Uah'),
            'int_delivery_cost_css_usd' => Yii::t('ew', 'Int Delivery Cost Css Usd'),
            'int_delivery_payer' => Yii::t('ew', 'Int Delivery Payer'),
            'int_delivery_payment_type' => Yii::t('ew', 'Int Delivery Payment Type'),
            'clearance_cost' => Yii::t('ew', 'Clearance Cost'),
            'customs_clearance_charge' => Yii::t('ew', 'Customs Clearance Charge'),
            'clearance_payer' => Yii::t('ew', 'Clearance Payer'),
            'clearance_payment_type' => Yii::t('ew', 'Clearance Payment Type'),
            'total_pay_cost_uah' => Yii::t('ew', 'Total Pay Cost Uah'),
            'third_party_id' => Yii::t('ew', 'Third Party'),
            'fact_pay_int_deliv' => Yii::t('ew', 'Fact of payment of international delivery'),
            'third_party_ccs_id' => Yii::t('ew', 'Third Party'),
            'fact_pay_ccs' => Yii::t('ew', 'Fact of payment of CCS'),
            'inner_shipment' => Yii::t('ew', 'Внутренняя отправка'),
        ];
    }

    /**
     * Метод получения названия оплаты
     */
    public function getFactPayIntDeliv() {
        return $this->hasOne(FactPayment::className(), ['id' => 'fact_pay_int_deliv']);
    }

    /**
     * Метод получения названия оплаты ТБУ
     */
    public function getFactPayCcs() {
        return $this->hasOne(FactPayment::className(), ['id' => 'fact_pay_ccs']);
    }

    /**
     * Метод получения названия Clearance Payer
     */
    public function getClearancePayer() {
        return $this->hasOne(PayerType::className(), ['id' => 'clearance_payer']);
    }

    /**
     * Метод получения названия Clearance Payment Type
     */
    public function getClearancePaymentType() {
        return $this->hasOne(FormPayment::className(), ['id' => 'clearance_payment_type']);
    }

    /**
     * Метод получения модели накладной
     */
    public function getEw() {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    /**
     * Метод получения модели третьего лица
     */
    public function getThirdParty() {
         return Counterparty::findOne(['id' => $this->third_party_id]);
    }

    /**
     * Метод получения модели третьего лица
     */
    public function getThirdPartyCcs() {
        return Counterparty::findOne(['id' => $this->third_party_ccs_id]);
    }

    /**
     * Метод получения названия третьего лица
     */
    public function getThirdPartyName() {
        return Counterparty::findOne(['id' => $this->third_party_id])->counterpartyName;
    }

    /**
     * Метод получения названия третьего лица
     */
    public function getThirdPartyCcsName() {
        return Counterparty::findOne(['id' => $this->third_party_ccs_id])->counterpartyName;
    }

}

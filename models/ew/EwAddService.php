<?php

namespace app\models\ew;

use Yii;
use app\models\common\CommonModel;
use app\models\dictionaries\currency\Currency;

/**
 * This is the model class for table "{{%ew_add_service}}".
 *
 * @property string $id
 * @property string $ew_id Ссылка на EW
 * @property string $service_name Название услуги
 * @property integer $service_cost Стоимость услуги
 * @property string $currency Код валюты
 * @property string $service_cost_uah Стоимость услуги, грн
 * @property string $payer Плательщик по дополнительным услугам
 * @property string $third_party Третье лицо
 * @property string $form_pay Форма оплаты по дополнительной услуге
 * @property string $fact_pay Факт оплаты по дополнительной услуге
 *
 * @property string $currencyName Название валюты
 * @property string $payerName Плательщик по дополнительным услугам (название)
 * @property string $formPayName Форма оплаты по дополнительной услуге (название)
 * @property string $factPayName Факт оплаты по дополнительной услуге (название)
 *
 * @property Currency $cur
 * @property ExpressWaybill $ew

 */

class EwAddService extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ew_add_service}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['ew_id'], 'required'],
            [['ew_id', 'currency', 'payer', 'form_pay', 'fact_pay', 'third_party_id'], 'integer'],
            [['service_cost', 'service_cost_uah'], 'number'],
            [['service_name', 'third_party'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ew', 'ID'),
            'ew_id' => Yii::t('ew', 'Ew ID'),
            'service_name' => Yii::t('ew', 'Service Name'),
            'service_cost' => Yii::t('ew', 'Service Cost'),
            'currency' => Yii::t('ew', 'Currency'),
            'currencyName' => Yii::t('ew', 'Currency'),
            'service_cost_uah' => Yii::t('ew', 'Service Cost Uah'),
            'payer' => Yii::t('ew', 'Payer'),
            'payerName' => Yii::t('ew', 'Payer'),
            'third_party' => Yii::t('ew', 'Third Party'),
            'third_party_id' => Yii::t('ew', 'Third Party'),
            'form_pay' => Yii::t('ew', 'Form Pay'),
            'formPayName' => Yii::t('ew', 'Form Pay'),
            'fact_pay' => Yii::t('ew', 'Fact Pay'),
            'factPayName' => Yii::t('ew', 'Fact Pay'),
        ];
    }

    /**
     * Метод получения модели валюты
     */
    public function getCur()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency']);
    }

    /**
     * Метод получения названия валюты
     */
    public function getCurrencyName()
    {
        return $this->cur->nameShort;
    }

    /**
     * Метод получения названия плательщика
     */
    public function getPayerName()
    {
        return $this->ew->payerTypeList[$this->payer];
    }

    /**
     * Метод получения названия формы оплаты
     */
    public function getFormPayName()
    {
        return $this->ew->paymentTypeList[$this->form_pay];
    }

    /**
     * Метод получения названия факта оплаты
     */
    public function getFactPayName()
    {
        return $this->ew->paymentFactList[$this->fact_pay];
    }


    /**
     * Метод получения модели накладной
     */
    public function getEw()
    {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'ew_id'=>$this->ew_id,
            'service_name'=>$this->service_name,
            'service_cost'=>$this->service_cost,
            'currency'=>$this->currency,
            'currencyName'=>$this->currencyName,
            'service_cost_uah'=> $this->service_cost_uah,
            'payer'=>$this->payer,
            'payerName'=>$this->payerName,
            'third_party'=>$this->third_party,
            'third_party_id'=>$this->third_party_id,
            'form_pay'=>$this->form_pay,
            'formPayName'=>$this->formPayName,
            'fact_pay'=>$this->fact_pay,
            'factPayName'=>$this->factPayName,
        ];
    }

}

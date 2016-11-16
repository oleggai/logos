<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;

/**
 * Модель инвоиса накладной
 *
 * @property string $id Идентификатор
 * @property string $ew_id ССылка на накладную
 * @property string $invoice_num Номер инфоиса
 * @property string $invoice_date Дата инвоиса
 * @property string $invoice_export_purpose Цель экспорта
 * @property string $currency Валюта
 * @property string $total_weight Общий вес
 * @property string $invoice_type Тип инвойса
 * @property string $invoice_cost Стоимость инвойса
 * @property integer $condition_incoterms Ссылка на справочник - Условия доставки Инкотермс
 * @property integer $incotermsName текст - Условия доставки Инкотермс
 * @property array $invoiceStHotecList Массив заявок HOTEC
 * @property string $cparty_counterparty Контрагент
 * @property string $cparty_assignee Контактное лицо
 * @property string $cparty_postcode Поштовый индекс контрагента
 * @property integer $cparty_country Контактное лицо
 * @property string $cparty_city Населенный пункт
 * @property string $cparty_address Адрес контрагента
 * @property string $cparty_phone_num Телефон
 * @property string $cparty_email Ел пошта контрагента
 *
 * @property integer $counterparty_id Контрагент - сcылка на контрагента, ФИО, Тип лица, ID,Код ЕГРПОУ
 * @property integer $cp_address_id Страна, Регион,Город,Индекс,Адрес. - сслка на адрес контрагента
 * @property integer $cp_contactpers_id Представитель - ссылка на контактное лицо контрагента
 * @property integer $cp_phonenum_id Телефон - ссылка на телефон контрагента
 * @property integer $cp_email_id email - ссылка на email контрагента
 *
 * @property ExpressWaybill $ew Модель ЭН
 * @property InvoiceTypes $invoiceType Модель типа инвойса
 * @property InvoicePosition[] $invoicePositions Массив позиций инвоиса
 */
class Invoice extends CommonModel
{

    const PROFORMA_INVOICE_ID   = 1;
    const COMMERCIAL_INVOICE_ID = 2;

    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%invoice}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            /*
            [['counterparty_id'], 'required',
                'when' => function($model) {
                    // Если все поля не заполнены, данные не валидировать
                    if( !$model->cparty_counterparty &&
                        !$model->cparty_assignee &&
                        !$model->cparty_type &&
                        !$model->cparty_postcode &&
                        !$model->cparty_phone_num &&
                        !$model->cparty_country &&
                        !$model->cparty_city &&
                        !$model->cparty_address) {return false;}
                    // Если хоть одно поле заполнено, то нужно заполнить остальные (блок контрагента)
                    else {
                        return true;
                    }
                }],
            */
            [['ew_id', 'condition_incoterms'], 'integer'],
            [['invoice_date'], 'safe'],
            [['invoice_num'], 'string', 'max' => 100],
            [['invoice_export_purpose'], 'string', 'max' => 50],
            [['currency'], 'integer'],
            [['total_weight'], 'number'],
            [['invoice_type'], 'integer'],
            [['invoice_cost'], 'number'],
            [['counterparty_id', 'cp_address_id', 'cp_contactpers_id', 'cp_phonenum_id', 'cp_email_id'], 'integer']
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ew', 'ID'),
            'ew_id' => Yii::t('ew', 'Ew ID'),
            'invoice_num' => Yii::t('ew', 'Invoice Num'),
            'invoice_date' => Yii::t('ew', 'Invoice Date'),
            'invoice_export_purpose' => Yii::t('ew', 'Invoice Export Purpose'),
            'currency' => Yii::t('ew', 'Invoice Currency'),
            'total_weight' => Yii::t('ew', 'Invoice Total Weight'),
            'invoice_type' => Yii::t('ew', 'Invoice Type'),
            'invoice_cost' => Yii::t('ew', 'Invoice Cost'),
            'statement_notes'=> Yii::t('ew', 'Statement Notes'),

        ];
    }

    /**
     * Метод получения модели накладной
     */
    public function getEw()
    {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    /**
     * Метод получения позиций накладной
     */
    public function getInvoicePositions()
    {
        return $this->hasMany(InvoicePosition::className(), ['inv_id' => 'id']);
    }

    public function getInvoiceStHotec() {

        return $this->hasMany(InvoiceStHotec::className(), ['invoice_id' => 'id']);
    }

    public function getInvoiceStHotecList() {
        $list = [];
        $lang = Yii::$app->language;
        $field = 'name_'.$lang;
        $invoiceStNotes = $this->invoiceStHotec;
        foreach($invoiceStNotes as $invoiceStNote) {
            $list[] = $invoiceStNote->listStatementNotes->{$field};
        }
        return $list;
    }

    public function getInvoiceType() {

        return $this->hasOne(InvoiceTypes::className(), ['id' => 'invoice_type']);
    }

    public function getIncotermsName() {
        $lang = Yii::$app->language;
        $field = 'name_'.$lang;
        $conditionIncoterms = ListConditionIncoterms::findOne(['id' => $this->condition_incoterms]);
        return $conditionIncoterms->{$field};
    }

    public static function getPattern($patternLang = 'en-uk') {
        $pattern = [
            'en-uk' => [
                'proforma_invoice_text'  => 'INVOICE/ ІНВОЙС',
                'commerial_invoice_text' => 'COMMERCIAL INVOICE/ КОМЕРЦІЙНИЙ ІНВОЙС',
                'invoice_number' => 'INVOICE NUMBER/ НОМЕР ІНВОЙСУ: ',
                'invoice_date' => 'INVOICE DATE/ ДАТА ІНВОЙСУ: ',
                'delivery_address' => 'DELIVERY ADDRESS/ АДРЕС ДОСТАВКИ ',
                'number_ew' => 'INTERNATIONAL WAYBILL NUMBER/ НОМЕР ЕКСПРЕС-НАКЛАДНОЇ: ',
                'shipper' => 'SHIPPER/ ВІДПРАВНИК',
                'consignee' => 'CONSIGNEE/ ОТРИМУВАЧ',
                'contact_name' => 'Contact name/ Контактна особа:',
                'pib' => "Shipper Name/ Ім'я відправника:",
                'postcode' => 'Postcode/ Поштовий індекс:',
                'country' => 'Country/ Країна:',
                'city' => 'City/ Місто:',
                'address' => 'Address/ Адреса:',
                'tel_fax' => 'Tel./ Fax/ Телефон/Факс:',
                'shipment_information' => 'SHIPMENT INFORMATION/ ІНФОРМАЦІЯ ПРО ВІДПРАВЛЕННЯ',
                'full_description' => 'Full description of shipment/ Повний опис відправлення',
                'country_of_orign' => 'Country of origin/ Країна походження',
                'units_of_measure' => 'Units of measure/ Одиниці виміру',
                'quantity' => 'Quantity/ Кількість',
                'cost_per_unit' => 'Cost per unit/ Вартість за одиницю',
                'sub_total_value' => 'Sub total value/ Загальна вартість',
                'total_value_currency' => 'Total value & Currency/ Вартість і валюта для митного декларування: ',
                'delivery_cost_currency' => 'Delivery cost & Currency/ Вартість і валюта доставки:',
                'total_weight' => 'Total weight/ Загальна вага:',
                'n_of_pieces' => 'N of Pieces/ Кількість місць:',
                'reason_for_export' => 'Reason for export/ Мета експорту:',
                'terms_of_delivery' => 'Terms of delivery/ Умови поставки:',

                'signature' => 'Signature/ Підпис',
                'signature_fio' => 'Name/ ПІБ'
            ],
            'en-ru' => [
                'proforma_invoice_text'  => 'INVOICE/ ИНВОЙС',
                'commerial_invoice_text' => 'COMMERCIAL INVOICE/ КОММЕРЧЕСКИЙ ИНВОЙС',
                'invoice_number' => 'INVOICE NUMBER/ НОМЕР ИНВОЙСА: ',
                'invoice_date' => 'INVOICE DATE/ ДАТА ИНВОЙСА: ',
                'delivery_address' => 'DELIVERY ADDRESS/ АДРЕС ДОСТАВКИ ',
                'number_ew' => 'INTERNATIONAL WAYBILL NUMBER/ НОМЕР ЭКСПРЕСС-НАКЛАДНОЙ ',
                'shipper' => 'SHIPPER/ ОТПРАВИТЕЛЬ',
                'consignee' => 'CONSIGNEE/ ПОЛУЧАТЕЛЬ',
                'contact_name' => 'Contact name/ Контактное лицо:',
                'pib' => "Shipper Name/ Имя отправителя:",
                'postcode' => 'Postcode/ Почтовый индекс:',
                'country' => 'Country/ Страна:',
                'city' => 'City/ Город:',
                'address' => 'Address/ Адрес:',
                'tel_fax' => 'Tel./ Fax/ Телефон/Факс:',
                'shipment_information' => 'SHIPMENT INFORMATION/ ИНФОРМАЦИЯ ПРО ОТПРАВЛЕНИЕ',
                'full_description' => 'Full description of shipment/ Полное описание отправления',
                'country_of_orign' => 'Country of origin/ Страна происхождения',
                'units_of_measure' => 'Units of measure/ Единицы измерения',
                'quantity' => 'Quantity/ Количество',
                'cost_per_unit' => 'Cost per unit/ Стоимость за единицу',
                'sub_total_value' => 'Sub total value/ Общая стоимость',
                'total_value_currency' => 'Total value & Currency/ Стоимость и валюта для таможенного декларирования: ',
                'delivery_cost_currency' => 'Delivery cost & Currency/ Стоимость и валюта доставки:',
                'total_weight' => 'Total weight/ Общий вес:',
                'n_of_pieces' => 'N of Pieces/ Количество мест:',
                'reason_for_export' => 'Reason for export/ Цель экспорта:',
                'terms_of_delivery' => 'Terms of delivery/ Условия поставки:',

                'signature' => 'Signature/ Подпись',
                'signature_fio' => 'Name/ ФИО'
            ],
            'en'    => [
                'proforma_invoice_text'  => 'INVOICE',
                'commerial_invoice_text' => 'COMMERCIAL INVOICE',
                'invoice_number' => 'INVOICE NUMBER: ',
                'invoice_date' => 'INVOICE DATE: ',
                'delivery_address' => 'DELIVERY ADDRESS',
                'number_ew' => 'INTERNATIONAL WAYBILL NUMBER: ',
                'shipper' => 'SHIPPER',
                'consignee' => 'CONSIGNEE',
                'contact_name' => 'Contact name:',
                'pib' => "Shipper Name:",
                'postcode' => 'Postcode:',
                'country' => 'Country:',
                'city' => 'City:',
                'address' => 'Address:',
                'tel_fax' => 'Tel./ Fax:',
                'shipment_information' => 'SHIPMENT INFORMATION',
                'full_description' => 'Full description of shipment',
                'country_of_orign' => 'Country of origin',
                'units_of_measure' => 'Units of measure',
                'quantity' => 'Quantity',
                'cost_per_unit' => 'Cost per unit',
                'sub_total_value' => 'Sub total value',
                'total_value_currency' => 'Total value & Currency:',
                'delivery_cost_currency' => 'Delivery cost & Currency:',
                'total_weight' => 'Total weight:',
                'n_of_pieces' => 'N of Pieces:',
                'reason_for_export' => 'Reason for export:',
                'terms_of_delivery' => 'Terms of delivery:',

                'signature' => 'Signature',
                'signature_fio' => 'Name'
            ],
            'ru'    => [
                'proforma_invoice_text'  => 'ИНВОЙС',
                'commerial_invoice_text' => 'КОММЕРЧЕСКИЙ ИНВОЙС',
                'invoice_number' => 'НОМЕР ИНВОЙСА: ',
                'invoice_date' => 'ДАТА ИНВОЙСА: ',
                'delivery_address' => 'АДРЕС ДОСТАВКИ ',
                'number_ew' => 'НОМЕР ЭКСПРЕСС-НАКЛАДНОЙ ',
                'shipper' => 'ОТПРАВИТЕЛЬ',
                'consignee' => 'ПОЛУЧАТЕЛЬ',
                'contact_name' => 'Контактное лицо:',
                'pib' => 'Имя отправителя:',
                'postcode' => 'Почтовый индекс:',
                'country' => 'Страна:',
                'city' => 'Город:',
                'address' => 'Адрес:',
                'tel_fax' => 'Телефон/ Факс:',
                'shipment_information' => 'ИНФОРМАЦИЯ ПРО ОТПРАВЛЕНИЕ',
                'full_description' => 'Полное описание отправления',
                'country_of_orign' => 'Страна происхождения',
                'units_of_measure' => 'Единицы измерения',
                'quantity' => 'Количество',
                'cost_per_unit' => 'Стоимость за единицу',
                'sub_total_value' => 'Общая стоимость',
                'total_value_currency' => 'Стоимость и валюта для таможенного декларирования: ',
                'delivery_cost_currency' => 'Стоимость и валюта доставки:',
                'total_weight' => 'Общий вес:',
                'n_of_pieces' => 'Количество мест:',
                'reason_for_export' => 'Цель экспорта:',
                'terms_of_delivery' => 'Условия поставки:',

                'signature' => 'Подпись',
                'signature_fio' => 'ФИО'
            ],
            'uk'    => [
                'proforma_invoice_text'  => 'ІНВОЙС',
                'commerial_invoice_text' => 'КОМЕРЦІЙНИЙ ІНВОЙС',
                'invoice_number' => 'НОМЕР ІНВОЙСУ: ',
                'invoice_date' => 'ДАТА ІНВОЙСУ: ',
                'delivery_address' => 'АДРЕСА ДОСТАВКИ ',
                'number_ew' => 'НОМЕР ЕКСПРЕС-НАКЛАДНОЇ: ',
                'shipper' => 'ВІДПРАВНИК',
                'consignee' => 'ОТРИМУВАЧ',
                'contact_name' => 'Контактна особа:',
                'pib' => "Ім'я відправника:",
                'postcode' => 'Поштовий індекс:',
                'country' => 'Країна:',
                'city' => 'Місто:',
                'address' => 'Адреса:',
                'tel_fax' => 'Телефон/ Факс:',
                'shipment_information' => 'ІНФОРМАЦІЯ ПРО ВІДПРАВЛЕННЯ',
                'full_description' => 'Повний опис відправлення',
                'country_of_orign' => 'Країна походження',
                'units_of_measure' => 'Одиниці виміру',
                'quantity' => 'Кількість',
                'cost_per_unit' => 'Вартість за одиницю',
                'sub_total_value' => 'Загальна вартість',
                'total_value_currency' => 'Вартість і валюта для митного декларування: ',
                'delivery_cost_currency' => 'Вартість і валюта доставки:',
                'total_weight' => 'Загальна вага:',
                'n_of_pieces' => 'Кількість місць:',
                'reason_for_export' => 'Мета експорту:',
                'terms_of_delivery' => 'Умови поставки:',

                'signature' => 'Підпис',
                'signature_fio' => 'ПІБ'
            ]
        ];
        return $pattern[$patternLang];
    }
}

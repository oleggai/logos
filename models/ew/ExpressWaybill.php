<?php

namespace app\models\ew;

use app\classes\DateTimeFormatter;
use app\classes\DocumentStorage;
use app\models\counterparty\CounterpartyLegalEntity;
use app\models\counterparty\CounterpartyManualAdress;
use app\models\common\CommonModel;
use app\models\counterparty\CounterpartyContactPers;
use app\models\counterparty\CounterpartyContactPersEmail;
use app\models\counterparty\CounterpartyContactPersPhones;
use app\models\counterparty\CounterpartyPrivatPers;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\currency\Currency;
use app\models\common\DateFormatBehavior;
use app\models\dictionaries\employee\Employee;
use app\models\dictionaries\employee\JobPosition;
use app\models\dictionaries\events\Event;
use app\models\dictionaries\events\EwHistoryEvents;
use app\models\manifest\Manifest;
use app\models\common\ShortDateFormatBehavior;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\dictionaries\warehouse\ListCargoType;
use app\models\dictionaries\address\ListCity;
use app\models\common\Langs;
use app\models\counterparty\Counterparty;
use app\models\dictionaries\service\ServiceType;
use app\models\counterparty\ListPersonType;
use app\models\dictionaries\warehouse\ListWarehouse;

/**
 * Модель накладной
 *
 * @property integer $id Идентификатор
 * @property string $ew_num Номер ЭН
 * @property integer $state Состояние ЭН
 * @property string $date Дата ЭН
 * @property string $est_delivery_date Расчетная дата доставки
 * @property integer $service_type Тип услуги
 * @property integer $primary_num Номер первичной ЭН
 * @property string $primary_date Дата первичной ЭН
 * @property integer $order_num Номер заказа
 * @property string $order_date Дата заказа
 * @property integer $sender_counterparty_id ID Контрагента
 * @property string $sender_assignee Представитель
 * @property string $sender_phone_num Телефон
 * @property string $sender_address Адрес
 * @property string $sender_email E-mail
 * @property integer $sender_type Тип отправителя
 * @property string $sender_id Receiver ID
 * @property string $sender_edrpou Код ЕГРПОУ
 * @property integer $receiver_counterparty_id ID Контрагента
 * @property string $receiver_assignee Представитель
 * @property string $receiver_phone_num Телефон
 * @property string $receiver_address Адрес
 * @property string $receiver_email E-mail
 * @property integer $receiver_type Тип отправителя
 * @property string $receiver_id Receiver ID
 * @property string $receiver_edrpou Код ЕГРПОУ
 * @property integer $shipment_type Вид отправления
 * @property string $total_dimensional_weight_kg Общий объемный вес, кг
 * @property string $total_actual_weight_kg Общий фактический вес, кг
 * @property string $general_desc Общее описание отправления
 * @property string $declared_cost Объявленная стоимость
 * @property string $declared_currency Валюта
 * @property string $customs_declaration_cost Стоимость таможенного декларирования
 * @property string $customs_declaration_currency Валюта таможенного декларирования
 * @property integer $payer_type Плательщик по ЭН
 * @property string $payer_third_party Третье лицо //старое
 * @property string $payer_third_party_id Третье лицо
 * @property integer $payer_payment_type Форма оплаты
 * @property string $closing_date Дата закрытия
 * @property string $closing_sending_receiver Представитель
 * @property integer closing_receiver_post_id Должность закрывшего - Ссылка на справочник должностей
 * @property string $closing_issued_shipment Выдал отправления
 * @property string $closing_add_shipment_info Дополнительная информация по ЭН
 * @property integer $closing_receiver_doc_type Вид документа
 * @property string $closing_receiver_doc_serial_num Серия документа
 * @property string $closing_doc_num Номер документа
 * @property string $closing_doc_issue_date Дата выдачи документа
 * @property string $closing_shipment_notes Примечания
 * @property string $storage_expiration_date Дата окончания срока хранения МЕВ
 * @property string $issue_css_responsible_pers Фамилия работника ЦСС, выдавшего МЕВ
 * @property string $doc_info_for_package_issue Название, номер и дата документа на основании которого МЕВ випущен с места хранения
 * @property string $doc_info_for_customs Название, номер и дата документа на основании которого выполнено таможенное оформление предметов в МЕВ
 * @property string $rec_num_allowed_issue Номер ОНП должностного лица, которое позволило выдачу МЕВ
 * @property string $cargo_est_weight_kg Расчетный вес, кг
 * @property string $ew_type Тип ЭН
 * @property integer $customs_brokerage Таможенно-брокерские услуги
 * @property string $picked_shipment Принял отправление
 * @property string $dimen_cntrl_weight_kg Объемный контрольный вес, кг
 * @property string $delivery_type Вид доставки
 * @property string $actual_cntrl_weight_kg Фактический контрольный вес, кг
 * @property string $shipment_format Формат отправления
 * @property EwType $ewType Тип ЭН
 * @property DeliveryType $deliveryType Вид доставки
 * @property ServiceType $serviceType Вид доставки
 * @property ShipmentFormat $shipmentFormat Формат отправления
 * @property Counterparty $receiverCounterparty Контрагент
 * @property Counterparty $senderCounterparty Контрагент
 * @property Counterparty $payerThirdPartyCounterparty Контрагент
 * @property string $receiver_cp_contactpers_job_position
 *
 * @property EwCost[] $ewCosts Список цен
 * @property EwPlace[] $ewPlaces Список мест
 * @property EwPlace[] $ewPlacesRel Список связанных мест
 * @property InvoicePosition[] $ewPositions Список позиций инвоисов
 * @property Country $senderCountry Модель страны отрпавителя
 * @property ListCity $senderCity Модель города отрпавителя
 * @property Country $receiverCountry Модель страны получателя
 * @property ListCargoType $cargoType cargo type
 * @property Currency $declaredCurrency Модель валюты
 * @property Currency $customsDeclarationCurrency Модель валюты
 * @property Employee $closingIssuedShipment  Модель представителя
 * @property Invoice[] $invoices Список инвоисов
 *
 * @property mixed stateList Список доступных состояний накладной
 * @property mixed serviceTypeList Список доступных типов услуги
 * @property mixed personTypeList Список доступных типов отправителя
 * @property mixed shipmentTypeList  Список доступных видов отправления
 * @property mixed payerTypeList Список доступных типов плательшиков
 * @property mixed paymentTypeList Список доступных типов оплаты
 * @property mixed docTypeList Список доступных типов документов
 * @property mixed paymentFactList Список признаков оплаты
 *
 * @property mixed ewPlacesCount Общее кол-во мест в накладной
 * @property mixed ewPositionsArray Позиции инвоисов в виде массива
 *
 *  свойства связанные со стоимостью (см модель EwCost)
 * @property string $int_delivery_cost_full_usd
 * @property string $int_delivery_cost_full
 * @property string $int_delivery_full_currency
 * @property string $int_delivery_cost_full_uah
 * @property string $int_delivery_cost_css_uah
 * @property string $int_delivery_cost_css_usd
 * @property integer $int_delivery_payer
 * @property integer $int_delivery_payment_type
 * @property string $clearance_cost
 * @property string $customs_clearance_charge
 * @property integer $clearance_payer
 * @property integer $clearance_payment_type
 * @property string $total_pay_cost_uah
 * @property string $third_party Третье лицо, имя
 * @property string $third_party_id Третье лицо
 * @property integer $fact_pay_int_deliv Факт оплаты по международной доставке,
 * @property string $third_party_ccs Третье лицо ТБУ, имя
 * @property string $third_party_ccs_id Третье лицо ТБУ
 * @property integer $fact_pay_ccs Факт оплаты по ТБУ,
 * @property integer $inner_shipment Внутренняя отправка
 *  ~свойства связанные со стоимостью (см модель EwCost)
 *
 * @property EwAddService[] $ewAddServices Список дополнительных услуг
 *
 * @property EwToDupalSyncResult $syncResult Результат синхронизации
 * @property int|string creator_user_id
 * @property Manifest[] manifests Массив связанных манифестов
 * @property Manifest lastManifest Последний манифест
 * @property string stateStr
 * @property string shipment_typeStr
 * @property LogEw logCreation
 * @property LogEw logLastUpdate
 * @property mixed serviceData
 * @property EwRelatedOrder[] ewRelatedOrders
 * @property EwNonDelivery[] ewNonDeliveries
 * @property RelEwEw[] $relatedEws Зависимые накладные
 * @property RelEwEw[] $relatedInitEws Накладные от которых зависит данная
 * @property string clientOrderNums
 * @property EwHistoryEvents[] $ewHistoryEvents
 * @property Invoice invoice
 * @property integer sender_cp_phonenum_id
 * @property CounterpartyContactPersPhones sender_cp_phonenum
 * @property integer sender_cp_email_id
 * @property CounterpartyContactPersEmail sender_cp_email
 * @property integer sender_cp_address_id
 * @property CounterpartyManualAdress sender_cp_address
 * @property integer receiver_cp_phonenum_id
 * @property CounterpartyContactPersPhones receiver_cp_phonenum
 * @property integer receiver_cp_email_id
 * @property CounterpartyContactPersEmail receiver_cp_email
 * @property integer receiver_cp_address_id
 * @property CounterpartyManualAdress receiver_cp_address
 * @property CounterpartyManualAdress receiver_cp_address_primary
 * @property CounterpartyManualAdress sender_cp_address_primary
 * @property integer sender_cp_contactpers_id
 * @property CounterpartyContactPers sender_cp_contactpers
 * @property integer receiver_cp_contactpers_id
 * @property CounterpartyContactPers receiver_cp_contactpers
 * @property ListCity sender_city
 * @property ListCity receiver_city
 * @property string payerContragent
 * @property mixed processingOperations
 */
class ExpressWaybill extends CommonModel {

    /**
     * Префикс номера накладной
     */
    const ID_PREFIX = 30;

    /**
     * Максимальный размер номера накладной
     */
    const ID_MAXSIZE = 20;
    // тип услуги
    const SERVICE_TYPE_WW = 1;
    const SERVICE_TYPE_WD = 2;
    const SERVICE_TYPE_DW = 3;
    const SERVICE_TYPE_DD = 4;
    const SERVICE_TYPE_DC = 5;
    const SERVICE_TYPE_WC = 6;
    // тип лица (отправителя, получателя и тд)
    const PERSON_TYPE_PRIVATE = 1; // физлицо
    const PERSON_TYPE_LEGAL = 2; // юрлицо
    // вид отправления
    const SHIPMENT_TYPE_SHIPMENT = 1;
    const SHIPMENT_TYPE_DOCUMENT = 2;
    // тип плательщика
    const PAYER_TYPE_SHIPPER = 1;
    const PAYER_TYPE_RECEIVER = 2;
    const PAYER_TYPE_THIRDPARTY = 3;
    // тип документа
    const DOC_TYPE_PASSPORT_UA = 1;
    const DOC_TYPE_DRIVER = 2;
    const DOC_TYPE_PASSPORT = 3;
    const DOC_TYPE_PENSION = 4;
    const DOC_TYPE_MILITARY = 5;
    // форма оплаты
    const PAYMENT_TYPE_CASH = 1;
    const PAYMENT_TYPE_CASHLESS = 2;
    // факт оплаты
    const PAYMENT_PAID = 1;
    const PAYMENT_NOT_PAID = 2;
    const POSTCODE_UKRAINE = '04073';
    // запись в yii2_list_entity_type
    const ENTITY_TYPE = 1;

    // Название сущьности, нужно для сохранения прикрепленных документов
    const ENTITY_NAME = 'EW';

    /**
     * @var EwPlace Переменная для хранения введеных пользователем данных о местах
     */
    private $ewPlacesInput;

    /**
     * @var InvoicePosition  Переменная для хранения введеных пользователем данных о позициях инвоисов
     */
    private $ewPositionsInput;
    private $ewPositionsInputAppend;

    /**
     * @var InvoiceStHotec  Переменная для хранения введеных пользователем данных о заявлениях НОТЕС
     */
    private $invoiceStHotecInput;
    private $invoiceStHotecInputAppend;

    /**
     * @var EwCost  Переменная для хранения введеных пользователем данных о стоимостях
     */
    private $ewCostInput;

    /**
     * @var Invoice  Переменная для хранения введеных пользователем данных об инвоисах
     */
    private $ewInvoice;

    /**
     * @var EwRelatedOrder[]
     */
    private $ewRelatedOrdersInput;

    /**
     * @var EwNonDelivery[]
     */
    private $ewNonDeliveriesInput;
    public $init_ew_id;

    /**
     * @var EwAddService[]
     */
    private $ewAddServicesInput;
    private $saveEvent;

    /**
     * @var EwHistoryStatuses[]
     */
    private $ewHistoryStatusesInput;
    private $ewHistoryStatusesInputAppend;

    /**
     * Имя таблицы в базе данных
     */
    public static function tableName() {
        return '{{%express_waybill}}';
    }

    /**
     * Получение доступных операций над накладной
     */
    public function getOperations() {
        // новая запись
        if ($this->isNewRecord)
            return [];

        // состояние удалена
        if ($this->state == self::STATE_DELETED)
            return [self::OPERATION_CANCEL => Yii::t('app', 'Restore')];

        if ($this->state == self::STATE_CLOSED) {
            return [
                self::OPERATION_CANCEL => Yii::t('app', 'Restore'),
                self::OPERATION_CHANGE_STATUS => Yii::t('app', 'Проставление статуса трекинга')
            ];
        }

        //if ($this->operation == self::OPERATION_VIEW)
        return [
            self::OPERATION_UPDATE => Yii::t('app', 'Update'),
            self::OPERATION_CLOSE => Yii::t('app', 'Close'),
            self::OPERATION_DELETE => Yii::t('app', 'Delete'),
            self::OPERATION_CHANGE_STATUS => Yii::t('app', 'Проставление статуса трекинга'),
            self::OPERATION_CHANGE_NONDELIVERY => Yii::t('app', 'Проставление причины недоставки')
        ];

        /*
          return [
          self::OPERATION_CLOSE => Yii::t('app', 'Close'),
          self::OPERATION_DELETE => Yii::t('app', 'Delete'),
          ];
         */
    }

    /**
     * Список использованных в перечне инвойсов идентификаторов справочника ЕИ
     */
    public function getUnitsIdArray() {
        $inp = $this->ewPositions;
        if(!$inp)
            return '';

        $allunitsid=ArrayHelper::map($inp, 'inv_pos_id', 'units_of_measurement');
        foreach ($allunitsid as $key=>$val) $allunitsid[$key]="'".$val."'";
        return CommonModel::array2string($allunitsid);
    }

    private function copyEW($params) {

        if ($params['operation'] == self::OPERATION_COPY || $params['operation'] == self::OPERATION_COPY_EW_REDIRECT || $params['operation'] == self::OPERATION_COPY_EW_RETURN) {

            $model = ExpressWaybill::findOne(['id' => $params['id']]);
            if ($model != null) {
                $ew_number = $this->ew_num;

                $this->attributes = $model->attributes;
                $this->id = null;
                $this->isNewRecord = true;

                if ($model->state == self::STATE_CLOSED) {
                    $this->closing_date = null;
                    $this->closing_sending_receiver = null;
                    $this->closing_receiver_post_id = null;
                    $this->closing_receiver_doc_type = null;
                    $this->closing_doc_issue_date = null;
                    $this->closing_receiver_doc_serial_num = null;
                    $this->closing_doc_num = null;
                    $this->closing_issued_shipment = null;
                }

                $invoice = $model->getEwInvoice();
                if ($invoice) {
                    $this->ewInvoice->invoice_cost = $invoice->invoice_cost;
                    $this->ewInvoice->cparty_id = $invoice->cparty_id;
                    $this->ewInvoice->cparty_type = $invoice->cparty_type;
                    $this->ewInvoice->cparty_counterparty = $invoice->cparty_counterparty;
                    $this->ewInvoice->cparty_assignee = $invoice->cparty_assignee;
                    $this->ewInvoice->cparty_phone_num = $invoice->cparty_phone_num;
                    $this->ewInvoice->cparty_email = $invoice->cparty_email;
                    $this->ewInvoice->cparty_country = $invoice->cparty_country;
                    $this->ewInvoice->cparty_city = $invoice->cparty_city;
                    $this->ewInvoice->cparty_postcode = $invoice->cparty_postcode;
                    $this->ewInvoice->cparty_address = $invoice->cparty_address;
                }

                $this->ewPositionsInput = $model->ewPositions;
                $this->ewCostInput = $model->ewCosts[0];
                $this->ewPlacesInput = $model->ewPlaces;
                $this->ewRelatedOrdersInput = $model->ewRelatedOrders;
                $this->ewNonDeliveriesInput = $model->ewNonDeliveries;
                $this->ewAddServicesInput = $model->ewAddServices;
                $i = 0;
                foreach ($this->ewPlacesInput as $place) {
                    $i++;
                    if ($i == 1)
                        $place->place_bc = $ew_number;
                    else
                        $place->place_bc = $ew_number . str_pad($i, 4, 0, STR_PAD_LEFT);
                }

                if ($params['operation'] == self::OPERATION_COPY_EW_REDIRECT) {
                    $this->ew_type = 3;
                    $this->init_ew_id = $params['id'];
                    //(«Регіон отримувача», «Населений пункт отримувача», «Адреса отримувача», «Індекс отримувача» - вищезазначені поля очищуються
                    $this->receiver_cp_address_id = null;
                    //$this->receiver_region = null;
                    //$this->receiver_city = null;
                    //$this->receiver_address = null;
                    //$this->receiver_postcode = null;
                } else if ($params['operation'] == self::OPERATION_COPY_EW_RETURN) {
                    $this->ew_type = 4;
                    $this->init_ew_id = $params['id'];

                    // «Дані відправника» та «Дані отримувача» значення відповідних атрибутів змінюються місцями

                    $tmp = $this->sender_cp_address_id;
                    $this->sender_cp_address_id = $this->receiver_cp_address_id;
                    $this->receiver_cp_address_id = $tmp;

                    $tmp = $this->sender_cp_contactpers_id;
                    $this->sender_cp_contactpers_id = $this->receiver_cp_contactpers_id;
                    $this->receiver_cp_contactpers_id = $tmp;

                    $tmp = $this->sender_counterparty_id;
                    $this->sender_counterparty_id = $this->receiver_counterparty_id;
                    $this->receiver_counterparty_id = $tmp;

                    $tmp = $this->sender_cp_phonenum_id;
                    $this->sender_cp_phonenum_id = $this->receiver_cp_phonenum_id;
                    $this->receiver_cp_phonenum_id = $tmp;

                    $tmp = $this->sender_cp_email_id;
                    $this->sender_cp_email_id = $this->receiver_cp_email_id;
                    $this->receiver_cp_email_id = $tmp;

                    //$tmp = $this->sender_address;
                    //$this->sender_address = $this->receiver_address;
                    //$this->receiver_address = $tmp;

                    //$tmp = $this->sender_assignee;
                    //$this->sender_assignee = $this->receiver_assignee;
                    //$this->receiver_assignee = $tmp;

                    //$tmp = $this->sender_city;
                    //$this->sender_city = $this->receiver_city;
                    //$this->receiver_city = $tmp;

                    //$tmp = $this->sender_counterparty;
                    //$this->sender_counterparty = $this->receiver_counterparty;
                    //$this->receiver_counterparty = $tmp;

                    //$tmp = $this->sender_country;
                    //$this->sender_country = $this->receiver_country;
                    //$this->receiver_country = $tmp;

                    //$tmp = $this->sender_edrpou;
                    //$this->sender_edrpou = $this->receiver_edrpou;
                    //$this->receiver_edrpou = $tmp;

                    //$tmp = $this->sender_email;
                    //$this->sender_email = $this->receiver_email;
                    //$this->receiver_email = $tmp;

                    //$tmp = $this->sender_id;
                    //$this->sender_id = $this->receiver_id;
                    //$this->receiver_id = $tmp;

                    //$tmp = $this->sender_phone_num;
                    //$this->sender_phone_num = $this->receiver_phone_num;
                    //$this->receiver_phone_num = $tmp;

                    //$tmp = $this->sender_postcode;
                    //$this->sender_postcode = $this->receiver_postcode;
                    //$this->receiver_postcode = $tmp;

                    //$tmp = $this->sender_region;
                    //$this->sender_region = $this->receiver_region;
                    //$this->receiver_region = $tmp;

                    //$tmp = $this->sender_type;
                    //$this->sender_type = $this->receiver_type;
                    //$this->receiver_type = $tmp;

                    // дані про оплату на вкладці «Вартість» та в блоці «Дані платника по ЕН» очищуються
                    $this->int_delivery_cost_css_uah = null;
                    $this->int_delivery_cost_css_usd = null;
                    $this->int_delivery_cost_full_uah = null;
                    $this->int_delivery_cost_full_usd = null;
                    $this->int_delivery_cost_full = null;
                    $this->int_delivery_full_currency = null;
                    $this->int_delivery_payer = null;
                    $this->int_delivery_payment_type = null;
                    $this->clearance_cost = null;
                    $this->customs_clearance_charge = null;
                    $this->clearance_payer = null;
                    $this->clearance_payment_type = null;
                    $this->total_pay_cost_uah = null;
                    $this->third_party_id = null;
                    $this->fact_pay_int_deliv = null;
                    $this->third_party_ccs_id = null;
                    $this->fact_pay_ccs = null;
                    $this->inner_shipment = null;

                    $this->payer_type = null;
                    $this->payer_payment_type = null;
                    $this->payer_third_party_id = null;
                }
            }
        }
    }

    /**
     * Формирование полей по-умолчанию, перед созданием новой накладной
     * @param $params
     * params ['id'] - родительская накладная
     * params ['operation'] - операция self::OPERATION_COPY  self::OPERATION_COPY_EW_REDIRECT self::OPERATION_COPY_EW_RETURN и т.д.
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return;

        $index_size = 6;
        $index = $this->getNextCounterValue('ew_id') % pow(10, $index_size + 1);
        $prefix = self::ID_PREFIX . substr(date("Y"), 2, 2);
        $ew_number = $prefix . str_pad($index, $index_size, '0', STR_PAD_LEFT);
        $this->ew_num = $ew_number;

        if ($this->ewInvoice == null)
            $this->ewInvoice = new Invoice();
        $this->ewInvoice->invoice_num = $this->ew_num;
        $this->ewInvoice->invoice_type = 1;

        if ($params['operation'] != null)
            $this->copyEW($params);

        $this->ew_num = $ew_number;
        $this->state = self::STATE_CREATED;
        if (!$this->ew_type)
            $this->ew_type = 1;

        $this->picked_shipment = Yii::$app->user->identity->employee_id;
        $this->shipment_format = 1;
        // Просьба по умолчанию проставлять значение по умолчанию в поле «Delivery type» значение «Standard».
        $this->delivery_type = 2; // стандартная

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        if (!$this->ewPlacesInput) {
            $defaultPlace = new EwPlace();
            $defaultPlace->place_number = 1;
            $defaultPlace->place_bc = $this->ew_num;
            $this->ewPlacesInput[] = $defaultPlace;
        }

        // При создании ЕН все заявки Hotec по умолчанию
        if ($params['operation'] == null) {
            $invoiceStHotecArray = [];
            $listStatementHotec = ListStatementHotec::find()->all();
            foreach ($listStatementHotec as $val) {
                $invoiceStHotec = new InvoiceStHotec();
                $invoiceStHotec->statement_hotec = $val->id;
                $invoiceStHotecArray[] = $invoiceStHotec;
            }
            $this->invoiceStHotecInput = $invoiceStHotecArray;
        }
    }

    /**
     * Поведения
     */
    function behaviors() {
        return [
            [
                'class' => DateFormatBehavior::className(),
                'attributes' => [
                    '_date' => 'date',
                    '_primary_date' => 'primary_date',
                    '_order_date' => 'order_date',
                    '_closing_date' => 'closing_date',
                    '_closing_doc_issue_date' => 'closing_doc_issue_date',
                    '_storage_expiration_date' => 'storage_expiration_date',
                    '_invoice_date' => 'invoice_date'
                ]
            ],
            [
                'class' => ShortDateFormatBehavior::className(),
                'attributes' => [
                    '_est_delivery_date' => 'est_delivery_date'
                ]
            ],
            [
                'class' => DocumentStorage::className(),
            ]
        ];
    }

    /**
     * Правила для полей
     */
    public function rules() {

        return
                array_merge(parent::rules(), [
            [['ew_num', 'state', 'general_desc',
            'payer_payment_type', 'cargo_est_weight_kg', 'customs_brokerage',
            'picked_shipment', 'ew_type', 'shipment_type', 'payer_type'],
                'required'],
            [['customs_declaration_currency'], 'required', 'when' => function() {
            return !$this->ewInvoice->currency ? true : false;
        }],
            [['state', 'service_type', 'shipment_type', 'declared_currency', 'customs_declaration_currency', 'payer_type', 'payer_payment_type', 'closing_issued_shipment', 'closing_receiver_doc_type', 'ew_type', 'customs_brokerage', 'delivery_type', 'shipment_format', 'closing_receiver_post_id'], 'integer'],
            [['_date', '_est_delivery_date', '_primary_date', '_order_date', /* '_closing_date', */ '_closing_doc_issue_date', '_storage_expiration_date', '_invoice_date'], 'string'],
            [['_date', '_primary_date', '_order_date', '_closing_date', '_closing_doc_issue_date', '_storage_expiration_date', '_invoice_date'], 'validateDate'],
            [['_est_delivery_date',], 'validateDateShort'],
            ['_primary_date', 'validateDate', 'skipOnEmpty' => false, 'skipOnError' => false],
            ['_closing_date', 'validateDate', 'skipOnEmpty' => false],
            [['total_dimensional_weight_kg', 'total_actual_weight_kg', 'declared_cost', 'customs_declaration_cost', 'cargo_est_weight_kg', 'dimen_cntrl_weight_kg', 'actual_cntrl_weight_kg'], 'number'],
            [['closing_sending_receiver', 'closing_add_shipment_info',  'issue_css_responsible_pers', 'doc_info_for_package_issue', 'doc_info_for_customs', 'rec_num_allowed_issue', 'init_ew_id'], 'string', 'max' => 255],
            [['general_desc', 'closing_shipment_notes'], 'string', 'max' => 500],
            [['closing_receiver_doc_serial_num', 'closing_doc_num'], 'string', 'max' => 50, 'min' => 2],
//            [['scanning_status'], 'string', 'max' => 20],
            [['order_num', 'primary_num'], 'string', 'max' => 50],
            [['ew_num'], 'string', 'max' => 30],
            ['ew_num', 'unique', 'message' => Yii::t('ew', 'This ew num has already been taken')],
            /*['payer_third_party_id', 'required',
                'whenClient' => "function (attribute, value) {return $('#expresswaybill-payer_type').val() == " . self::PAYER_TYPE_THIRDPARTY . ";}",
                'when' => function($model) {
                    return $model->payer_type == self::PAYER_TYPE_THIRDPARTY;
                }],
            */
            /*
              ['receiver_postcode', 'required', 'when' => function($model) {
              //Необхідно реалізувати необов’язковість заповнення індексу отримувача А1.1. п. 1.3.4, якщо тип послуги в ЕН А1.1. п. 1.7.1.14 = «Склад-Склад», «Двері-Склад», «Склад-ЦСС», «Двері-ЦСС»
              return
              $model->service_type != self::SERVICE_TYPE_WW &&
              $model->service_type != self::SERVICE_TYPE_DW &&
              $model->service_type != self::SERVICE_TYPE_WC &&
              $model->service_type != self::SERVICE_TYPE_DC;
              }],
             */
            ['customs_declaration_cost', 'required', 'when' => function($model) {
                    return $model->shipment_type != self::SHIPMENT_TYPE_DOCUMENT;
                }],
            /*
              [['closing_receiver_doc_serial_num','closing_doc_num'], 'required',
              'whenClient' => "function (attribute, value) {return $('#expresswaybill-closing_receiver_doc_type').val() == ".self::DOC_TYPE_PASSPORT_UA.";}",
              'when'=>function($model) { return $model->closing_receiver_doc_type == self::DOC_TYPE_PASSPORT_UA;}],
             */
            // from cost
            [['int_delivery_payer', 'int_delivery_payment_type', 'clearance_payer', 'clearance_payment_type', 'fact_pay_int_deliv', 'fact_pay_ccs', 'inner_shipment'], 'integer'],
            [['int_delivery_cost_full_usd', 'int_delivery_cost_full', 'int_delivery_full_currency', 'int_delivery_cost_full_uah', 'int_delivery_cost_css_uah', 'int_delivery_cost_css_usd', 'clearance_cost', 'customs_clearance_charge', 'total_pay_cost_uah'], 'number'],
            ['ewRelatedOrders', 'validateEwRelatedOrders'],
            ['ewNonDeliveries', 'validateEwNonDeliveries'],
            ['ewAddServices', 'validateEwAddServices'],
            ['ewHistoryStatuses', 'validateEwHistoryStatuses'],
            ['ewPositions', 'validateEwPositions'],
            /* [['in_ew_position_full_desc'], 'required',
              'whenClient' => "function (attribute, value) {return $('#in_ew_position_full_desc').val() != '';}"] */
            ['ewPositions', 'safe'],
            [['ewInvoice'], 'validateEwInvoice'],
            [['invoiceStHotec'], 'validateInvoiceStHotec'],
            ['ewPlaces', 'validateEwPlaces'],
            [['sender_counterparty_id', 'sender_cp_address_id', 'sender_cp_contactpers_id', 'sender_cp_phonenum_id', 'sender_cp_email_id'], 'integer'],
            [['receiver_counterparty_id', 'receiver_cp_address_id', 'receiver_cp_contactpers_id', 'receiver_cp_phonenum_id', 'receiver_cp_email_id'], 'integer'],
            [['payer_third_party_id', 'third_party_id', 'third_party_ccs_id'], 'integer'],
            [['sender_cp_phonenum_id','receiver_cp_phonenum_id'], 'validatePhoneNumber']
        ]);
    }

    /*
      public function scenarios() {

      $arrayAttrForValidate = parent::scenarios()['default'];
      $arrayWithoutReceiverPostCode = $arrayAttrForValidate;
      unset($arrayWithoutReceiverPostCode[array_search('receiver_postcode', $arrayWithoutReceiverPostCode)]);
      return [
      'receiver_postcode' => $arrayWithoutReceiverPostCode,
      'default' => $arrayAttrForValidate
      ];
      }
     */

    /**
     * Надписи для полей
     */
    public function attributeLabels() {
        return array_merge(parent::attributeLabels(), [
            'id' => Yii::t('ew', 'ID'),
            'ew_num' => Yii::t('ew', 'Ew Num'),
            'state' => Yii::t('ew', 'State'),
            'stateStr' => Yii::t('ew', 'State'),
            '{{%express_waybill}}.state' => Yii::t('ew', 'State'),
            'date' => Yii::t('ew', 'Date'),
            '_date' => Yii::t('ew', 'Date'),
            'est_delivery_date' => Yii::t('ew', 'Est Delivery Date'),
            '_est_delivery_date' => Yii::t('ew', 'Est Delivery Date'),
            'service_type' => Yii::t('ew', 'Service Type'),
            'primary_num' => Yii::t('ew', 'Primary Num'),
            'primary_date' => Yii::t('ew', 'Primary Date'),
            '_primary_date' => Yii::t('ew', 'Primary Date'),
            'order_num' => Yii::t('ew', 'Order Num'),
            'order_date' => Yii::t('ew', 'Order Date'),
            '_order_date' => Yii::t('ew', 'Order Date'),
            //эти старые поля не трогать, используются в форме
            'sender_country' => Yii::t('ew', 'Sender Country'),
            'sender_region' => Yii::t('ew', 'Sender Region'),
            'sender_city' => Yii::t('ew', 'Sender City'),
            'sender_postcode' => Yii::t('ew', 'Sender Postcode'),
            'sender_counterparty' => Yii::t('ew', 'Sender Counterparty'),
            'sender_assignee' => Yii::t('ew', 'Sender Assignee'),
            'sender_phone_num' => Yii::t('ew', 'Sender Phone Num'),
            'sender_cp_phonenum_id' => Yii::t('ew', 'Sender Phone Num'),
            'sender_address' => Yii::t('ew', 'Sender Address'),
            'sender_email' => Yii::t('ew', 'Sender Email'),
            'sender_type' => Yii::t('ew', 'Sender Type'),
            'sender_id' => Yii::t('ew', 'Sender ID'),
            'sender_edrpou' => Yii::t('ew', 'Sender Edrpou'),
            'receiver_country' => Yii::t('ew', 'Receiver Country'),
            'receiver_region' => Yii::t('ew', 'Receiver Region'),
            'receiver_city' => Yii::t('ew', 'Receiver City'),
            'receiver_postcode' => Yii::t('ew', 'Receiver Postcode'),
            'receiver_counterparty' => Yii::t('ew', 'Receiver Counterparty'),
            'receiver_assignee' => Yii::t('ew', 'Receiver Assignee'),
            'receiver_phone_num' => Yii::t('ew', 'Receiver Phone Num'),
            'receiver_cp_phonenum_id' => Yii::t('ew', 'Receiver Phone Num'),
            'receiver_address' => Yii::t('ew', 'Receiver Address'),
            'receiver_email' => Yii::t('ew', 'Receiver Email'),
            'receiver_type' => Yii::t('ew', 'Receiver Type'),
            'receiver_id' => Yii::t('ew', 'Receiver ID'),
            'receiver_edrpou' => Yii::t('ew', 'Receiver Edrpou'),
            //---
            'shipment_type' => Yii::t('ew', 'Shipment Type'),
            'total_dimensional_weight_kg' => Yii::t('ew', 'Total Dimensional Weight Kg'),
            'total_actual_weight_kg' => Yii::t('ew', 'Total Actual Weight Kg'),
            'general_desc' => Yii::t('ew', 'General Desc'),
            'declared_cost' => Yii::t('ew', 'Declared Cost'),
            'declared_currency' => Yii::t('ew', 'Declared Currency'),
            'customs_declaration_cost' => Yii::t('ew', 'Customs Declaration Cost'),
            'customs_declaration_currency' => Yii::t('ew', 'Customs Declaration Currency'),
            'payer_type' => Yii::t('ew', 'Payer Type'),
            'payer_third_party' => Yii::t('ew', 'Payer Third Party'),
            'payer_payment_type' => Yii::t('ew', 'Payer Payment Type'),
            'closing_date' => Yii::t('ew', 'Closing Date'),
            '_closing_date' => Yii::t('ew', 'Closing Date'),
            'closing_sending_receiver' => Yii::t('ew', 'Closing Sending Receiver'),
            'closing_issued_shipment' => Yii::t('ew', 'Issued Shipment'),
//            'closing_nondelivery_reason' => Yii::t('ew', 'Closing Nondelivery Reason'),
            'closing_add_shipment_info' => Yii::t('ew', 'Closing Add Shipment Info'),
            'closing_receiver_post_id' => Yii::t('ew', 'Closing Receiver Post'),
            'closing_receiver_doc_type' => Yii::t('ew', 'Closing Receiver Doc Type'),
            'closing_receiver_doc_serial_num' => Yii::t('ew', 'Closing Receiver Doc Serial Num'),
            'closing_doc_num' => Yii::t('ew', 'Closing Doc Num'),
            'closing_doc_issue_date' => Yii::t('ew', 'Closing Doc Issue Date'),
            '_closing_doc_issue_date' => Yii::t('ew', 'Closing Doc Issue Date'),
            'closing_shipment_notes' => Yii::t('ew', 'Closing Shipment Notes'),
            'storage_expiration_date' => Yii::t('ew', 'Storage Expiration Date'),
            '_storage_expiration_date' => Yii::t('ew', 'Storage Expiration Date'),
            'issue_css_responsible_pers' => Yii::t('ew', 'Issue Css Responsible Pers'),
            'doc_info_for_package_issue' => Yii::t('ew', 'Doc Info For Package Issue'),
            'doc_info_for_customs' => Yii::t('ew', 'Doc Info For Customs'),
            'rec_num_allowed_issue' => Yii::t('ew', 'Rec Num Allowed Issue'),
//            'scanning_status' => Yii::t('ew', 'Scanning Status'),
            'ew_places_count' => Yii::t('ew', 'Amount of pieces'),
            'cargo_est_weight_kg' => Yii::t('ew', 'Estimated weight, kg'),
            'ew_type' => Yii::t('ew', 'EW type'),
            'customs_brokerage' => Yii::t('ew', 'Customs brokerage'),
            'picked_shipment' => Yii::t('ew', 'Picked up the shipment'),
            'dimen_cntrl_weight_kg' => Yii::t('ew', 'Dimentional control weight, kg'),
            'delivery_type' => Yii::t('ew', 'Delivery type'),
            'shipment_format' => Yii::t('ew', 'Format of shipment'),
            'actual_cntrl_weight_kg' => Yii::t('ew', 'Actual control weight, kg'),
            // from cost
            'int_delivery_cost_full_usd' => Yii::t('ew', 'EW cost USD'),
            'int_delivery_cost_full' => Yii::t('ew', 'EW cost'),
            'int_delivery_full_currency' => Yii::t('ew', 'EW currency'),
            'int_delivery_cost_full_uah' => Yii::t('ew', 'EW cost UAH'),
            'int_delivery_cost_css_uah' => Yii::t('ew', 'Int Delivery Cost Css Uah'),
            'int_delivery_cost_css_usd' => Yii::t('ew', 'Int Delivery Cost Css Usd'),
            'int_delivery_payer' => Yii::t('ew', 'Int Delivery Payer'),
            'int_delivery_payment_type' => Yii::t('ew', 'Int Delivery Payment Type'),
            'clearance_cost' => Yii::t('ew', 'Clearance Cost'),
            'customs_clearance_charge' => Yii::t('ew', 'Customs Clearance Charge'),
            'clearance_payer' => Yii::t('ew', 'Clearance Payer'),
            'clearance_payment_type' => Yii::t('ew', 'Clearance Payment Type'),
            'total_pay_cost_uah' => Yii::t('ew', 'Total Pay Cost Uah'),
            'third_party' => Yii::t('ew', 'Third Party'),
            'fact_pay_int_deliv' => Yii::t('ew', 'Fact of payment of international delivery'),
            'third_party_ccs' => Yii::t('ew', 'Third Party'),
            'fact_pay_ccs' => Yii::t('ew', 'Fact of payment of CCS'),
            'delivery_cost' => Yii::t('ew', 'Delivery cost'),
            'delivery_full_currency' => Yii::t('ew', 'Currency of delivery cost'),
            'add_service_cost' => Yii::t('ew', 'Add. service cost'),
            'add_service_currency' => Yii::t('ew', 'Currency of add. service cost'),
            'inner_shipment' => Yii::t('ew', 'Внутренняя отправка'),
            // ~from cost
            //invoice
            //~Invoice
            'syn' => Yii::t('ew', 'Purpose for export'),
            'operation' => Yii::t('app', 'Operation'),
            'ewPlacesCount' => Yii::t('ew', 'Places count'),
            'sender_country_code' => Yii::t('ew', 'Sender Country Code'),
            'receiver_country_code' => Yii::t('ew', 'Receiver Country Code'),
            'condition_incoterms' => Yii::t('ew', 'Condition Incoterms'),
            'cparty_id' => Yii::t('ew', 'ID Counterparty'),
            'cparty_counterparty' => Yii::t('ew', 'Counterparty'),
            'cparty_assignee' => Yii::t('ew', 'Assignee'),
            'cparty_postcode' => Yii::t('ew', 'PostCode'),
            'cparty_country' => Yii::t('ew', 'Counterparty Country'),
            'cparty_city' => Yii::t('ew', 'Counterparty City'),
            'cparty_address' => Yii::t('ew', 'Counterparty Address'),
            'cparty_email' => Yii::t('ew', 'Counterparty Email'),
            'cparty_type' => Yii::t('ew', 'Counterparty Person Type'),
            'cparty_phone_num' => Yii::t('ew', 'Cunterparty Phone Num'),
            '_invoice_date' => Yii::t('ew', 'Invoice Date'),
            'statement_notes' => Yii::t('ew', 'Statement Notes'),
        ]);
    }

    /**
     * Метод получения моделей цен
     */
    public function getEwCosts() {
        return $this->hasMany(EwCost::className(), ['ew_id' => 'id']);
    }

    /**
     * Метод получения моделей связанных мест
     */
    public function getEwPlacesRel() {
        return $this->hasMany(EwPlace::className(), ['ew_id' => 'id']);
    }

    /**
     * Метод получения моделей мест
     */
    public function getEwPlaces() {
        if ($this->ewPlacesInput)
            return $this->ewPlacesInput;

        $this->ewPlacesInput = $this->hasMany(EwPlace::className(), ['ew_id' => 'id'])->orderBy('place_number')->all();
        return $this->ewPlacesInput;
    }


    public function validateEwPlaces() {

        if ($this->ewPlacesInput)
            foreach ($this->ewPlacesInput as $place) {
                if (!$place->validate()) {
                    $this->addErrors($place->errors);
                }
            }
        /*
          $placeInBd = EwPlace::findOne(['place_bc'=>$place->place_bc]);

          // такой ШК уже введен
          if ($placeInBd!=null && $placeInBd->ew_id != $this->id ){
          $this->addError('barcode', Yii::t('ew', 'Entered bar code '. $placeInBd->place_bc .' alredy exist in EW '. $placeInBd->ew->ew_num));
          return false;
          }

         */
    }

    public function setEwPlaces($value) {
        for ($i = 1; $i <= count($value); $i++) {

            // новая запись
            $place = new EwPlace();

            // или существующая
            if (!$this->isNewRecord && substr($value[$i]['id'], 0, 1) != '-') {
                $place = EwPlace::findOne(['place_bc' => $value[$i]['id']]);
            }


            $place->load($value, $i);
            $this->ewPlacesInput[] = $place;
        }
    }

    public function saveEwPlaces() {

        $placesInBase = EwPlace::findAll(['ew_id' => $this->id]);
        $placesSaved = [];

        // обход по новым записям
        if ($this->ewPlacesInput)
            foreach ($this->ewPlacesInput as $place) {
                $place->ew_id = $this->id;
                $place->save();
                $placesSaved[] = $place->place_bc;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        foreach ($placesInBase as $place) {
            if (!in_array($place->place_bc, $placesSaved)) {
                $place->delete();
            }
        }
    }

    /**
     * Метод получения общего кол-ва мест
     */
    public function getEwPlacesCount() {
        $result = sizeof($this->ewPlaces);
        if (!$result) // места считаются с 1
            $result = 1;

        return $result;
    }

    /**
     * Метод получения моделей позиций инвоиса
     */
    public function getEwPositions() {
        if ($this->ewPositionsInput)
            return $this->ewPositionsInput;
        if (sizeof($this->invoices) > 0)
            $this->ewPositionsInput = $this->invoices[0]->getInvoicePositions()->all();

        return $this->ewPositionsInput;
    }

    public function validateEwPositions() {

        if ($this->ewPositionsInput) {
            foreach ($this->ewPositionsInput as $pos) {
                if (!$pos->validate()) {
                    $this->addErrors($pos->errors);
                }
            }
        }
    }

    public function setEwPositions($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->ewPositionsInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        for ($i = 1; $i <= count($value); $i++) {

            if (!isset($value[$i]))
                continue;

            // новая запись
            $position = new InvoicePosition();

            // или существующая
            if ($value[$i]['inv_pos_id'] > 0)
                $position = InvoicePosition::findOne(['inv_pos_id' => $value[$i]['inv_pos_id']]);

            $position->load($value, $i);
            $this->ewPositionsInput[] = $position;
        }
    }

    public function getInvoiceStHotec() {
        if ($this->invoiceStHotecInput) {
            return $this->invoiceStHotecInput;
        }
        return $this->invoices[0]->invoiceStHotec;
    }

    public function setInvoiceStHotec($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->invoiceStHotecInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        $invoice = $this->invoice;
        for ($i = 1; $i <= count($value); $i++) {

            if (!isset($value[$i]))
                continue;

            // Если ид больше нуля значит запись есть в базе
            if ($value[$i]['id'] > 0) {
                $invoiceStNote = InvoiceStHotec::findOne(['invoice_id' => $invoice->id, 'id' => $value[$i]['id']]);
                $invoiceStNote->load($value, $i);
                $this->invoiceStHotecInput[] = $invoiceStNote;
            } else {
                $invoiceStNote = new InvoiceStHotec();
                $invoiceStNote->statement_hotec = $value[$i]['statement_hotec'];
                $this->invoiceStHotecInput[] = $invoiceStNote;
            }
        }
    }

    public function saveInvoiceStHotec() {
        $invoiceStNotesInBase = $this->invoices[0]->invoiceStHotec;
        $invoiceStNotesSaved = [];

        // обход по новым записям
        if ($this->invoiceStHotecInput) {
            foreach ($this->invoiceStHotecInput as $invoiceStNote) {
                if ($invoiceStNote->invoice_id == null) {
                    $invoiceStNote->invoice_id = $this->invoice->id;
                }
                $invoiceStNote->save();
                $invoiceStNotesSaved[] = $invoiceStNote->id;
            }
        }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$invoiceStNotesInBase) {
            $invoiceStNotesInBase = [];
        }
        if (!$this->invoiceStHotecInputAppend)
        foreach ($invoiceStNotesInBase as $invoiceStNote) {
            if (!in_array($invoiceStNote->id, $invoiceStNotesSaved)) {
                $invoiceStNote->delete();
            }
        }
    }

    /**
     * Метод получения общего кол-ва позиций инвоиса
     */
    public function getEwPositionsCount() {
        return sizeof($this->ewPositions);
    }

    /**
     * Метод полечения позиций в виде массива
     */
    public function getEwPositionsArray() {
        $name_units_short_lang = 'name_short_' . Yii::$app->language;
        $result = [];
        $i = 1;
        //$positions = $this->hasErrors() ? $this->ewPositionsInput : $this->ewPositions;
        $positions = $this->ewPositionsInput != null ? $this->ewPositionsInput : $this->ewPositions;
        if (sizeof($positions) == 0)
            return $result;
        foreach ($positions as $ewPosition) {
            $country = Country::getById($ewPosition->manufacturer_country_code);
            $unit = Units::getById($ewPosition->units_of_measurement);
            $currency = Currency::findOne(['id' => $ewPosition->pieces_currency]);
            $result[] = [
                'position_number' => $i++,
                'full_desc' => $ewPosition->full_desc,
                'customs_goods_code' => $ewPosition->customs_goods_code,
                'manufacturer_country_code' => $ewPosition->manufacturer_country_code,
                'manufacturer_country_text' => ($country == null) ? '' : $country->alpha2_code,
                'pieces_quantity' => $ewPosition->pieces_quantity,
                'pieces_amount' => $ewPosition->pieces_amount,
                'units_of_measurement' => $ewPosition->units_of_measurement,
                'units_of_measurement_text' => ($unit == null) ? '' : $unit->$name_units_short_lang,
                'cost_per_piece' => $ewPosition->cost_per_piece,
                'total_cost' => $ewPosition->total_cost,
                'pieces_weight' => $ewPosition->pieces_weight,
                'unitModel' => Units::findOne($ewPosition->units_of_measurement),
                'pieces_currency_text' => $currency ? $currency->nameShortEn : '',
                'material' => $ewPosition->material_good
            ];
        }

        return $result;
    }

    /**
     * Метод получения модели страны отправителя
     */
    public function getSenderCountry() {
        //return $this->hasOne(Country::className(), ['id' => 'sender_country']);
    }

    /**
     * Метод получения модели города отправителя
     */
    public function getSenderCity() {
        //return $this->hasOne(ListCity::className(), ['id' => 'sender_city']);
    }

    /**
     * Метод получения cargo type
     */
    public function getCargoType() {
        return $this->hasOne(ListCargoType::className(), ['id' => 'shipment_type']);
    }

    /**
     * Метод получения модели страны получателя
     */
    public function getReceiverCountry() {
        //return $this->hasOne(Country::className(), ['id' => 'receiver_country']);
    }

    /**
     * Метод получения модели валюты
     */
    public function getDeclaredCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'declared_currency']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomsDeclarationCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'customs_declaration_currency']);
    }

    /**
     *
     */
    public function getClosingIssuedShipment() {
        return $this->hasOne(Employee::className(), ['id' => 'closing_issued_shipment']);
    }

    /**
     *  Метод получения типа плательщика
     */
    public function getPayerType() {
        return $this->hasOne(PayerType::className(), ['id' => 'payer_type']);
    }

    /**
     *  Метод получения типа платежа плательщика
     */
    public function getPayerPaymentType() {
        return $this->hasOne(FormPayment::className(), ['id' => 'payer_payment_type']);
    }

    /**
     * Метод получения моделей инвоисов
     */
    public function getInvoices() {
        return $this->hasMany(Invoice::className(), ['ew_id' => 'id']);
    }

    /**
     * Метод получения доступных состояний накладной
     * @param bool $empty
     * @return array
     */
    public function getStateList($empty = false) {
        return parent::getStateList($empty) + [self::STATE_CLOSED => Yii::t('ew', 'Closed')];
    }

    /**
     * Метод получения доступных состояний накладной
     * @param bool $empty
     * @return array
     */
    public static function getEwStateList($empty = false) {
        return parent::getStateList($empty) + [self::STATE_CLOSED => Yii::t('ew', 'Closed')];
    }

    /**
     * Метод получения типов отправителя
     */
    public function getPersonTypeList() {
        return
                [
                    null => '',
                    self::PERSON_TYPE_LEGAL => Yii::t('ew', 'Legal person'),
                    self::PERSON_TYPE_PRIVATE => Yii::t('ew', 'Natural person'),
        ];
    }

    /**
     * Метод получения доступных видов отправления
     */
    public static function getShipmentTypeList() {
        return
                [
                    null => '',
                    self::SHIPMENT_TYPE_SHIPMENT => Yii::t('ew', 'Cargo'),
                    self::SHIPMENT_TYPE_DOCUMENT => Yii::t('ew', 'Document'),
        ];
    }

    /**
     * Метод получения доступных способов оплаты
     */
    public function getPaymentTypeList() {
        return
                [
                    null => '',
                    self::PAYMENT_TYPE_CASH => Yii::t('ew', 'Cash'),
                    self::PAYMENT_TYPE_CASHLESS => Yii::t('ew', 'Cashless'),
        ];
    }

    /**
     *  Метод получения доступных типов плательщика
     */
    public function getPayerTypeList() {
        return
                [
                    null => '',
                    self::PAYER_TYPE_RECEIVER => Yii::t('ew', 'Receiver'),
                    self::PAYER_TYPE_SHIPPER => Yii::t('ew', 'Shipper'),
                    self::PAYER_TYPE_THIRDPARTY => Yii::t('ew', 'Third party'),
        ];
    }

    /**
     *  Метод получения доступных типов документов
     */
    public function getDocTypeList() {
        return
                [
                    null => '',
                    self::DOC_TYPE_PASSPORT_UA => Yii::t('ew', 'Passport of the citizen of Ukraine'),
                    self::DOC_TYPE_DRIVER => Yii::t('ew', 'Driver`s license'),
                    self::DOC_TYPE_PASSPORT => Yii::t('ew', 'Passport'),
                    self::DOC_TYPE_PENSION => Yii::t('ew', 'Pension certificate'),
                    self::DOC_TYPE_MILITARY => Yii::t('ew', 'Military ID'),
        ];
    }

    /**
     *  Метод получения доступных типов услуг
     */
    public function getServiceTypeList() {
        return
                [
                    null => '',
                    self::SERVICE_TYPE_WW => Yii::t('ew', 'Warehouse-Warehouse'),
                    self::SERVICE_TYPE_WD => Yii::t('ew', 'Warehouse-Door'),
                    self::SERVICE_TYPE_DW => Yii::t('ew', 'Door-Warehouse'),
                    self::SERVICE_TYPE_DD => Yii::t('ew', 'Door-Door'),
                    self::SERVICE_TYPE_DC => Yii::t('ew', 'Door-CSS'),
                    self::SERVICE_TYPE_WC => Yii::t('ew', 'Warehouse-CSS'),
        ];
    }

    /**
     * Метод получения доступных признаков оплаты
     */
    public function getPaymentFactList() {
        return
                [
                    null => '',
                    self::PAYMENT_PAID => Yii::t('ew', 'Paid'),
                    self::PAYMENT_NOT_PAID => Yii::t('ew', 'Not paid'),
        ];
    }

    /**
     * Метод получения моделей дополнительных услуг
     */
    public function getEwAddServices() {
        if ($this->ewAddServicesInput)
            return $this->ewAddServicesInput;

        return $this->hasMany(EwAddService::className(), ['ew_id' => 'id']);
    }

    public function validateEwAddServices() {

        if ($this->ewAddServicesInput)
            foreach ($this->ewAddServicesInput as $service) {
                if (!$service->validate()) {
                    $this->addErrors($service->errors);
                }
            }
    }

    public function setEwAddServices($value) {
        for ($i = 1; $i <= count($value); $i++) {

            // новая запись
            $service = new EwAddService();
            $service->ew_id = $this->id;

            // или существующая
            if ($value[$i]['id'] > 0)
                $service = EwAddService::findOne(['id' => $value[$i]['id']]);


            $service->load($value, $i);
            $this->ewAddServicesInput[] = $service;
        }
    }

    public function saveEwAddServices() {

        $serviceInBase = EwAddService::findAll(['ew_id' => $this->id]);
        $serviceSaved = [];

        // обход по новым записям
        if ($this->ewAddServicesInput)
            foreach ($this->ewAddServicesInput as $service) {
                $service->ew_id = $this->id;
                $service->save();
                $serviceSaved[] = $service->id;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        foreach ($serviceInBase as $service) {
            if (!in_array($service->id, $serviceSaved)) {
                $service->delete();
            }
        }
    }

    /**
     * Метод получения моделей статусов трекинга
     */
    public function getEwHistoryStatuses() {

        if ($this->ewHistoryStatusesInput)
            return $this->ewHistoryStatusesInput;

        return $this->hasMany(EwHistoryStatuses::className(), ['ew_id' => 'id'])->orderBy('date');
    }

    public function validateEwHistoryStatuses()
    {
        if ($this->ewHistoryStatusesInput) {
            foreach ($this->ewHistoryStatusesInput as $status) {
                if (!$status->validate()) {
                    $this->addErrors($status->errors);
                }
            }
        }
    }

    /**
     * Присваивает статусы к ЕН
     * @param array $value
     */
    public function setEwHistoryStatuses($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->ewHistoryStatusesInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        for ($i = 1; $i <= count($value); $i++) {

            if (!isset($value[$i]))
                continue;

            // новая запись
            $status = new EwHistoryStatuses();
            $status->ew_id = $this->id;

            // или существующая
            if ($value[$i]['id'] > 0)
                $status = EwHistoryStatuses::findOne(['id' => $value[$i]['id']]);


            $status->load($value, $i);
            $this->ewHistoryStatusesInput[] = $status;
        }
    }

    /**
     * Сохраняет/обновляет историю статусов ЕН
     */
    public function saveEwHistoryStatuses()
    {
        $statusesInBase = EwHistoryStatuses::find()->where(['ew_id' => $this->id])->all();
        $statusesSaved = [];

        // обход по новым записям
        if ($this->ewHistoryStatusesInput) {
            foreach ($this->ewHistoryStatusesInput as $status) {
                $status->ew_id = $this->id;
                if (empty($status->status_country)) {
                    $status->status_country = $status->creatorUser->employee->country_id; // значение по умолчанию
                }
                $status->save();
                $statusesSaved[] = $status->id;
            }
        }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$this->ewHistoryStatusesInputAppend)
        foreach ($statusesInBase as $status) {
            if (!in_array($status->id, $statusesSaved)) {
                $status->delete();
            }
        }
    }

    public function validateEwInvoice($attribute) {
        if (!$this->{$attribute}->validate()) {
            $this->addErrors($this->{$attribute}->errors);
        }
    }

    public function getEwInvoice() {
        if (!$this->ewInvoice) {
            $invoice = $this->invoices[0];
            return $invoice ? $invoice : new Invoice();
        }
        return $this->ewInvoice;
    }

    /**
     * @return Invoice
     */
    public function getInvoice() {
        return $this->getEwInvoice();
    }

    public function setEwInvoice($value) {
        $invoice = Invoice::findOne(['ew_id' => $value['ew_id']]);
        // Если есть такой инвойс в базе
        $this->ewInvoice = $invoice ? $invoice : new Invoice();
        $this->ewInvoice->load($value, '');
        /*        if(!$invoice) {
          $this->ewInvoice->ew_id = $this->id;
          } */
    }

    public function saveEwInvoice() {
        //$invoiceInBase = Invoice::findOne(['ew_id' => $this->ewInvoice->ew_id]);
        // Если такой инвойс есть в базе и пришел пустой атрибут invoice_num, значит удаляем инвойс из базы
        /*        if($invoiceInBase && $this->ewInvoice->invoice_num == '') {
          $invoiceInBase->delete();
          return;
          } */
        $invoice = Invoice::findOne(['ew_id' => $this->id]);
        if (!$invoice) {
            $this->ewInvoice->ew_id = $this->id;
        }
        if (!$this->ewInvoice->validate()) {
            $this->addErrors($this->ewInvoice->errors);
        } else {
            if(!$this->ewInvoice->currency) {
                $this->ewInvoice->currency = $this->customs_declaration_currency;
            }
            $this->ewInvoice->ew_id = $this->id;
            $this->ewInvoice->save();
        }
    }

    /**
     * Метод перед сохранением накладной
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            // проверка доступности операций
            if ($this->operation == self::OPERATION_CLOSE){
                if ($this->state != self::STATE_CREATED) {
                    $this->addError('state', Yii::t('ew', "Save error. Can't close EW with state not 'Created'"));
                    return false;
                }
                else
                    $this->state = self::STATE_CLOSED;
            }

            //$op = Yii::$app->getRequest()->get()['operation'];
            //$curr_op = Yii::$app->getRequest()->get()['current_operation'];
            //if ($op == CommonModel::OPERATION_CHANGE_STATUS || $curr_op == CommonModel::OPERATION_CHANGE_STATUS)
            if ($this->operation == self::OPERATION_CHANGE_STATUS)
                $this->saveEvent = Event::EW_STATUS_POSTED;
            //else if ($op == CommonModel::OPERATION_CHANGE_NONDELIVERY || $curr_op == CommonModel::OPERATION_CHANGE_NONDELIVERY)
            else if ($this->operation == self::OPERATION_CHANGE_NONDELIVERY)
                $this->saveEvent = Event::EW_NONDELIVERY_POSTED;
            else
                $this->saveEvent = $insert ? Event::EW_CREATE : Event::EW_UPDATE;

            // смена состояния накладной в зависимости от выбранной операции
            if ($this->operation == self::OPERATION_CLOSE) {
                $this->saveEvent = Event::EW_CLOSE;
                //$this->state = self::STATE_CLOSED;
                if (!$this->closing_issued_shipment)
                    $this->closing_issued_shipment = Yii::$app->user->identity->employee_id;
            }
            else if ($this->operation == self::OPERATION_DELETE) {
                $this->saveEvent = Event::EW_DELETE;
                //$this->state = self::STATE_DELETED;
            } else if ($this->operation == self::OPERATION_CANCEL) {
                $this->saveEvent = $this->state == self::STATE_DELETED ? Event::EW_DELETE_CANCEL : Event::EW_UPDATE_CANCEL;
                //$this->state = self::STATE_CREATED;
                $this->closing_date = null;
                $this->closing_sending_receiver = null;
                $this->closing_receiver_post_id = null;
                //$this->closing_receiver_doc_type = null;
                $this->closing_doc_issue_date = null;
                $this->closing_receiver_doc_serial_num = null;
                $this->closing_doc_num = null;
                $this->closing_issued_shipment = null;
            }

            // при создании автоподстановка текущего пользователя
            if ($this->isNewRecord) {
                $this->creator_user_id = Yii::$app->user->identity->getId();
            }

            $this->calcCargo_est_weight_kg();

            // Необхідно реалізувати проставлення значення «0.00» в поле «Вартість митного декларування» на вкладці «Загальна інформація по ЕН» при обранні значення «Документы» в полі А1.1. п. 1.4.1. «Вид відправлення», значення повинно проставлятись автоматично у випадку відсутності значення у вищезазначеному полі.
            if ($this->shipment_type == self::SHIPMENT_TYPE_DOCUMENT && $this->customs_declaration_cost == null) {
                $this->customs_declaration_cost = 0;
            }

            // Необхідно реалізувати автоматичне заповнення полів (лише якшо дані відсутні) А1.1. п. 1.4.4.12 «Оголошена вартість» та А1.1. п. 1.4.4.13  «Валюта оголошеної вартості» даними «Вартості митного декларування» та «Валюти митного декларування» на вкладці «Дані відправлення» з можливістю подальшого редагування.
            if ($this->declared_cost == null)
                $this->declared_cost = $this->customs_declaration_cost;
            if ($this->declared_currency == null)
                $this->declared_currency = $this->customs_declaration_currency;

            // Зависимость Invoice Currency and Customs Declaration Currency
            $ew = ExpressWaybill::findOne(['id' => $this->id]);

            $ewCustomsDeclarationCurrencyInBase = $ew->customs_declaration_currency;
            $ewCustomsDeclarationCurrencyPost = $this->customs_declaration_currency;

            $ewDeclaredCurrencyInBase = $ew->declared_currency;
            $ewDeclaredCurrencyPost = $this->declared_currency;

            // Если не менялась Customs Declaration Currency, то ставим валюту инвойса
            $changeCustomsDeclarationCurrencyBool = ($ewCustomsDeclarationCurrencyInBase == $ewCustomsDeclarationCurrencyPost ) ? false : true;
            $changeDeclaredCurrencyBool = ($ewDeclaredCurrencyInBase == $ewDeclaredCurrencyPost) ? false : true;
            // Если не менялись Customs Declaration Currency and Declared Currency ставим валюту инвойса
            if (!$changeCustomsDeclarationCurrencyBool && !$changeDeclaredCurrencyBool && $this->ewInvoice->currency) {
                $this->customs_declaration_currency = $this->ewInvoice->currency;
                $this->declared_currency = $this->ewInvoice->currency;
            }

            return true;
        }

        return false;
    }

    /**
     * Метод архивирования
     * @param $operation int Тип операции. LogExpressWaybill::OPERATION_
     */
    public function archive($operation) {
        $user_id = Yii::$app->user->id;
        $sql = "call archive_express_waybill($this->id,$operation, $user_id)";
        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * Метод после сохранения
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE
            || $this->operation == self::OPERATION_CHANGE_STATUS || $this->operation == self::OPERATION_CHANGE_NONDELIVERY) {


            // сохранение введенных стоимостей
            if ($this->ewCosts != null) {
                foreach ($this->ewCosts as $oldEwCost)
                    $oldEwCost->delete();
            }
            $this->ewCostInput->ew_id = $this->id;
            $this->ewCostInput->save();

            $this->saveEwRelatedOrders();
            if ($this->operation == self::OPERATION_CHANGE_NONDELIVERY) {
                $this->saveEwNonDeliveries();
            }

            $this->saveEwInvoice();

            $this->saveInvoiceStHotec();

            $this->savePositions();

            $this->saveEwAddServices();
            if ($this->operation == self::OPERATION_CHANGE_STATUS) {
                $this->saveEwHistoryStatuses();
            }
            $this->saveEwPlaces();


            if ($insert && $this->init_ew_id) {
                $relEw = new RelEwEw();
                $relEw->creator_user_id = Yii::$app->user->id;
                $relEw->ew_id = $this->id;
                $relEw->ew_id_init = $this->init_ew_id;
                $relEw->save();
            }
        }

        Event::callEvent($this->saveEvent, $this->id, ['model' => $this]);

        // архивирование данных, всегда вконце должно быть!
        $this->archive($insert ? LogEw::OPERATION_CREATE : LogEw::OPERATION_UPDATE);

        $this->operation = self::OPERATION_NONE;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getInt_delivery_cost_full_usd() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->int_delivery_cost_full_usd;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->int_delivery_cost_full_usd;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setInt_delivery_cost_full_usd($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->int_delivery_cost_full_usd = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getInt_delivery_cost_full() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->int_delivery_cost_full;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->int_delivery_cost_full;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setInt_delivery_cost_full($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->int_delivery_cost_full = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getInt_delivery_full_currency() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->int_delivery_full_currency;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->int_delivery_full_currency;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setInt_delivery_full_currency($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->int_delivery_full_currency = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getInt_delivery_cost_full_uah() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->int_delivery_cost_full_uah;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->int_delivery_cost_full_uah;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setInt_delivery_cost_full_uah($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->int_delivery_cost_full_uah = $value;
    }

    /**
     * Метод получения поля Внутренняя отправка
     */
    public function getInner_shipment() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->inner_shipment;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->inner_shipment;

        return '';
    }

    /**
     * Метод сохранения поля Внутренняя отправка
     * @param $value
     */
    public function setInner_shipment($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->inner_shipment = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getInt_delivery_cost_css_uah() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->int_delivery_cost_css_uah;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->int_delivery_cost_css_uah;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setInt_delivery_cost_css_uah($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->int_delivery_cost_css_uah = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getInt_delivery_cost_css_usd() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->int_delivery_cost_css_usd;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->int_delivery_cost_css_usd;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setInt_delivery_cost_css_usd($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->int_delivery_cost_css_usd = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getInt_delivery_payer() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->int_delivery_payer;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->int_delivery_payer;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setInt_delivery_payer($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->int_delivery_payer = $value;
    }

    /**
     * Метод получения названия третей персоны
     */
    public function getThird_party() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->thirdParty->counterpartyName;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->thirdParty->counterpartyName;

        return '';
    }

    /**
     * Метод получения модели третей персоны
     */
    public function getThirdPartyCounterparty() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->thirdParty;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->thirdParty;

        return '';
    }

    /**
     * Метод получения id третей персоны
     */
    public function getThird_party_id() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->third_party_id;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->third_party_id;

        return '';
    }

    /**
     * Метод сохранения id третей персоны
     * @param $value
     */
    public function setThird_party_id($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->third_party_id = $value;
    }

    /**
     * Метод получения названия третей персоны
     */
    public function getThird_party_ccs() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->thirdPartyCcs->counterpartyName;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->thirdPartyCcs->counterpartyName;

        return '';
    }

    /**
     * Метод получения модели третей персоны
     */
    public function getThirdPartyCcsCounterparty() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->thirdPartyCcs;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->thirdPartyCcs;

        return '';
    }

    /**
     * Метод получения id третей персоны
     */
    public function getThird_party_ccs_id() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->third_party_ccs_id;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->third_party_ccs_id;

        return '';
    }

    /**
     * Метод сохранения поля третей персоны
     * @param $value
     */
    public function setThird_party_ccs_id($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->third_party_ccs_id = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getInt_delivery_payment_type() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->int_delivery_payment_type;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->int_delivery_payment_type;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setInt_delivery_payment_type($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->int_delivery_payment_type = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getClearance_cost() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->clearance_cost;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->clearance_cost;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setClearance_cost($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->clearance_cost = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getCustoms_clearance_charge() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->customs_clearance_charge;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->customs_clearance_charge;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setCustoms_clearance_charge($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->customs_clearance_charge = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getClearance_payer() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->clearance_payer;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->clearance_payer;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setClearance_payer($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->clearance_payer = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getClearance_payment_type() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->clearance_payment_type;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->clearance_payment_type;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setClearance_payment_type($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->clearance_payment_type = $value;
    }

    /**
     * Метод получения поля стоимости
     */
    public function getTotal_pay_cost_uah() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->total_pay_cost_uah;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->total_pay_cost_uah;

        return '';
    }

    /**
     * Метод сохранения поля стоимости
     * @param $value
     */
    public function setTotal_pay_cost_uah($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->total_pay_cost_uah = $value;
    }

    /**
     * Метод получения поля Факт оплаты по международной доставке
     */
    public function getFact_pay_int_deliv() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->fact_pay_int_deliv;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->fact_pay_int_deliv;

        return '';
    }

    /**
     * Метод сохранения поля Факт оплаты по международной доставке
     * @param $value
     */
    public function setFact_pay_int_deliv($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->fact_pay_int_deliv = $value;
    }

    /**
     * Метод получения поля Факт оплаты по ТБУ
     */
    public function getFact_pay_ccs() {

        if ($this->ewCostInput != null)
            return $this->ewCostInput->fact_pay_ccs;

        if (sizeof($this->ewCosts) > 0)
            return $this->ewCosts[0]->fact_pay_ccs;

        return '';
    }

    /**
     * Метод сохранения поля Факт оплаты по ТБУ
     * @param $value
     */
    public function setFact_pay_ccs($value) {

        if ($this->ewCostInput == null)
            $this->ewCostInput = new EwCost();

        $this->ewCostInput->fact_pay_ccs = $value;
    }

    public function getInvoice_date() {
        if ($this->ewInvoice != null)
            return $this->ewInvoice->invoice_date;
        if (sizeof($this->invoices) > 0)
            return $this->invoices[0]->invoice_date;
        return '';
    }

    public function setInvoice_date($value) {
        if ($this->ewInvoice == null)
            $this->ewInvoice = new Invoice();
        $this->ewInvoice->invoice_date = $value;
    }

    public function getStateStr() {
        if ($this->state != null)
            return $this->stateList[$this->state];

        return '';
    }

    public function getService_typeStr() {
        if ($this->service_type != null)
            return $this->serviceTypeList[$this->service_type];

        return '';
    }

    public function getShipment_typeStr() {
        if ($this->shipment_type != null)
            return $this->shipmentTypeList[$this->shipment_type];

        return '';
    }

    public function getSyncResult() {
        return $this->hasOne(EwToDupalSyncResult::className(), ['ew_id' => 'id'])
                        ->orderBy('datesync desc')
                        ->limit(1);
    }

    public function validateDate($attribute/* ,$params */) {
        if ($this->operation == self::OPERATION_CLOSE) {
            if ($attribute == '_closing_date') {
                if (!isset($this->closing_date)/* == NULL */) {
                    $this->addError($attribute, Yii::t('app', 'Field "Closing date" has to be filled'));
                }
            }
        }
        if (!DateFormatBehavior::validate($_POST['ExpressWaybill'][$attribute])) {
            $this->addError($attribute, Yii::t('app', 'Date format error'));
        }
    }

    public function validateDateShort($attribute) {
        if (!ShortDateFormatBehavior::validate($_POST['ExpressWaybill'][$attribute])) {
            $this->addError($attribute, Yii::t('app', 'Date format error'));
        }
    }

    /**
     * Метод возвращающий расчетные итоги ЭН
     * @return  array general_weight,general_volume_weight,itog_weight (общий вес, общий объемный вес, общий итоговый вес)
     */
    public function getItogiArray() {
        $general_weight = 0;
        $general_volume_weight = 0;
        $ews_place_count = 0;

        //расчитываем общие веса исходя из данных по местам
        foreach ($this->ewPlaces as $ewPlace) {
            $general_weight += $ewPlace->actual_weight;
            $general_volume_weight += $ewPlace->length * $ewPlace->width * $ewPlace->height / 5000;
            $ews_place_count++;
        }


        //если введено значение общего объемного веса то берем его
        if (isset($this->total_dimensional_weight_kg) && $this->total_dimensional_weight_kg != '') {
            $general_volume_weight = $this->total_dimensional_weight_kg;
        }

        //если введено значение общего фактического веса то берем его
        if (isset($this->total_actual_weight_kg) && $this->total_actual_weight_kg != '') {
            $general_weight = $this->total_actual_weight_kg;
        }


        $itog_weight = ($general_weight > $general_volume_weight) ? $general_weight : $general_volume_weight; //берем большее значение
        return array(
            'general_weight' => $general_weight,
            'general_volume_weight' => $general_volume_weight,
            'itog_weight' => $itog_weight,
            'ews_place_count' => $ews_place_count ? : 1
        );
    }

    /**
     * Метод получения сязанных манифестов
     * @return \yii\db\ActiveQuery
     */
    public function getManifests() {
        return $this->hasMany(Manifest::className(), ['id' => 'mn_id'])->viaTable('{{%mn_ew}}', ['ew_id' => 'id']);
    }

    /**
     * Метод получения последнего манифеста (по дате манифеста)
     * @return \yii\db\ActiveQuery
     */
    public function getLastManifest() {
        return $this->hasOne(Manifest::className(), ['id' => 'mn_id'])
                        ->viaTable('{{%mn_ew}}', ['ew_id' => 'id'])
                        ->orderBy('date desc');
    }
    
    /**
     * Метод для получения прикрепленных к ЭН документов
     * @return type
     */
    public function getAttachedDocs()
    {
        return $this->hasMany(\app\models\attached_doc\AttachedDoc::className(), ['id' => 'attdoc_id'])
                ->viaTable(\app\models\attached_doc\EwAttachedDoc::tableName(), ['ew_id' => 'id']);
    }

    public function toJson() {

        $delivery_full_currency = $this->int_delivery_full_currency ? Currency::getById($this->int_delivery_full_currency)->getNameShortEn() : '';
        $customs_declaration_currency = $this->customs_declaration_currency ? Currency::getById($this->customs_declaration_currency)->getNameShortEn() : '';
        $add_service_cost = 0;
        $add_service_currency = '';
        foreach ($this->ewAddServices as $service) {
            $add_service_cost += $service->service_cost;
            // @todo convert to EUR here!
            $add_service_currency = ($service->currency) ? Currency::getById($service->currency)->getNameShortEn() : '';
        }
        return [
            'id' => $this->id,
            'state' => $this->state,
            'ew_num' => $this->ew_num,
            'date' => $this->_date,
            'est_delivery_date' => $this->_est_delivery_date,
            'primary_num' => $this->primary_num,
            'primary_date' => $this->_primary_date,
            'order_num' => $this->clientOrderNums,
            'order_date' => $this->_order_date,
            'sender_country' => $this->sender_cp_address->countryModel->nameShort,
            'sender_region' => $this->sender_cp_address->regionName,
            'sender_city' => $this->sender_cp_address ? $this->sender_cp_address->getCityName(null,true) : '',
            'sender_address' => $this->sender_cp_address->addressName,
            'sender_counterparty' => $this->senderCounterparty->counterpartyName,
            'receiver_country' => $this->receiver_cp_address->countryModel->nameShort,
            'receiver_region' => $this->receiver_cp_address->regionName,
            'receiver_city' => $this->receiver_cp_address ? $this->receiver_cp_address->getCityName(null,true) : '',
            'receiver_address' => $this->receiver_cp_address->addressName,
            'receiver_counterparty' => $this->receiverCounterparty->counterpartyName,
            'payer_type' => $this->getPayerTypeList()[$this->payer_type],
            'shipment_type' => $this->shipment_typeStr,
            'ewPlacesCount' => $this->ewPlacesCount,
            'total_actual_weight_kg' => $this->total_actual_weight_kg,
            'total_dimensional_weight_kg' => $this->getItogiArray()['general_volume_weight'],
            'general_desc' => $this->general_desc,
            'customs_declaration_cost' => $this->customs_declaration_cost,
            'sender_country_code' => $this->sender_cp_address->countryModel->alpha2_code,
            'sender_id' => $this->senderCounterparty->counterparty_id,
            'sender_postcode' => $this->sender_cp_address->index,
            'sender_email' => $this->sender_cp_email->email,
            'sender_phone_num' => $this->sender_cp_phonenum->displayPhone,
            'receiver_country_code' => $this->receiver_cp_address->countryModel->alpha2_code,
            'receiver_id' => $this->receiverCounterparty->counterparty_id,
            'receiver_postcode' => $this->receiver_cp_address->index,
            'receiver_email' => $this->receiver_cp_email->email,
            'receiver_phone_num' => $this->receiver_cp_phonenum->displayPhone,
            'delivery_cost' => $this->int_delivery_cost_full,
            'delivery_full_currency' => $delivery_full_currency,
            'customs_declaration_currency' => $customs_declaration_currency,
            'add_service_cost' => $add_service_cost,
            'add_service_currency' => $add_service_currency,
            'closing_date' => DateTimeFormatter::npiFormat($this->closing_date),
            'clearance_cost' => $this->clearance_cost,

        ];
    }

    /**
     * Получить логи манифеста
     */
    public function getLogs() {
        return $this
                        ->hasMany(LogEw::className(), ['id' => 'log_row_id'])
                        ->viaTable('{{%arch_express_waybill}}', ['id' => 'id']);
    }

    /**
     * Получить лог создания
     */
    public function getLogCreation() {
        return $this
                        ->hasOne(LogEw::className(), ['id' => 'log_row_id'])
                        ->where(['type' => LogEw::OPERATION_CREATE])
                        ->viaTable('{{%arch_express_waybill}}', ['id' => 'id']);
    }

    /**
     * Получить последний лог редактирования
     */
    public function getLogLastUpdate() {
        return $this
                        ->hasOne(LogEw::className(), ['id' => 'log_row_id'])
                        ->where(['type' => LogEw::OPERATION_UPDATE])
                        ->orderBy('date desc')
                        ->viaTable('{{%arch_express_waybill}}', ['id' => 'id']);
    }

    /**
     * Получение служебной информации
     * @return array Массив с полями служебной информации
     */
    public function getServiceData() {

        $createArray = [];
        if ($this->logCreation)
            $createArray = $this->logCreation->toJson('create_');
        $lastUpdateArray = [];
        if ($this->logLastUpdate)
            $lastUpdateArray = $this->logLastUpdate->toJson('lastupdate_');


        return array_merge($createArray, $lastUpdateArray);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEwType() {
        return $this->hasOne(EwType::className(), ['id' => 'ew_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryType() {
        return $this->hasOne(DeliveryType::className(), ['id' => 'delivery_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServiceType() {
        return $this->hasOne(ServiceType::className(), ['id' => 'service_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShipmentFormat() {
        return $this->hasOne(ShipmentFormat::className(), ['id' => 'shipment_format']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReceiverCounterparty() {
        return $this->hasOne(\app\models\counterparty\Counterparty::className(), ['id' => 'receiver_counterparty_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSenderCounterparty() {
        return $this->hasOne(\app\models\counterparty\Counterparty::className(), ['id' => 'sender_counterparty_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayerThirdPartyCounterparty() {
        return $this->hasOne(\app\models\counterparty\Counterparty::className(), ['id' => 'payer_third_party_id']);
    }

    public function calcCargo_est_weight_kg() {

        /*$this->cargo_est_weight_kg = max(
                $this->actual_cntrl_weight_kg, $this->dimen_cntrl_weight_kg, $this->total_dimensional_weight_kg, $this->total_actual_weight_kg
        );*/
        // максимум только среду двух полей, так сказала Сыч Наталия.
        $this->cargo_est_weight_kg = max($this->total_dimensional_weight_kg, $this->total_actual_weight_kg);
    }

    public function afterFind() {

        parent::afterFind();

        if ($this->state == self::STATE_CREATED){
            $this->closing_receiver_doc_type = null;
        }

        $this->calcCargo_est_weight_kg();
    }

    /**
     * Получение обычных фильтров
     * @return array filters
     */
    public function getFilters() {
        return [
            ['id' => 'f_ew_num', 'label' => Yii::t('ew', 'Number') . ':', 'operation' => '=', 'field' => 'ew_num'],
            ['id' => 'f_manifests', 'label' => Yii::t('ew', 'MN Number') . ':', 'operation' => '=', 'field' => '{{%manifest}}.mn_num'],
            ['id' => 'f_ew_date_begin', 'type' => self::FILTER_DATETIME, 'label' => Yii::t('app', 'Date from') . ':', 'operation' => '>=', 'field' => '{{%express_waybill}}.date',],
            ['id' => 'f_ew_date_end', 'type' => self::FILTER_DATETIME, 'label' => Yii::t('app', 'Date to') . ':', 'operation' => '<=', 'field' => '{{%express_waybill}}.date'],
            ['id' => 'f_ew_state', 'type' => self::FILTER_DROPDOWN,
                'items' => $this->getStateList(true), 'operation' => '=', 'field' => '{{%express_waybill}}.state'],
            ['id' => 'f_ew_nums', 'operation' => 'in', 'field' => 'ew_num', 'hidden' => true],
        ];
    }

    /**
     * Получение расширенных фильтров
     * @return array filters
     */
    public function getAFilters() {

        $urlEntities = Url::to(['dictionaries/list-entity-type/get-list']);
        $urlCargoTypes = Url::to(['dictionaries/list-cargo-type/get-list']);
        $urlShipmentFormats = Url::to(['dictionaries/shipment-format/get-list']);
        $urlServiceTypes = Url::to(['dictionaries/list-service-type/get-list']);
        $urlPersonTypes = Url::to(['dictionaries/list-person-type/get-list']);
        $urlEwTypes = Url::to(['ew/express-waybill/get-ew-type-list']);
        $urlPayerTypes = Url::to(['dictionaries/payer-type/get-list']);
        $urlPaymentTypes = Url::to(['dictionaries/form-payment/get-list']);
        $urlDeliveryTypes = Url::to(['dictionaries/delivery-type/get-list']);
        $urlCurrencyList = Url::to(['dictionaries/currency/get-list']);
        $urlBooleansList = Url::to(['common/get-booleans']);
        $urlCounterparty = Url::to(['counterparty/counterparty/get-list']);
        $urlCounterpartyTypes = Url::to(['dictionaries/payer-type/get-list']);
        $urlCountries = Url::to(['dictionaries/country/get-list']);
        $urlCities = Url::to(['dictionaries/list-city/get-list']);
        $urlWarehouses = Url::to(['dictionaries/warehouse/get-list']);
        $urlEmployees = Url::to(['dictionaries/employee/get-list']);

        $listCountry = Country::getListFast();
        $listCity = ListCity::getList('name',true);
        $ewTable = $this->tableName();

        return [
            [
                'id' => 'title',
                'label' => Yii::t('ew', 'Entity') . ':',
            ],
            ['id' => 'af_ew_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language,
                'label' => Yii::t('app', 'Language') . ':', 'items' => Langs::$Names, 'lang_selector' => true],
            [
                'id' => 'br',
            ],
            // Сущность
            [
                'id' => 'af_ew_entity',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('ew', 'Document') . ':',
                'items' => ListEntityType::getVisibleList(true,'name_short'),
                'lang_dependency' => true,
                'url' => $urlEntities,
            ],
            // Номер сущности
            [
                'id' => 'af_ew_entity_number',
                'label' => Yii::t('ew', '№') . ':',
            ],
            // Тип накладной/заказа
            [
                'id' => 'af_ew_order_type',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('ew', 'Doc. type') . ':',
                'items' => WbOrderType::getList(true),
                'lang_dependency' => true,
                'url' => 'index.php?r=dictionaries/wb-order-type/get-list',
            ],
            // Перевозчик
            [
                'id' => 'af_ew_order_carrier',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('ew', 'Carrier') . ':',
                'items' => \app\models\dictionaries\carrier\ListCarrier::getList(true),
                'lang_dependency' => true,
                'url' => 'index.php?r=dictionaries/list-carrier/get-list',
            ],
            // Период дат сущности
            [
                'id' => 'af_ew_entity_date_begin',
                'type' => self::FILTER_DATETIME,
                'label' => Yii::t('app', 'Date from') . ':',
            ],
            [
                'id' => 'af_ew_entity_date_end',
                'type' => self::FILTER_DATETIME,
                'label' => Yii::t('app', 'Date to') . ':',
            ],
            [
                'id' => 'br',
            ],
//            [
//                'id' => 'af_ew_ccd',
//                'label' => Yii::t('ew', 'CCD name, number, date') . ':',
//            ],
            // entity state
            [
                'label' => Yii::t('app', 'State') . ':',
                'id' => 'af_entity_state',
                'type' => self::FILTER_CHECKBOXESDROPDOWN,
                'items' => EwState::getList(false),//$this->getStateList(false),
                'operation' => 'in',
                'field' => "$ewTable.state",
                'lang_dependency' => true,
                'url' => 'index.php?r=dictionaries/ew-state/get-list',
            ],
            [
                'id' => 'hr',
            ],
            [
                'id' => 'title',
                'label' => Yii::t('ew', 'EW params') . ':',
            ],
            [
                'id' => 'af_ew_shipment_type',
                'type' => self::FILTER_DROPDOWN,
                'items' => ListCargoType::getList(true),
                'operation' => '=',
                'field' => "$ewTable.shipment_type",
                'label' => Yii::t('ew', 'Shipment Type') . ':',
                'lang_dependency' => true,
                'url' => $urlCargoTypes,
            ],
            [
                'id' => 'af_ew_shipment_format',
                'type' => self::FILTER_DROPDOWN,
                'items' => ShipmentFormat::getList(true),
                'operation' => '=',
                'field' => "$ewTable.shipment_format",
                'label' => Yii::t('ew', 'Shipment Format') . ':',
                'lang_dependency' => true,
                'url' => $urlShipmentFormats,
            ],
            [
                'join' => [
                    [
                        'type' => 'leftJoin',
                        'from' => "(select max(`yii2_ew_place`.place_number) place_count, `yii2_ew_place`.ew_id from yii2_ew_place group by `yii2_ew_place`.ew_id) pc",
                        'on' => "pc.ew_id = " . ExpressWaybill::tableName() . ".id",
                    ],
                ],
                'id' => 'af_ew_places_from',
                'label' => Yii::t('ew', 'Places From') . ':',
                'field' => 'pc.place_count',
                'operation' => '>=',
            ],
            [
                'id' => 'af_ew_places_to',
                'label' => Yii::t('ew', 'Places To') . ':',
                'field' => 'pc.place_count',
                'operation' => '<=',
            ],
            [
                'id' => 'af_ew_service_type',
                'type' => self::FILTER_CHECKBOXESDROPDOWN,
                'items' => ServiceType::getList(false),
                'field' => "$ewTable.service_type",
                'label' => Yii::t('ew', 'Service Type') . ':',
                'lang_dependency' => true,
                'url' => $urlServiceTypes,
                'operation' => 'in',
            ],
            [
                'id' => 'af_ew_type',
                'type' => self::FILTER_CHECKBOXESDROPDOWN,
                'items' => EwType::getList(false),
                'field' => "$ewTable.ew_type",
                'label' => Yii::t('ew', 'EW type') . ':',
                'lang_dependency' => true,
                'url' => $urlEwTypes,
                'operation' => 'in',
            ],
            [
                'id' => 'af_ew_weight_from',
                'label' => Yii::t('ew', 'Weight from, kg') . ':',
                'field' => "$ewTable.cargo_est_weight_kg",
                'operation' => '>=',
            ],
            [
                'id' => 'af_ew_weight_to',
                'label' => Yii::t('ew', 'Weight to, kg') . ':',
                'field' => "$ewTable.cargo_est_weight_kg",
                'operation' => '<=',
            ],
            [
                'id' => 'af_ew_payer_type',
                'type' => self::FILTER_CHECKBOXESDROPDOWN,
                'items' => PayerType::getList(false),
                'operation' => 'in',
                'field' => "$ewTable.payer_type",
                'label' => Yii::t('ew', 'Payer Type') . ':',
                'lang_dependency' => true,
                'url' => $urlPayerTypes,
            ],
            [
                'id' => 'af_ew_delivery_type',
                'type' => self::FILTER_CHECKBOXESDROPDOWN,
                'items' => DeliveryType::getList(false),
                'operation' => 'in',
                'field' => "$ewTable.delivery_type",
                'label' => Yii::t('ew', 'Delivery type') . ':',
                'lang_dependency' => true,
                'url' => $urlDeliveryTypes,
            ],
            [
                'id' => 'br',
            ],
            [
                'id' => 'af_ew_cdcost_from',
                'label' => Yii::t('ew', 'CD Cost from') . ':',
            ],
            [
                'id' => 'af_ew_cdcost_to',
                'label' => Yii::t('ew', 'CD Cost To') . ':',
            ],
            [
                'id' => 'af_ew_cdcost_currency',
                'type' => self::FILTER_DROPDOWN,
                'items' => Currency::getList('nameShort', true),
                'label' => Yii::t('ew', 'Curr.') . ':',
                'lang_dependency' => true,
                'url' => $urlCurrencyList,
            ],
            [
                'id' => 'af_ew_cbs',
                'type' => self::FILTER_DROPDOWN,
                'items' => CommonModel::getBooleans(true),
                'label' => Yii::t('ew', 'CBS') . ':',
                'lang_dependency' => true,
                'url' => $urlBooleansList,
                'field' => "$ewTable.customs_brokerage",
                'operation' => '=',
            ],
            [
                'id' => 'af_ew_payment',
                'type' => self::FILTER_DROPDOWN,
                'items' => FormPayment::getList(true),
                'label' => Yii::t('ew', 'Payment type') . ':',
                'field' => "$ewTable.payer_payment_type",
                'operation' => '=',
                'lang_dependency' => true,
                'url' => $urlPaymentTypes,
            ],
            [
                'id' => 'br',
            ],
            [
                'join' => [
                    [
                        'type' => 'leftJoin',
                        'from' => EwAddService::tableName() . " as",
                        'on' => "as.ew_id = " . ExpressWaybill::tableName() . ".id",
                    ],
                ],
                'id' => 'af_ew_adds',
                'type' => self::FILTER_DROPDOWN,
                'items' => CommonModel::getBooleans(true),
                'label' => Yii::t('ew', 'Additional services') . ':',
                'lang_dependency' => true,
                'url' => $urlBooleansList,
                'field' => 'as.id IS NOT NULL',
                'operation' => '=',
            ],
            [
                'join' => [
                    [
                        'type' => 'leftJoin',
                        'from' => EwCost::tableName() . " ct",
                        'on' => "ct.ew_id = " . ExpressWaybill::tableName() . ".id",
                    ],
                ],
                'label' => Yii::t('ew', 'Inner shipment') . ':',
                'id' => 'af_ew_inner',
                'field' => 'ct.inner_shipment',
                'operation' => '=',
                'type' => self::FILTER_CHECKBOX,
            ],
            [
                'id' => 'hr',
            ],
            // counterparty: visible, not deleted, not inner
            [
                'id' => 'title',
                'label' => Yii::t('ew', 'Counterparty params') . ':',
            ],
            [
                // counterparty.counterparty_id
                'id' => 'af_ew_counterparty_code',
                'label' => Yii::t('ew', 'Counterparty code') . ':',
                'operation' => 'like',
            ],
            [
                // counterparty.counterparty_contact_pers.counterparty_contact_pers_phones.phone_number
                'id' => 'af_ew_counterparty_phone',
                'label' => Yii::t('ew', 'Counterparty phone') . ':',
                'operation' => 'like',
            ],
            [
                'id' => 'br',
            ],
            // payer_type
            [
                'id' => 'af_ew_counterparty_type',
                'type' => self::FILTER_CHECKBOXESDROPDOWN,
                'value' => '',
                'label' => Yii::t('ew', 'Counterparty type') . ':',
                'items' => PayerType::getList(false),
                'lang_dependency' => true,
                'url' => $urlCounterpartyTypes,
            ],
            // list_person_type
            [
                'id' => 'af_ew_person_type',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('ew', 'Person type') . ':',
                'items' => ListPersonType::getList(true),
                'lang_dependency' => true,
                'url' => $urlPersonTypes,
            ],
            // counterparty_manual_adress.index_{lang}
            [
                'id' => 'af_ew_counterparty_index',
                'label' => Yii::t('ew', 'Index') . ':',
            ],
            [
                'id' => 'br',
            ],
            // counterparty country
            [
                'id' => 'af_ew_counterparty_country',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'Country') . ':',
                'items' => $listCountry,
                'lang_dependency' => true,
                'url' => $urlCountries,
                'operation' => '=',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_creation_country').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'filtercounterparty_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],
            // counterparty city
            [
                'id' => 'af_ew_counterparty_city',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'City') . ':',
                'items' => $listCity,
                'lang_dependency' => true,
                'url' => $urlCities,
                'operation' => '=',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_creation_city').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname' => 'filtercounterparty_city',
                'view_tab_title' => Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname' => 'city_{0}',
            ],
            [
                'id' => 'br',
            ],
            // counterparty
            [
                'id' => 'af_ew_counterparty_counterparty',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'Counterparty') . ':',
                'items' => Counterparty::getListFast(true),
                'lang_dependency' => true,
                'url' => $urlCounterparty,
                'operation' => '=',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_creation_counterparty').': '.Yii::t('tab_title', 'counterparty_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['counterparty/counterparty/index']),
                'select_tab_uniqname' => 'filtercounterparty_counterparty',
                'view_tab_title' => Yii::t('tab_title', 'counterparty_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['counterparty/counterparty/view']),
                'view_tab_uniqname' => 'counterparty_{0}',
            ],
            [
                'id' => 'hr',
            ],

            // Параметры создания ЕН
            [
                'id' => 'title',
                'label' => Yii::t('ew', 'EW creation') . ':',
            ],
            // Период дат создания ЕН
            [
                'id' => 'af_ew_date_begin',
                'type' => self::FILTER_DATETIME,
                'label' => Yii::t('app', 'Date from') . ':',
                'field' => "$ewTable.date",
                'operation' => '>=',
            ],
            [
                'id' => 'af_ew_date_end',
                'type' => self::FILTER_DATETIME,
                'label' => Yii::t('app', 'Date to') . ':',
                'field' => "$ewTable.date",
                'operation' => '<=',
            ],
            [
                'id' => 'br',
            ],
            // EW creation
            // joins
            [
                'join' => [
                    [
                        'type' => 'leftJoin',
                        'from' => '{{%arch_express_waybill}}' . " aewс",
                        'on' => "aewс.ew_num = " . ExpressWaybill::tableName() . ".ew_num",
                    ],
                    [
                        'type' => 'leftJoin',
                        'from' => '{{%log_express_waybill}}' . " lewс",
                        'on' => "lewс.id = aewс.log_row_id and lewс.type = 1",
                    ],
                    [
                        'type' => 'leftJoin',
                        'from' => '{{%users}}' . " uewс",
                        'on' => "uewс.user_id = lewс.user_id",
                    ],
                    [
                        'type' => 'leftJoin',
                        'from' => '{{%employee}}' . " eewc",
                        'on' => "eewc.id = uewс.employee_id",
                    ],
                ],
            // logEw.user.employee.country
                'field' => 'eewc.country_id',
                'operation' => '=',
                
                'id' => 'af_ew_creation_country',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'Country') . ':',
                'items' => $listCountry,
                'lang_dependency' => true,
                'url' => $urlCountries,
                'operation' => '=',
                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_creation_country') . ': ' . Yii::t('tab_title', 'country_full_name') . ' ' . Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'filtercounterparty_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name') . ' {0} ' . Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],
            // logEw.user.employee.city
            [
                'field' => 'eewc.city_id',
                'operation' => '=',
                'id' => 'af_ew_creation_city',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'City') . ':',
                'items' => $listCity,
                'lang_dependency' => true,
                'url' => $urlCities,
                'operation' => '=',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_creation_city').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname' => 'filtercounterparty_city',
                'view_tab_title' => Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname' => 'city_{0}',
            ],
            [
                'id' => 'br',
            ],
            // logEw.user.employee.department
            [
                'field' => 'eewc.warehouse_id',
                'operation' => '=',
                'id' => 'af_ew_creation_department',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'Department') . ':',
                'items' => ListWarehouse::getListByEmplyee('name', true),
                'lang_dependency' => true,
                'url' => $urlWarehouses,
                'operation' => '=',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_creation_warehouse').': '.Yii::t('tab_title', 'warehouse_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/warehouse/index']),
                'select_tab_uniqname' => 'filtercounterparty_warehouse',
                'view_tab_title' => Yii::t('tab_title', 'warehouse_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/warehouse/view']),
                'view_tab_uniqname' => 'warehouse_{0}',
            ],
            // logEw.user.employee.surnameFull
            [
                'field' => 'eewc.id',
                'operation' => '=',
                'id' => 'af_ew_creation_user',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'User') . ':',
                // Всё равно глючит, если у пользователя привязка к удалённому складу.
                'items' => Employee::getList('surnameFull', true, 'city_id IS NOT NULL AND warehouse_id IS NOT NULL AND country_id IS NOT NULL'),
                'lang_dependency' => true,
                'url' => $urlEmployees,
                'operation' => '=',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_creation_employee').': '.Yii::t('tab_title', 'employee_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/employee/index']),
                'select_tab_uniqname' => 'filtercounterparty_employee',
                'view_tab_title' => Yii::t('tab_title', 'employee_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/employee/view']),
                'view_tab_uniqname' => 'employee_{0}',
            ],
        ];
    }

    public function getEntityType() {
        return self::ENTITY_TYPE;
    }

    public function getEwRelatedOrders() {

        if ($this->ewRelatedOrdersInput)
            return $this->ewRelatedOrdersInput;

        return $this->hasMany(EwRelatedOrder::className(), ['ew_id' => 'id']);
    }

    public function getEwNonDeliveries() {
        /*        if($this->ewNonDeliveriesInput) {
          return $this->ewNonDeliveriesInput;
          } */
        return $this->hasMany(EwNonDelivery::className(), ['ew_id' => 'id']);
    }

    public function validateEwRelatedOrders() {

        if ($this->ewRelatedOrdersInput) {
            $keys = [];
            foreach ($this->ewRelatedOrdersInput as $order) {
                $keys[] = $order->wb_order_num . '_' . $order->wb_order_type;
                if (!$order->validate()) {
                    $this->addErrors($order->errors);
                }
            }
            if (count($this->ewRelatedOrders) > count(array_unique($keys))) {
                $this->addError('ewRelatedOrders', Yii::t('error',
                    $this->getAttributeLabel('wb_order_num') . ' + ' . $this->getAttributeLabel('wb_order_type') . ' ' . 'должно быть уникальным значением'));
            }
        }
    }

    public function validateEwNonDeliveries() {
        if ($this->ewNonDeliveriesInput) {
            foreach ($this->ewNonDeliveriesInput as $nonDelivery) {
                if (!$nonDelivery->validate()) {
                    $this->addErrors($nonDelivery->errors);
                }
            }
        }
    }

    public function validateInvoiceStHotec() {
        if ($this->invoiceStHotecInput) {
            foreach ($this->invoiceStHotecInput as $invoiceStNotes) {
                if (!$invoiceStNotes->validate()) {
                    $this->addErrors($invoiceStNotes->errors);
                }
            }
        }
    }

    public function setEwRelatedOrders($value) {

        for ($i = 1; $i <= count($value); $i++) {

            // новая запись
            $order = new EwRelatedOrder();
            $order->ew_id = $this->id;

            // или существующая
            if ($value[$i]['id'] > 0)
                $order = EwRelatedOrder::findOne(['id' => $value[$i]['id']]);


            $order->load($value, $i);
            $this->ewRelatedOrdersInput[] = $order;
        }
    }

    public function setEwNonDeliveries($value) {
        for ($i = 1; $i <= count($value); $i++) {

            $nonDelivery = new EwNonDelivery();
            $nonDelivery->ew_id = $this->id;

            if ($value[$i]['id'] > 0) {
                $nonDelivery = EwNonDelivery::findOne(['id' => $value[$i]['id']]);
            }
            $nonDelivery->load($value, $i);
            $this->ewNonDeliveriesInput[] = $nonDelivery;
        }
    }

    public function saveEwRelatedOrders() {

        $ordersInBase = EwRelatedOrder::findAll(['ew_id' => $this->id]);
        $orderSaved = [];


        // обход по новым записям
        if ($this->ewRelatedOrdersInput)
            foreach ($this->ewRelatedOrdersInput as $order) {
                $order->ew_id = $this->id;
                $order->save();
                $orderSaved[] = $order->id;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        foreach ($ordersInBase as $order) {
            if (!in_array($order->id, $orderSaved)) {
                $order->delete();
            }
        }
    }

    public function saveEwNonDeliveries() {
        $nonDeliveriesInBase = $this->ewNonDeliveries;
        $nonDeliveriesSaved = [];

        // обход по новым записям
        if ($this->ewNonDeliveriesInput) {
            foreach ($this->ewNonDeliveriesInput as $nonDelivery) {
                $nonDelivery->ew_id = $this->id;
                $nonDelivery->save();
                $nonDeliveriesSaved[] = $nonDelivery->id;
            }
        }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        foreach ($nonDeliveriesInBase as $nonDelivery) {
            if (!in_array($nonDelivery->id, $nonDeliveriesSaved)) {
                $nonDelivery->delete();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedEws() {
        return $this->hasMany(RelEwEw::className(), ['ew_id_init' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedInitEws() {
        return $this->hasMany(RelEwEw::className(), ['ew_id' => 'id']);
    }

    public function getClientOrderNums() {

        $result = [];
        if ($this->ewRelatedOrders)
            foreach ($this->ewRelatedOrders as $order) {
                if ($order->wb_order_type == 1) {
                    $result[] = $order->wb_order_num;
                }
            }

        return implode(',', $result);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEwHistoryEvents() {
        return $this->hasMany(EwHistoryEvents::className(), ['ew_id' => 'id'])->orderBy('date desc');
    }

    /**
     * Флаг доступности контролов на форме
     * @return bool флаг доступности контролов на форме
     */
    public function getDisableEdit($field = null) {

        if (($this->operation == self::OPERATION_CHANGE_STATUS && $field == 'ewHistoryStatuses')
            || ($this->operation == self::OPERATION_CHANGE_NONDELIVERY && $field == 'ewNonDeliveries'))
            return false;

        if (parent::getDisableEdit())
            return true;

        if ($this->operation == self::OPERATION_CLOSE || $this->operation == self::OPERATION_CHANGE_STATUS || $this->operation == self::OPERATION_CHANGE_NONDELIVERY)
            return true;

        return !$this->isNewRecord && array_key_exists('state', $this->attributes)
            && ($this->state == self::STATE_DELETED || $this->state == self::STATE_CLOSED)
            && $this->operation != self::OPERATION_CANCEL;
    }

    public function getGridOperations() {
        return parent::getGridOperations() +
                [
                    self::OPERATION_CLOSE => Yii::t('app', 'Close'),
                    self::OPERATION_COPY => Yii::t('app', 'Copy'),
                    self::OPERATION_CHANGE_STATUS => Yii::t('app', 'Проставление статуса трекинга'),
                    self::OPERATION_CHANGE_NONDELIVERY => Yii::t('app', 'Проставление причины недоставки'),
                    self::OPERATION_COPY_EW_REDIRECT => Yii::t('ew', 'Redirection EW'),
                    self::OPERATION_COPY_EW_RETURN => Yii::t('app', 'Return EW'),
                    self::OPERATION_COPY_EW_RECEIPT => Yii::t('app', 'Receipt'),
                    self::OPERATION_COPY_EW_CUSTOMS => Yii::t('app', 'Customs brokerage services sheet'),
                    self::OPERATION_COPY_EW_ACCEPTANCE => Yii::t('app', 'Acceptance report'),
        ];
    }

    public function getGridOperationsOptions() {

        return parent::getGridOperationsOptions() +
                [
                    self::OPERATION_CLOSE => ['url' => Url::to(['close']), 'state_depend' => [self::STATE_CREATED], 'name_for_tab' => Yii::t('tab_title', 'close_command')],
                    self::OPERATION_COPY => ['url' => Url::to(['create']), 'separator_before' => true, 'tab_name_sufix' => 'copy'],
                    self::OPERATION_CHANGE_STATUS => ['url' => Url::to(['edit-status']), 'state_depend' => [self::STATE_CREATED, self::STATE_CLOSED]],
                    self::OPERATION_CHANGE_NONDELIVERY => ['url' => Url::to(['edit-nondelivery']), 'state_depend' => [self::STATE_CREATED]],
                    self::OPERATION_COPY_EW_REDIRECT => ['url' => Url::to(['create']), 'group' => Yii::t('app', 'Create on the strength of')],
                    self::OPERATION_COPY_EW_RETURN => ['url' => Url::to(['create']), 'group' => Yii::t('app', 'Create on the strength of')],
                //self::OPERATION_COPY_EW_RECEIPT =>   ['group' => Yii::t('app','Create on the strength of')],
                //self::OPERATION_COPY_EW_CUSTOMS =>   ['group' => Yii::t('app','Create on the strength of')],
                //self::OPERATION_COPY_EW_ACCEPTANCE =>   ['group' => Yii::t('app','Create on the strength of')],
        ];
    }

    private function savePositions() {

        $invoiceId = $this->ewInvoice->id;

        // сохранение введенных позиций инвоисов

        $posicionInBase = $this->ewInvoice->getInvoicePositions()->all();
        $posicionSaved = [];
        $invoiceCost = 0;
        // обход по новым записям
        if ($this->ewPositionsInput)
            foreach ($this->ewPositionsInput as $position) {
                if ($position->full_desc == '' && $position->customs_goods_code == '' && $position->manufacturer_country_code == '' && $position->pieces_quantity == '' && $position->units_of_measurement == '' && $position->cost_per_piece == '' && $position->total_cost == '')
                    continue;
                $position->inv_id = $invoiceId;
                if ($position->save()) {
                    $invoiceCost += $position->total_cost;
                }
                $posicionSaved[] = $position->inv_pos_id;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$this->ewPositionsInputAppend)
        foreach ($posicionInBase as $position) {
            if (!in_array($position->inv_pos_id, $posicionSaved)) {
                $position->delete();
            }
        }

        $invoice = Invoice::findOne(['id' => $invoiceId]);
        $invoice->invoice_cost = $invoiceCost;
        $invoice->save();
    }

    /**
     * Метод получения мест накладной в виде массива
     * @param bool $sort
     * @return array
     */
    public function getEwPlacesArray($sort = false) {
        $result = array();

        $places = $this->ewPlacesInput != null ? $this->ewPlacesInput : $this->ewPlaces;

        if ($places)
            foreach ($places as $ewPlace)
                $result[] = [
                    'place_number' => $ewPlace->place_number,
                    'place_bc' => $ewPlace->place_bc,
                    'place_shipment_desc' => $ewPlace->place_shipment_desc,
                    'length' => $ewPlace->length,
                    'width' => $ewPlace->width,
                    'height' => $ewPlace->height,
                    'dimensional_weight' => $ewPlace->dimensional_weight,
                    'actual_weight' => $ewPlace->actual_weight,
                    'place_pack' => $ewPlace->place_pack,
                    'place_pack_num' => $ewPlace->place_pack_num,
                ];

        if ($sort) {
            usort($result, [EwPlace::className(), 'sortPlaces']);
        }
        return $result;
    }

    public function getMaximum_length() {

        $result = 0;

        if ($this->ewPlaces)
            foreach ($this->ewPlaces as $place) {
                if ($place->length > $result)
                    $result = $place->length;
            }

        return $result;
    }

    public function getMaximum_width() {

        $result = 0;

        if ($this->ewPlaces)
            foreach ($this->ewPlaces as $place) {
                if ($place->width > $result)
                    $result = $place->width;
            }

        return $result;
    }

    public function getMaximum_height() {

        $result = 0;

        if ($this->ewPlaces)
            foreach ($this->ewPlaces as $place) {
                if ($place->height > $result)
                    $result = $place->height;
            }

        return $result;
    }

    public function getSender_cp_phonenum() {
        return CounterpartyContactPersPhones::findOne(['id' => $this->sender_cp_phonenum_id]);
    }

    public function getSender_cp_email() {
        return CounterpartyContactPersEmail::findOne(['id' => $this->sender_cp_email_id]);
    }

    public function getSender_cp_address() {
        return CounterpartyManualAdress::findOne(['id' => $this->sender_cp_address_id]);
    }

    public function getSender_cp_address_primary() {
        return CounterpartyManualAdress::findOne(['counterparty' => $this->sender_counterparty_id, 'primary_address' => 1]);
    }

    public function getReceiver_cp_phonenum() {
        return CounterpartyContactPersPhones::findOne(['id' => $this->receiver_cp_phonenum_id]);
    }

    public function getReceiver_cp_email() {
        return CounterpartyContactPersEmail::findOne(['id' => $this->receiver_cp_email_id]);
    }

    public function getReceiver_cp_address() {
        return CounterpartyManualAdress::findOne(['id' => $this->receiver_cp_address_id]);
    }

    public function getReceiver_cp_address_primary() {
        return CounterpartyManualAdress::findOne(['counterparty' => $this->receiver_counterparty_id, 'primary_address' => 1]);
    }

    public function getSender_cp_contactpers() {
        return CounterpartyContactPers::findOne(['id' => $this->sender_cp_contactpers_id]);
    }

    public function getReceiver_cp_contactpers() {
        return CounterpartyContactPers::findOne(['id' => $this->receiver_cp_contactpers_id]);
    }

    public function getReceiver_cp_contactpers_job_position() {
        return CounterpartyContactPers::findOne(['id' => $this->receiver_cp_contactpers_id])->job_position;
    }

    public function getPayerContragent() {

        $payer_contragent = '';

        if ($this->payer_type == self::PAYER_TYPE_SHIPPER) {
            $payer_contragent = $this->senderCounterparty->counterpartyName;
        } else if ($this->payer_type == self::PAYER_TYPE_RECEIVER) {
            $payer_contragent = $this->receiverCounterparty->counterpartyName;
        } else if ($this->payer_type == self::PAYER_TYPE_THIRDPARTY) {
            $payer_contragent = $this->payerThirdPartyCounterparty->counterpartyName;
        }

        return $payer_contragent;
    }

    public function validatePhoneNumber($attribute){
        // факс нельзя
        if (CounterpartyContactPersPhones::findOne($this->$attribute)->phone_num_type == 4){
            $field = $attribute == "sender_cp_phonenum_id" ? "sender_cp_phonenum[displayPhone]" : "receiver_cp_phonenum[displayPhone]";
            $this->addError($field, $this->getAttributeLabel($attribute). Yii::t('ew',' fax choice denied'));
        }
    }

    public function afterValidate(){
        if (!$this->sender_counterparty_id) {
            $this->addError('senderCounterparty[counterpartyName]', $this->getAttributeLabel('sender_counterparty') . Yii::t('ew', ' cannot be blank.'));
            return;
        }

        if (!$this->sender_cp_contactpers_id)
            $this->addError('sender_cp_contactpers[display_name]', $this->getAttributeLabel('sender_assignee') . Yii::t('ew', ' cannot be blank.'));

        if (!$this->sender_cp_phonenum_id)
            $this->addError('sender_cp_phonenum[displayPhone]', $this->getAttributeLabel('sender_phone_num') . Yii::t('ew', ' cannot be blank.'));

        if (!$this->sender_cp_address_id) {
            $this->addError('sender_cp_address[countryName]', $this->getAttributeLabel('sender_country') . Yii::t('ew', ' cannot be blank.'));
            $this->addError('sender_cp_address[cityName]', $this->getAttributeLabel('sender_city') . Yii::t('ew', ' cannot be blank.'));
            $this->addError('sender_cp_address[addressName]', $this->getAttributeLabel('sender_address') . Yii::t('ew', ' cannot be blank.'));
        }


        if (!$this->receiver_counterparty_id) {
            $this->addError('receiverCounterparty[counterpartyName]', $this->getAttributeLabel('receiver_counterparty') . Yii::t('ew', ' cannot be blank.'));
            return;
        }

        if (!$this->receiver_cp_contactpers_id)
            $this->addError('receiver_cp_contactpers[display_name]', $this->getAttributeLabel('receiver_assignee') . Yii::t('ew', ' cannot be blank.'));

        if (!$this->receiver_cp_phonenum_id)
            $this->addError('receiver_cp_phonenum[displayPhone]', $this->getAttributeLabel('receiver_phone_num') . Yii::t('ew', ' cannot be blank.'));

        if (!$this->receiver_cp_address_id) {
            $this->addError('receiver_cp_address[countryName]', $this->getAttributeLabel('receiver_country') . Yii::t('ew', ' cannot be blank.'));
            $this->addError('receiverr_cp_address[cityName]', $this->getAttributeLabel('receiver_city') . Yii::t('ew', ' cannot be blank.'));
            $this->addError('receiver_cp_address[addressName]', $this->getAttributeLabel('receiver_address') . Yii::t('ew', ' cannot be blank.'));
        }
        else if ($this->receiver_cp_address->adress_kind == 1 && !$this->receiver_cp_address->index)
            $this->addError('receiver_cp_address[index]', $this->getAttributeLabel('receiver_postcode') . Yii::t('ew', ' cannot be blank.'));

        if ($this->payer_type == self::PAYER_TYPE_THIRDPARTY && !$this->payer_third_party_id)
            $this->addError('payerThirdPartyCounterparty[counterpartyName]', $this->getAttributeLabel('payer_third_party') . Yii::t('ew', ' cannot be blank.'));
    }

    /**
     * Получение всей таблицы ЭН в виде массива полей
     * @param null $lang требуемый язык
     * @param string $where
     * @param null $columns
     * @return array массив накладных в виде массива их полей
     */
    public static function selectAsArray($lang = null, $where = '', $columns=null, $with_limits = true){

        if  (!$lang)
            $lang = Yii::$app->language;

        $ewTable = self::tableName();
        $ordersTable = EwRelatedOrder::tableName();
        $addressTable = CounterpartyManualAdress::tableName();
        $countryTranslateTable = Country::translateTableName();
        $countryTable = Country::tableName();
        $cpTable = Counterparty::tableName();
        $privatePersTable = CounterpartyPrivatPers::tableName();
        $legalEntityTable = CounterpartyLegalEntity::tableName();
        $ewPlaceTable = EwPlace::tableName();
        $currencyTable =Currency::tableName();
        $currencyTranslateTable =Currency::translateTableName();
        $costTable = EwCost::tableName();
        $addServiceTable = EwAddService::tableName();
        $emailTable = CounterpartyContactPersEmail::tableName();
        $phoneTable = CounterpartyContactPersPhones::tableName();
        $persTable = CounterpartyContactPers::tableName();

        $shipmentList = self::getShipmentTypeList();
        $payerType = self::getPayerTypeList();


        if (!$columns)
        $columns = [
            "$ewTable.id",
            "$ewTable.state",
            "$ewTable.ew_num",
            "$ewTable.primary_num",
            "$ewTable.general_desc",
            "$ewTable.total_actual_weight_kg",
            "$ewTable.total_dimensional_weight_kg",
            "cost.int_delivery_cost_full as delivery_cost",
            "$ewTable.customs_declaration_cost",
            "cost.clearance_cost",
            "DATE_FORMAT(date,'%d.%m.%Y %H:%i:%s') as date",
            "DATE_FORMAT(est_delivery_date,'%d.%m.%Y') as est_delivery_date",
            "DATE_FORMAT(closing_date,'%d.%m.%Y %H:%i:%s') as closing_date",
            "(select GROUP_CONCAT(wb_order_num SEPARATOR ', ') from $ordersTable where ew_id = $ewTable.id and wb_order_type = 1) as order_num ",
            "(case shipment_type when 1 then '".$shipmentList[1]."' else '".$shipmentList[2]."' end) as shipment_type",
            "(select name_short from $countryTranslateTable where lang = '$lang' and country_id = sender_cp_addres.country_id) as sender_country",
            "(select name_short from $countryTranslateTable where lang = '$lang' and country_id = receiver_cp_addres.country_id) as receiver_country",
            "(select alpha2_code from $countryTable where id = receiver_cp_addres.country_id) as receiver_country_code",
            "(select alpha2_code from $countryTable where id = sender_cp_addres.country_id) as sender_country_code",
            "sender_cp.counterparty_id as sender_id",
            "receiver_cp.counterparty_id as receiver_id",
            "sender_cp_addres.index as sender_postcode",
            "receiver_cp_addres.index as receiver_postcode",
            "sender_cp_mail.email as sender_email",
            "receiver_cp_mail.email as receiver_email",
            "sender_cp_phone.phone_number as sender_phone_num",
            "receiver_cp_phone.phone_number as receiver_phone_num",
            "ma_get_region(sender_cp_addres.id,'$lang') as sender_region",
            "ma_get_region(receiver_cp_addres.id,'$lang') as receiver_region",
            "ma_get_city(sender_cp_addres.id,'$lang') as sender_city",
            "ma_get_city(receiver_cp_addres.id,'$lang') as receiver_city",
            "ma_get_address(sender_cp_addres.id,'$lang') as sender_address",
            "ma_get_address(receiver_cp_addres.id,'$lang') as receiver_address",
            "coalesce(sender_pp.display_name_$lang,sender_le.display_name_$lang) as sender_counterparty",
            "coalesce(receiver_pp.display_name_$lang,receiver_le.display_name_$lang) as receiver_counterparty",
            "(case payer_type when 1 then '".$payerType[1]."' when 2 then '".$payerType[2]."' else '".$payerType[3]."' end) as payer_type",
            "(select coalesce ( (select count(*) from $ewPlaceTable where ew_id = $ewTable.id group by ew_id), 1)) as ewPlacesCount",
            "(select name_short from $currencyTranslateTable where currency_id = (select id from $currencyTable where id = cost.int_delivery_full_currency) and lang = 'en') as delivery_full_currency",
            "(select name_short from $currencyTranslateTable where currency_id = (select id from $currencyTable where id = $ewTable.customs_declaration_currency) and lang = 'en') as customs_declaration_currency",
            // в какой валюте сумма?
            "(select sum(service_cost) from $addServiceTable where ew_id = $ewTable.id) as add_service_cost",
            //  какая валюта? последняя?
            "(select name_short from $currencyTranslateTable where currency_id = (select id from $currencyTable where id = (select currency from $addServiceTable where ew_id = $ewTable.id order by id desc limit 1)) and lang = 'en') as add_service_currency",
            "sender_pers.full_name_$lang as sender_pers_name",
            "receiver_pers.full_name_$lang as receiver_pers_name",
        ];


        $query = (new Query)
            ->select($columns)
            ->from($ewTable)

            ->leftJoin("$phoneTable sender_cp_phone","sender_cp_phone.id = $ewTable.sender_cp_phonenum_id")
            ->leftJoin("$phoneTable receiver_cp_phone","receiver_cp_phone.id = $ewTable.receiver_cp_phonenum_id")
            ->leftJoin("$emailTable sender_cp_mail","sender_cp_mail.id = $ewTable.sender_cp_email_id")
            ->leftJoin("$emailTable receiver_cp_mail","receiver_cp_mail.id = $ewTable.receiver_cp_email_id")
            ->leftJoin("$addressTable sender_cp_addres","sender_cp_addres.id = $ewTable.sender_cp_address_id")
            ->leftJoin("$addressTable receiver_cp_addres","receiver_cp_addres.id = $ewTable.receiver_cp_address_id")
            ->leftJoin("$cpTable sender_cp","sender_cp.id = $ewTable.sender_counterparty_id")
            ->leftJoin("$cpTable receiver_cp","receiver_cp.id = $ewTable.receiver_counterparty_id")
            ->leftJoin("$privatePersTable sender_pp", "sender_pp.counterparty = sender_cp.id")
            ->leftJoin("$legalEntityTable sender_le", "sender_le.counterparty = sender_cp.id")
            ->leftJoin("$privatePersTable receiver_pp", "receiver_pp.counterparty = receiver_cp.id")
            ->leftJoin("$legalEntityTable receiver_le", "receiver_le.counterparty = receiver_cp.id")
            ->leftJoin("$costTable cost", "cost.ew_id = $ewTable.id")
            ->leftJoin("$persTable sender_pers", "sender_pers.id = $ewTable.sender_cp_contactpers_id")
            ->leftJoin("$persTable receiver_pers", "receiver_pers.id = $ewTable.receiver_cp_contactpers_id")
            ->where($where)
            ->orderBy("$ewTable.date desc");

        if ($with_limits)
            return self::getDataWithLimits($query);
        else
            return $query->all();
    }

    public function getRelatedEntities() {

        $result = [];

        $manifests = $this->manifests;
        foreach ($manifests as $mn) {
            $sd = $mn->serviceData;
            $result[] = [
                'id' => $mn->id,
                'state' => $mn->state,
                'document' => Yii::t('tab_title', 'MN_short_name'),
                'doc_type_num' => 1,
                'doc_num' => $mn->mn_num,
                'doc_type' => $mn->getTypeList()[$mn->mn_type],
                'date' => $mn->_date,
                'params' => $sd['create_country'] . ', ' . $sd['create_city'] . ', ' . $sd['create_departament'] . ', ' . $sd['create_surname']
            ];
        }

        //$rels = RelEwEw::find()->where('ew_id_init = :id OR ew_id = :id', [':id' => $this->id])->all();
        $rels = RelEwEw::find()->where('ew_id_init = :id OR ew_id = :id', [':id' => $this->id])->all();
        foreach ($rels as $rel) {
            $ew = ($rel->ew_id_init == $this->id) ? $rel->ew : $rel->ewInit;
            $sd = $ew->serviceData;
            $result[] = [
                'id' => $ew->id,
                'state' => $ew->state,
                'document' => Yii::t('tab_title', 'EW_short_name'),
                'doc_type_num' => 2,
                'doc_num' => $ew->ew_num,
                'doc_type' => $ew->ewType->name,
                'date' => $ew->_date,
                'params' => $sd['create_country'] . ', ' . $sd['create_city'] . ', ' . $sd['create_departament'] . ', ' . $sd['create_surname']
            ];
        }

        return json_encode($result);
    }

    public static function checkUniqueEwNum($ewNum) {
        return ExpressWaybill::findOne(['ew_num' => $ewNum]) ? Yii::t('error', "Attention! $ewNum already exists") : '';
    }

    public function getProcessingOperations(){

        return [
            self::OPERATION_NONE => '',
            self::OPERATION_CHANGE_STATUS => Yii::t('app', 'Проставление статуса трекинга'),
            self::OPERATION_CLOSE => Yii::t('app', 'Close'),
            self::OPERATION_DELETE => Yii::t('app', 'Delete'),
            self::OPERATION_CANCEL => Yii::t('app', 'Restore'),
        ];
    }

}

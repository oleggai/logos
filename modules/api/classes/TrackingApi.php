<?php

/**
 * Файл класса отримання статусу трекінгу TrackingAPI
 * Использование: 
 * 
 * json
 * {
 *  "appKey": "internal-app-key",
 *  "apiKey": "e5a303bc-ec5c-40b5-b504-1bcbfdd29f87",
 *  "modelName": "Tracking",
 *  "calledMethod": "traceEWExtInf",
 *  "methodProperties": [
 *      {
 *          "Number": "3015000000",
 *          "Language": "RU"
 *      },
 *      {
 *          "Number": "3015000979"
 *      },
 *      {
 *          "Number": "3015100980",
 *          "Language": "EN"
 *      }
 *  ]
 * }
 * 
 * xml
 *  <?xml version="1.0" encoding="UTF-8"?>
 *  <root>
 *      <appKey>internal-app-key</appKey>
 *      <apiKey>e5a303bc-ec5c-40b5-b504-1bcbfdd29f87</apiKey>
 *      <modelName>Tracking</modelName>
 *      <calledMethod>traceEW</calledMethod>
 *      <methodProperties>
 *          <Number>3015100000</Number>
 *          <Language>RU</Language>
 *      </methodProperties>
 *      <methodProperties>
 *          <Number>3015100979</Number>
 *      </methodProperties>
 *      <methodProperties>
 *          <Number>3015000091</Number>
 *          <Language>EN</Language>
 *      </methodProperties>
 *  </root>
 * 
 * @author Дмитрий Чеусов
 * @category API/classes
 */

namespace app\modules\api\classes;

use app\modules\api\classes\base\BaseApiResponse;
use app\models\ew\ExpressWaybill;
use app\models\ew\EwHistoryStatuses;
use app\models\ew\ListStatusesEw;
use app\models\dictionaries\currency\Currency;
use app\models\dictionaries\service\ServiceType;
use app\models\ew\WbOrderType;
use \app\models\dictionaries\carrier\ListCarrier;

/**
 * Клас для отримання статусу трекінгу
 * Используется API контроллерами
 */
class TrackingApi {

    /**
     * объект ЕН
     * @var ExpressWaybill
     */
    private $ew;

    /**
     * собираем сюда данные для $response->data
     * @var array
     */
    private $data;

    /**
     *
     * @var boolean Фильтры языков
     */
    private $lang_en;
    private $lang_ru;
    private $lang_uk;

    /**
     *
     * @var array Фильтры языков (массив значений) ['ru','en']
     */
    private $languages;

    /**
     * объект Base Api Response
     * @var type BaseApiResponse
     */
    private $response;
    private $count = 0;

    /* /////////////////////////////////////////////////////////////////////////
     * Служебные методы
     */

    /**
     * 
     * @return IDApi this object
     */
    private function init() {
        $this->response = new BaseApiResponse();
        return $this;
    }

    /**
     * Извлечение номеров из запроса и обёртка метода getRelated
     * @param array $params параметры вызова АПИ
     * @return array EW Ids
     */
    private function getNumbers($params) {
        $related = $numbers = [];
        if (is_array($params)) {
            foreach ($params as $param) {
                if (!($param->Number || $param->Ref)) {
                    $this->response->errors[] = ["error_code" => '420', 'error_msg' => \Yii::t('api', 'Number or Ref must be set!')];
                } else {
                    $related = $this->getRelated($param->Number);
                    if (empty($related)) {

                        $ewstatus = ListStatusesEw::find()->where(['code' => '0'])->one();

                        // $error430 = ["error_code" => '430', 'error_msg' => \Yii::t('api', "Nothing found for number") . ": {$param->Number}"];
                        //$this->response->errors[] = $error430;
                        $this->response->data[] = [
                            'TrackStatusesHistory' => [[
                            'TrackStatusRef' => $ewstatus->id,
                            'TrackStatusCode' => $ewstatus->code,
                            'TrackStatusDate' => date('d.m.Y H:i:s'),
                            'TrackStatusEN' => $ewstatus->NameFullEn,
                            'TrackStatusRU' => $ewstatus->NameFullRu,
                            'TrackStatusUA' => $ewstatus->NameFullUk,
                                //'errors' => [$error430],
                                ]],
                            'Number' => $param->Number,
                            'success' => true,
                        ];
                    } else
                        $this->count++;

                    $numbers[] = [
                        'ids' => $related,
                        'language' => $param->Language,
                    ];
                }
            }
        }
        return $numbers;
    }

    /**
     * Проверка данных и установка статуса/ошибок/инфо
     * @return BaseApiResponse
     */
    private function validateResponse() {
        if (empty($this->response->data)) {
            $this->response->errors[] = ["error_code" => '431', 'error_msg' => \Yii::t('api', 'Nothing found!')];
            return $this->response;
        }
        /* if ($this->count == 0) {
          $this->response->errors = [["error_code" => '431', 'error_msg' => \Yii::t('api', 'Nothing found!')]];
          return $this->response;
          } */
        $this->response->success = true;
        $count = count($this->response->data);
        $this->response->info[] = ["info_code" => '220', 'info_msg' => \Yii::t('api', 'Processed items: ') . $count];
        return $this->response;
    }

    /**
     * Получение значения объекта или пустой строки
     * @param type $value значение
     * @return string строчное значение или ''
     */
    private function value($value = '') {
        return (empty($value)) ? '' : "$value";
    }

    /**
     * Получение значения даты или пустой строки
     * @param type $value значение
     * @return string строчное значение или ''
     */
    private function date($value = '') {
        return (empty($value)) ? '' : "" . date('d.m.Y H:i:s', strtotime($value));
    }

    /**
     * Получение значения короткой даты или пустой строки
     * @param type $value значение
     * @return string строчное значение или ''
     */
    private function dateshort($value = '') {
        return (empty($value)) ? '' : "" . date('d.m.Y', strtotime($value));
    }

    /**
     * Получение name_{lang} строчного названия из объекта
     * @param Model $model объект
     * @param string $lang язык
     * @return string строчное названия или ''
     */
    private function name($model, $lang = '') {
        return (empty($model) || !method_exists($model, 'getName')) ? '' : "{$model->getName($lang)}";
    }

    /**
     * Установка языков
     * @param string $lang
     */
    private function setLanguages($lang = NULL) {
        $this->lang_en = $this->lang_ru = $this->lang_uk = false;

        if (empty($lang) || $lang == 'EN') {
            $this->lang_en = true;
            $this->languages['en'] = ['up' => 'EN', 'up_api' => 'EN', 'up_f' => 'En'];
        }
        if (empty($lang) || $lang == 'RU') {
            $this->lang_ru = true;
            $this->languages['ru'] = ['up' => 'RU', 'up_api' => 'RU', 'up_f' => 'Ru'];
        }
        if (empty($lang) || $lang == 'UK') {
            $this->lang_uk = true;
            $this->languages['uk'] = ['up' => 'UK', 'up_api' => 'UA', 'up_f' => 'Uk'];
        }
    }

    /* /////////////////////////////////////////////////////////////////////////
     * Методы АПИ
     */

    /**
     * 1.1.1.1.1. Отримання поточного статусу трекінгу інформації ЕН/ІД/ЗЗВ
     * @param array $params входящие параметры
     * @return BaseApiResponse стандартный ответ
     */
    public function trackEW($params) {
        $numbers = $this->init()->getNumbers($params);
        foreach ($numbers as $number) {
            $this->setLanguages($number['language']);
            foreach ($number['ids'] as $subnamber) {
                $this->response->data[] = $this->getEW($subnamber)
                        ->getInfo()
                        ->getStatus()
                        ->getData();
            }
        }
        $this->validateResponse();
        return $this->response;
    }

    /**
     * 1.1.1.1.2. Отримання історії статусів трекінгу ЕН/ІД/ЗВВ
     * @param array $params входящие параметры
     * @return BaseApiResponse стандартный ответ
     */
    public function traceEW($params) {
        $numbers = $this->init()->getNumbers($params);
        foreach ($numbers as $number) {
            $this->setLanguages($number['language']);
            foreach ($number['ids'] as $subnamber) {
                $this->response->data[] = $this->getEW($subnamber)
                        ->getInfo()
                        ->getHistory()
                        ->getData();
            }
        }
        $this->validateResponse();
        return $this->response;
    }

    /**
     * 1.1.1.1.3. Отримання поточного статусу трекінгу та скороченої інформації ЕН/ІД/ЗЗВ
     * @param array $params входящие параметры
     * @return BaseApiResponse стандартный ответ
     */
    public function trackEWShortInf($params) {
        $numbers = $this->init()->getNumbers($params);
        foreach ($numbers as $number) {
            $this->setLanguages($number['language']);
            foreach ($number['ids'] as $subnamber) {
                $this->response->data[] = $this->getEW($subnamber)
                        ->getInfo()
                        ->getStatus()
                        ->getShortInfo()
                        ->getData();
            }
        }
        $this->validateResponse();
        return $this->response;
    }

    /**
     * 1.1.1.1.4. Отримання історії статусів трекінгу та скороченої інформації ЕН/ІД/ЗВВ
     * @param array $params входящие параметры
     * @return BaseApiResponse стандартный ответ
     */
    public function traceEWShortInf($params) {
        $numbers = $this->init()->getNumbers($params);
        foreach ($numbers as $number) {
            $this->setLanguages($number['language']);
            foreach ($number['ids'] as $subnumber)
                $this->response->data[] = $this->getEW($subnumber)
                        ->getInfo()
                        ->getHistory()
                        ->getShortInfo()
                        ->getData();
        }
        $this->validateResponse();
        return $this->response;
    }

    /**
     * 1.1.1.1.5. Отримання поточного статусу трекінгу та розширеної інформації ЕН/ІД/ЗЗВ
     * @param array $params входящие параметры
     * @return BaseApiResponse стандартный ответ
     */
    public function trackEWExtInf($params) {
        $numbers = $this->init()->getNumbers($params);
        foreach ($numbers as $number) {
            $this->setLanguages($number['language']);
            foreach ($number['ids'] as $subnamber)
                $this->response->data[] = $this->getEW($subnamber)
                        ->getInfo()
                        ->getStatus()
                        ->getShortInfo()
                        ->getExtInfo()
                        ->getData();
        }
        $this->validateResponse();
        return $this->response;
    }

    /**
     * 1.1.1.1.6. Отримання історії статусів трекінгу та розширеної інформації ЕН/ІД/ЗВВ
     * @param array $params входящие параметры
     * @return BaseApiResponse стандартный ответ
     */
    public function traceEWExtInf($params) {

        $numbers = $this->init()->getNumbers($params);
        foreach ($numbers as $number) {
            $this->setLanguages($number['language']);
            foreach ($number['ids'] as $subnamber)
                $this->response->data[] = $this->getEW($subnamber)
                        ->getInfo()
                        ->getHistory()
                        ->getShortInfo()
                        ->getExtInfo()
                        ->getData();
        }
        $this->validateResponse();
        return $this->response;
    }

    /* /////////////////////////////////////////////////////////////////////////
     * Методы получения данных
     */

    /**
     * Получение списка связанных ЕН
     * @param string $number номер ЕН
     * @return array Ids
     */
    private function getRelated($number) {
        $related_orders = $related = $main = [];
        $waybills = ExpressWaybill::find()
                ->join('LEFT JOIN', '{{%ew_related_order}}', '{{%ew_related_order}}.ew_id = {{%express_waybill}}.id')
                ->where(['ew_num' => $number])
                ->andWhere('state != ' . ExpressWaybill::STATE_DELETED)
                ->orWhere(['{{%ew_related_order}}.wb_order_num' => $number])
                ->all();
        if (!empty($waybills)) {
            foreach ($waybills as $waybill) {
                $main[] = $waybill->id;
                $related_orders += $waybill->ewRelatedOrders;
            }
        }
        return(array_unique(array_merge($main, $related)));
    }

    /**
     * получение ЕН по id
     * @param string $id
     * @return IDApi this object
     */
    private function getEW($id) {
        $this->data = [];
        $this->ew = ExpressWaybill::find()
                ->where(['id' => $id])
                ->andWhere('state != ' . ExpressWaybill::STATE_DELETED)
                ->orderBy('id desc')
                ->one();
        return $this;
    }

    /**
     * Базовая информация ЕН
     * @return IDApi this object
     */
    private function getInfo() {

        $related_orders_a = [];

        if (!empty($this->ew)) {

            $related_orders = $this->ew->ewRelatedOrders;

            foreach ($related_orders as $i => $related_order) {
                $related_orders_a[$i]['EWRelatedWaybillOrderNumber'] = $this->value($related_order->wb_order_num);
                $related_orders_a[$i]['EWRelatedWaybillOrderDate'] = $this->date($related_order->wb_order_date);
                $related_orders_a[$i]['EWRelatedWaybillOrderTypeRef'] = $this->value($related_order->wb_order_type);
                $related_orders_a[$i]['EWRelatedWaybillOrderCarrierRef'] = $this->value($related_order->carrier_id);

                $order_type = WbOrderType::findOne(['id', $related_order->wb_order_type]);
                $carrier = ListCarrier::findOne(['id', $related_order->carrier_id]);

                if ($this->lang_en) {
                    $related_orders_a[$i]['EWRelatedWaybillOrderTypeEN'] = $this->name($order_type, 'en');
                    $related_orders_a[$i]['EWRelatedWaybillOrderCarrierEN'] = $this->name($carrier, 'en');
                }

                if ($this->lang_ru) {
                    $related_orders_a[$i]['EWRelatedWaybillOrderTypeRU'] = $this->name($order_type, 'ru');
                    $related_orders_a[$i]['EWRelatedWaybillOrderCarrierRU'] = $this->name($carrier, 'ru');
                }

                if ($this->lang_uk) {
                    $related_orders_a[$i]['EWRelatedWaybillOrderTypeUA'] = $this->name($order_type, 'uk');
                    $related_orders_a[$i]['EWRelatedWaybillOrderCarrierUA'] = $this->name($carrier, 'uk');
                }
            }
            $this->data += [
                'success' => true,
                'errors' => [],
                "Ref" => $this->ew->id,
                "Number" => $this->ew->ew_num,
                "EWRelatedWaybillOrderList" => $related_orders_a,
            ];
        }
        return $this;
    }

    /**
     * Статус ЕН
     * @return IDApi this object
     */
    private function getStatus() {
        $track_status = EwHistoryStatuses::find()
                ->joinWith('statusEw')
                ->where(['ew_id' => $this->ew->id, 'inner' => 0])
                ->orderBy('id desc')
                ->one();
        $this->data += $this->getStatusInfo($track_status);
        return $this;
    }

    /**
     * Get EW status history
     * @return IDApi this object
     */
    private function getHistory() {

        $track_statuses = EwHistoryStatuses::find()
                ->joinWith('statusEw')
                ->where(['ew_id' => $this->ew->id, 'inner' => 0])
                ->orderBy('date ASC')->all();
        if (count($track_statuses)) {
            $this->data["TrackStatusesHistory"] = [];
            foreach ($track_statuses as $track_status) {
                $this->data["TrackStatusesHistory"][] = $this->getStatusInfo($track_status);
            }
        }
        return $this;
    }

    /**
     * Статус ЕН
     * @return IDApi this object
     */
    private function getStatusInfo($track_status = 0) {

        if (!$track_status->statusEw->id) {
            $ew_status = ListStatusesEw::findOne($track_status->statusEw->id);
            $res = [
                "TrackStatusRef" => '',
                "TrackStatusCode" => '',
                "TrackStatusDate" => '',
                "TrackStatusCountryRef" => '',
            ];
            if ($this->lang_en) {
                $res += [
                    "TrackStatusEN" => '',
                    "TrackStatusCountryEN" => ''
                ];
            }
            if ($this->lang_ru) {
                $res += [
                    "TrackStatusRU" => '',
                    "TrackStatusCountryRU" => ''
                ];
            }
            if ($this->lang_uk) {
                $res += [
                    "TrackStatusUA" => '',
                    "TrackStatusCountryUA" => '',
                ];
            }
        } else {
            $ew_status = ListStatusesEw::findOne($track_status->statusEw->id);
            $res = [
                "TrackStatusRef" => $this->value($track_status->statusEw->id),
                "TrackStatusCode" => $this->value($track_status->statusEw->code),
                "TrackStatusDate" => $this->date($track_status->date),
                "TrackStatusCountryRef" => $this->value($track_status->status_country),
            ];
            if ($this->lang_en) {
                $res += [
                    "TrackStatusEN" => $this->value($ew_status->getNameFullEn()),
                    "TrackStatusCountryEN" => $this->value($track_status->countryModel->nameOfficialEn),
                ];
            }
            if ($this->lang_ru) {
                $res += [
                    "TrackStatusRU" => $this->value($ew_status->getNameFullRu()),
                    "TrackStatusCountryRU" => $this->value($track_status->countryModel->nameOfficialRu),
                ];
            }
            if ($this->lang_uk) {
                $res += [
                    "TrackStatusUA" => $this->value($ew_status->getNameFullUk()),
                    "TrackStatusCountryUA" => $this->value($track_status->countryModel->nameOfficialUk),
                ];
            }
        }
        return $res;
    }

    /**
     * Краткая информация ЕН
     * @return IDApi this object
     */
    private function getShortInfo() {
        $ew = $this->ew;
        $cost = $ew->ewCosts[0];
        $delivery_currency = Currency::findOne(['id' => $cost->int_delivery_full_currency]);
        $service_type = ServiceType::findOne(['id' => $ew->service_type]);
        $this->data += [
            "EWDeliveryTypeRef" => $this->value($ew->deliveryType->id),
            "EWShipperCountryRef" => $this->value($ew->sender_cp_address->country_id),
            "EWShipperSettlementRef" => $this->value($ew->sender_cp_address->city_id),
            "EWReceiverCountryRef" => $this->value($ew->receiver_cp_address->country_id),
            "EWReceiverSettlementRef" => $this->value($ew->receiver_cp_address->city_id),
            "EWDeliveryPayerRef" => $this->value($ew->payerType->id),
            "EWDeliveryCurrencyRef" => $this->value($cost->int_delivery_full_currency),
            "EWCargoTypeRef" => $this->value($ew->cargoType->id),
            "EWCBSPayerRef" => $this->value($cost->clearancePayer->id),
            "EWServiceTypeRef" => $this->value($ew->service_type),
            "EWDate" => $this->date($ew->date),
            "EWEstimetedDeliveryDate" => $this->dateshort($ew->est_delivery_date),
            "EWActualWeightkg" => $this->value($ew->total_actual_weight_kg),
            "EWVolumeWeightkg" => $this->value($ew->total_dimensional_weight_kg),
            "EWDeliverySum" => $this->value($cost->int_delivery_cost_full),
            "EWCBSSumUAH" => $this->value($cost->clearance_cost),
        ];

        foreach ($this->languages as $lang => $lang_add) {
            $this->data += [
                "EWCargoType" . $lang_add['up_api'] => $this->name($ew->cargoType, $lang),
                "EWDeliveryType" . $lang_add['up_api'] => $this->value($ew->deliveryType->{"name_" . $lang}),
                "EWShipperCountry" . $lang_add['up_api'] => $this->value($ew->sender_cp_address->countryModel->{"nameShort" . $lang_add['up_f']}),
                "EWReceiverCountry" . $lang_add['up_api'] => $this->value($ew->receiver_cp_address->countryModel->{"nameShort" . $lang_add['up_f']}),
                "EWShipperSettlement" . $lang_add['up_api'] => isset($ew->sender_cp_address) ? $this->value($ew->sender_cp_address->getcityName($lang, true)) : '',
                "EWReceiverSettlement" . $lang_add['up_api'] => isset($ew->receiver_cp_address) ? $this->value($ew->receiver_cp_address->getcityName($lang, true)) : '',
                "EWDeliveryPayer" . $lang_add['up_api'] => $this->name($ew->payerType, $lang),
                "EWCBSPayer" . $lang_add['up_api'] => $this->name($cost->clearancePayer, $lang),
                "EWDeliveryCurrency" . $lang_add['up_api'] => $this->value($delivery_currency->{"nameShort" . $lang_add['up_f']}),
                "EWServiceType" . $lang_add['up_api'] => $this->value($service_type->{"name_" . $lang}),
            ];
        }

        return $this;
    }

    /**
     * Полная информация ЕН
     * @return IDApi this object
     */
    private function getExtInfo() {
        $ew = $this->ew;
        $related = \app\models\ew\RelEwEw::find()
                ->where(['ew_id_init' => $ew->id])
                ->orderBy(['id' => 'asc'])
                ->all();
        foreach ($related as $rel) {
            if ($rel->ew->ew_type == 3) {
                $redirection = $rel->ew->ew_num;
                $redirection_id = $rel->ew->id;
            }
            if ($rel->ew->ew_type == 4) {
                $return = $rel->ew->ew_num;
                $return_id = $rel->ew->id;
            }
        }
        $cost = $ew->ewCosts[0];
        $this->data += [
            "EWReceiverWarehouseRef" => $this->value(),
            "EWShipperWarehouseRef" => $this->value(),
            "EWDeliveryFormPaymentRef" => $this->value($ew->payerPaymentType->id),
            "EWDeliveryFactPaymentRef" => $this->value($cost->factPayIntDeliv->id),
            "EWCBSFormPaymentRef" => $this->value($cost->clearancePaymentType->id),
            "EWCBSFactPaymentRef" => $this->value($cost->factPayCcs->id),
            "EWDeliveryFormatRef" => $this->value($ew->shipmentFormat->id),
            "EWRelatedRedirectionEWRef" => $this->value($redirection_id),
            "EWRelatedReturnEWRef" => $this->value($return_id),
            "EWTypeRef" => $this->value($ew->ewType->id),
            "EWCheckActualWeightkg" => $this->value($ew->actual_cntrl_weight_kg),
            "EWCheckVolumeWeightkg" => $this->value($ew->dimen_cntrl_weight_kg),
            "EWCalculatedWeightkg" => $this->value($ew->cargo_est_weight_kg),
            "EWOrderedCBS" => $ew->customs_brokerage == '1' ? 1 : 0,
            "EWRelatedRedirectionEWNumber" => $this->value($redirection),
            "EWRelatedReturnEWNumber" => $this->value($return),
            "EWShipperCounterpartyCode" => $this->value($ew->senderCounterparty->counterparty_id),
            "EWReceiverCounterpartyCode" => $this->value($ew->receiverCounterparty->counterparty_id),
        ];
        foreach ($this->languages as $lang => $lang_add) {
            $this->data += [
                "EWType" . $lang_add['up_api'] => $this->name($ew->ewType, $lang),
                "EWDeliveryType" . $lang_add['up_api'] => $this->name($ew->serviceType, $lang),
                "EWDeliveryFormat" . $lang_add['up_api'] => $this->name($ew->shipmentFormat, $lang),
                "EWShipperWarehouse" . $lang_add['up_api'] => $this->value(),
                "EWReceiverWarehouse" . $lang_add['up_api'] => $this->value(),
                "EWDeliveryFormPayment" . $lang_add['up_api'] => $this->name($ew->payerPaymentType, $lang),
                "EWCBSFactPayment" . $lang_add['up_api'] => $this->name($cost->factPayCcs, $lang),
                "EWCBSFormPayment" . $lang_add['up_api'] => $this->name($cost->clearancePaymentType, $lang),
                "EWDeliveryFactPayment" . $lang_add['up_api'] => $this->name($cost->factPayIntDeliv, $lang),
            ];
        }
        return $this;
    }

    /**
     * Получение массива данных
     * @return IDApi this object
     */
    private function getData() {
//        ksort($this->data);
        return $this->data;
    }

}

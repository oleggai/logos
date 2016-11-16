<?php

/**
 * Файл класса EwCostApi
 * Использование {"appKey":"internal-app-key","apiKey":"e5a303bc-ec5c-40b5-b504-1bcbfdd29f87","modelName":"ewCost","calledMethod":"calculate","methodProperties":[{"tariff_zone_shiper_counterparty_id":"10000438","tariff_zone_consig_counterparty_id":"10000438","cargo_type_id":"1","calc_weight":"10","cust_declare_cost":"10","cust_declare_currency":"3","cost_premium_cust_declare":"10","date_action":"2015-08-17","service_type":"1","shipment_format":"1","currency":"1"}]}
 * @author Дмитрий Чеусов
 * @category API/classes
 */

namespace app\modules\api\classes;

use app\modules\api\classes\base\BaseApiResponse;
use app\models\dictionaries\tariff\ListTariff;
use app\models\dictionaries\exchangerate\ExchangeRate;
use app\models\dictionaries\currency\Currency;
use app\models\counterparty\Counterparty;

/**
 * EwCostApi класс
 * Содержит методы расчёта стоимости ЕН
 * Используется API контроллерами
 */
class EwCostApi {

    const DEFAULT_CURRENCY = 2;
    const UAH = 3;

    /**
     * Вычисление стоимости стоимость доставки по ЭН 
     * стоимость доставки по ЭН = стоимость из тарифа (по которому нашлось соответствие) 
     * x расчетный вес по ЭН + процент надбавки x стоимость таможенного декларирования
     * @param array $params параметры запроса
     * @return BaseApiResponse
     */
    public function calculate($a_params) {
        $response = new BaseApiResponse;
        $params = $a_params[0];
        // Формируем $criteria
        $map = [
            'sender_cp_address_id',
            'receiver_cp_address_id',
            'cargo_type_id',
            'calc_weight',
            'cust_declare_cost',
            'cust_declare_currency',
            'date_action',
            'service_type',
            'shipment_format',
            'delivery_type'
        ];
        $condition = $andCondition = [];
        foreach ($map as $key) {
            if (empty($params->$key)) {
                $response->errors[] = ["error_code" => '420', 'error_msg' => $key . \Yii::t('api', ' required') . '!'];
            } else {
                $cp = new Counterparty;
                if ($key == 'cust_declare_cost' || $key == 'cust_declare_currency') {
                    continue;
                } elseif ($key == 'calc_weight') {
                    $andCondition[] = " calc_weight_from <= '{$params->$key}' AND calc_weight_to >= '{$params->$key}' ";
                } elseif ($key == 'cust_declare_cost') {
                    $andCondition[] = " cust_declare_cost_from <= '{$params->$key}' AND cust_declare_cost_to >= '{$params->$key}' ";
                } elseif ($key == 'date_action') {
                    $params->$key = date('Y-m-d', strtotime($params->$key));
                    $andCondition[] = " date_action_from <= '{$params->$key}' AND date_action_to >= '{$params->$key}' ";
                } elseif ($key == 'sender_cp_address_id') {
                    $shiper_tariff_zone_id = $cp->
                            getTariffZoneId(\app\models\counterparty\CounterpartyManualAdress::find()
                            ->where(['id' => $params->$key])
                            ->one());
                    if (empty($shiper_tariff_zone_id))
                        $response->errors[] = [
                            "error_code" => '441', 'error_msg' => \Yii::t('api', 'Shipper tariff zone not found')
                        ];
                } elseif ($key == 'receiver_cp_address_id') {
                    $consig_tariff_zone_id = $cp->
                            getTariffZoneId(\app\models\counterparty\CounterpartyManualAdress::find()
                            ->where(['id' => $params->$key])
                            ->one());
                    if (empty($consig_tariff_zone_id))
                        $response->errors[] = [
                            "error_code" => '442', 'error_msg' => \Yii::t('api', 'Receiver tariff zone not found')
                        ];
                } else {
                    $condition[$key] = $params->$key;
                }
            }
        };
        if (!empty($response->errors)) {
            return $response;
        }
        // Находим тариф, берём последний
        $condition['visible'] = $condition['state'] = 1;
        $sql = ListTariff::find()
                ->joinWith('listTariffServiceTypes')
                ->joinWith('listTariffDeliveryTypes')
                ->joinWith('listTariffShipmentFormats')
                ->andWhere($condition)
                ->andWhere(['tariff_zone_shiper' => $shiper_tariff_zone_id])
                ->andWhere(['tariff_zone_consig' => $consig_tariff_zone_id])
                ->andWhere(implode(' AND ', $andCondition))
                ->orderBy('id DESC');
        $rawSql = $sql->createCommand()->rawSql;
//        die($rawSql);
        $results = $sql->all();

        $result = [];
        // приведение customs cost по курсу валют и поиск подходящего тарифа
        foreach ($results as $a_result) {

            $tariff_ex_rate = ExchangeRate::getExRealRate($a_result->cust_declare_currency, $params->cust_declare_currency);
            $tariff_cust_cost_from = ($a_result->cust_declare_cost_from * $tariff_ex_rate);
            $tariff_cust_cost_to = ($a_result->cust_declare_cost_to * $tariff_ex_rate);

            if ($tariff_cust_cost_from <= $params->cust_declare_cost &&
                    $tariff_cust_cost_to >= $params->cust_declare_cost) {
                $result = $a_result;
                break;
            }
        }

        if (empty($result)) {
            $response->errors[] = ["error_code" => '421', 'error_msg' => \Yii::t('api', 'Tariff not found')];
            return $response;
        }

        // Считаем стоимость
        // стоимость доставки по ЭН = стоимость из тарифа (по которому нашлось соответствие) 
        // x расчетный вес по ЭН + процент надбавки x стоимость таможенного декларирования
        $tariff_cost_original = $result->cost;
        $tariff_cost_currency = $result->cost_currency;
        // если пришло без валюты - ебрём валюту тарифа
//        die($params->currency);
        if (empty($params->currency))
            $params->currency = $result->cost_currency;
        // пересчитываем в евро
        $tariff_cost_exchange_rate = ExchangeRate::getExRealRate($tariff_cost_currency, $params->currency);
        $tariff_cost = ($tariff_cost_original * $tariff_cost_exchange_rate);
        if (!$tariff_cost) {
            die(var_dump($result));
            $response->errors[] = [
                "error_code" => '431', 'error_msg' => \Yii::t('api', 'Tariff rate not found')
            ];
            $response->info[] = ["info_code" => '220', 'info_msg' => \Yii::t('api', 'Tariff found') . ': ' . $result->getName() . '.'];
            return $response;
        }

        $cost_premium_cust_declare = ($result->cost_premium_cust_declare / 100); // percents like 1.25 is 0.0125
        $cust_declare_cost = $params->cust_declare_cost;
        $cust_declare_currency = $params->cust_declare_currency;
        $calc_weight = $params->calc_weight;
        // пересчитываем в евро
        $cust_declare_cost = ($cust_declare_cost * ExchangeRate::getExRealRate($cust_declare_currency, $params->currency));
        if (!$cust_declare_cost) {
            $response->errors[] = [
                "error_code" => '432', 'error_msg' => \Yii::t('api', 'CBS rate not found')
            ];
            $response->info[] = [
                "info_code" => '220', 'info_msg' => \Yii::t('api', 'Tariff found') . ': ' . $result->getName() . '.'
            ];
            return $response;
        }

        $cost = $tariff_cost * $calc_weight + $cost_premium_cust_declare * $cust_declare_cost;
        $rate_uah = ExchangeRate::getExRealRate($params->currency, EwCostApi::UAH);
        $data = [
            'cost' => round($cost, 2),
            'currency_id' => $params->currency,
            'cost_uah' => round($cost * $rate_uah, 2),
            'rate_uah' => $rate_uah,
        ];

        // Выдаём ответ
        $response->data = $data;
        $response->success = 'true';
        $response->info[] = ["info_code" => '220', 'info_msg' => \Yii::t('api', 'Tariff found') . ': ' . $result->getName() . '.'];
        $response->info[] = ["info_code" => '221', 'info_msg' => \Yii::t('api', 'Cost calculated') . ': ' . "$tariff_cost * $calc_weight + $cost_premium_cust_declare * $cust_declare_cost"];
        $response->info[] = ["info_code" => '222', 'info_msg' => \Yii::t('api', 'UAH rate') . ': ' . "$rate_uah"];
        return $response;
    }

}

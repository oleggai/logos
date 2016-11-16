<?php
/**
 * Created by PhpStorm.
 * User: Hopr
 * Date: 02.10.2015
 * Time: 10:02
 */


namespace app\controllers\reports;

use app\controllers\CommonController;
use app\models\common\CommonModel;
use app\models\common\Langs;
use app\models\dictionaries\warehouse\ListCargoType;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use app\models\ew\ExpressWaybill;
use app\models\manifest\Manifest;
use app\models\ew\EwHistoryStatuses;
use app\models\ew\ListStatusesEw;
use app\models\ew\EwCost;
use app\models\ew\EwAddService;
use app\models\ew\EwRelatedOrder;
use app\models\ew\WbOrderType;

use app\models\counterparty\Counterparty;
use app\models\counterparty\CounterpartyPrivatPers;
use app\models\counterparty\CounterpartyLegalEntity;
use app\models\counterparty\CounterpartyManualAdress;

use app\models\dictionaries\currency\Currency;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\carrier\ListCarrier;

class ReportLogisticController extends CommonController {

    /**
     * ОТЧЕТ
     * Логистический отчет
     */

    /**
     * @return mixed текст контента страницы
     */
    const STATUS_COLOR_13 = '#E6E6E6';
    const STATUS_COLOR_31 = '#CEF6E3';
    const STATUS_COLOR_5 = '#F6CEF5';

    public function actionReportLogistic() {
        return $this->render('reportlogisticgrid', ['filters' => $this->reportLogistic_filters(), 'gridMode' => 0]);
    }

    public function actionReportLogisticSelect() {

        $params = Yii::$app->getRequest()->get();
        if ($params['operation'] == CommonModel::OPERATION_CREATE_MN)
            return $this->render('reportlogisticgrid', ['filters' => $this->reportLogistic_filters(), 'gridMode' => 1]);
        if ($params['operation'] == CommonModel::OPERATION_COPY_EW_CUSTOMS)
            return $this->render('reportlogisticgrid', ['filters' => $this->reportLogistic_filters(), 'gridMode' => 2]);
        if ($params['operation'] == CommonModel::OPERATION_COPY_EW_ACCEPTANCE)
            return $this->render('reportlogisticgrid', ['filters' => $this->reportLogistic_filters(), 'gridMode' => 3]);
    }


    /**
     * @return array правила фильтра для таблицы отчета
     */
    public static function reportLogistic_filters() {

        $urlStatuses = Url::to(['dictionaries/list-statuses-ew/get-list']);
        $urlCountries = Url::to(['dictionaries/country/get-list']);
        $urlCargoType = Url::to(['dictionaries/list-cargo-type/get-list']);
        $urlWbOrderType = Url::to(['dictionaries/wb-order-type/get-list']);
        $urlListCarrier = Url::to(['dictionaries/list-carrier/get-list']);

        $ewTable = ExpressWaybill::tableName();
        $manifestTable = Manifest::tableName();
        $ewHistoryStatusesTable =  EwHistoryStatuses::tableName();
        $ewStatusesTable = ListStatusesEw::tableName();
        $cargoTypeTable = ListCargoType::tableName();
        $ewRelOrdersTable = EwRelatedOrder::tableName();

        return [
            [
                'id' => 'f_ew_lang',
                'type' => CommonModel::FILTER_DROPDOWN,
                'value' => Yii::$app->language,
                'label' => Yii::t('app', 'Language') . ':',
                'items' => Langs::$Names,
                'lang_selector' => true,
            ],
            [
                'id' => 'f_ew_state',
                'type' => CommonModel::FILTER_CHECKBOXESDROPDOWN,
                'items' => ExpressWaybill::getEwStateList(false),
                'label' => Yii::t('logisticreport', 'Состояние ЭН') . ':',
                'operation' => 'in',
                'field' => $ewTable.'.state',
            ],
            [
                'id' => 'f_ew_num',
                'label' => Yii::t('logisticreport', 'Номер ЭН') . ':',
                'operation' => '=',
                'field' => $ewTable.'.ew_num',
            ],
            [
                'id' => 'f_status_code',
                'label' => Yii::t('logisticreport', 'Код статуса') . ':',
                'operation' => '=',
                'field' => $ewStatusesTable.'.code',
            ],
            [
                'id' => 'f_status_name',
                'type' => CommonModel::FILTER_CHECKBOXESDROPDOWN,
                'value' => '',
                'label' => Yii::t('logisticreport', 'Название статуса') . ':',
                'items' => ListStatusesEw::getList('title_short', false),
                'operation' => 'in',
                'field' => $ewStatusesTable.'.id',
                'lang_dependency' => true,
                'url' => $urlStatuses,
            ],
            [
                'id' => 'f__status_date_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('logisticreport', 'Дата статуса от ') . ':',
                'operation' => '>=',
                'field' => $ewHistoryStatusesTable.'.date',
            ],
            [
                'id' => 'f_status_date_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('logisticreport', 'Дата статуса по') . ':',
                'operation' => '<=',
                'field' => $ewHistoryStatusesTable.'.date',
            ],
            [
                'id' => 'f_status_country',
                'type' => CommonModel::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('logisticreport', 'Страна статуса') . ':',
                'items' => Country::getListFast(),
                'lang_dependency' => true,
                'url' => $urlCountries,
                'operation' => '=',
                'field' => $ewHistoryStatusesTable.'.status_country',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_status_country').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'filter_logisticreport_status_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],
            [
                'id' => 'f_status_day_cnt',
                'label' => Yii::t('logisticreport', 'Количество дней, в течении которых статус не менялся') . ':',
                'operation' => '>=',
                'field' =>"(select DATEDIFF(NOW(), coalesce(min(date),cast('0001-01-01' as DATE))) from $ewHistoryStatusesTable hs_table_1
                    where hs_table_1.ew_id=$ewTable.id and hs_table_1.date>=(select coalesce(max(date),cast('0001-01-01' as DATE))
                        from $ewHistoryStatusesTable hs_table_2
                            where hs_table_2.ew_id=$ewTable.id and hs_table_2.status_ew_id<>$ewHistoryStatusesTable.status_ew_id))",
            ],
            [
                'id' => 'f_sender_country',
                'type' => CommonModel::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('logisticreport', 'Страна отправителя') . ':',
                'items' => Country::getListFast(),
                'lang_dependency' => true,
                'url' => $urlCountries,
                'operation' => '=',
                'field' => 'sender_cp_addres.country_id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_sender_country').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'filter_logisticreport_sender_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],
            [
                'id' => 'f_receiver_country',
                'type' => CommonModel::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('logisticreport', 'Страна получателя') . ':',
                'items' => Country::getListFast(),
                'lang_dependency' => true,
                'url' => $urlCountries,
                'operation' => '=',
                'field' => 'receiver_cp_addres.country_id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'ew_receiver_country').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'filter_logisticreport_receiver_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],
            [
                'id' => 'f_shipment_name',
                'type' => CommonModel::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('logisticreport', 'Вид отправления') . ':',
                'items' => ListCargoType::getList(true),
                'operation' => '=',
                'field' => $cargoTypeTable.'.id',
                'lang_dependency' => true,
                'url' => $urlCargoType,
            ],
            [
                'id' => 'f_mn_num',
                'label' => Yii::t('logisticreport', 'Номер МН') . ':',
                'operation' => '=',
                'field' => $manifestTable.'.mn_num',
            ],
            [
                'id' => 'f_utd_num',
                'label' => Yii::t('logisticreport', 'Номер ЕТД') . ':',
                'operation' => '=',
                'field' => $manifestTable.'.utd_num',
            ],
            [
                'id' => 'f_wb_order_num',
                'label' => Yii::t('logisticreport', 'Номер связанной накладной') . ':',
                'operation' => '=',
                'field' => $ewRelOrdersTable.'.wb_order_num',
            ],
            [
                'id' => 'f_wb_order_type',
                'type' => CommonModel::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('logisticreport', 'Тип связанной накладной') . ':',
                'items' => WbOrderType::getList(true),
                'operation' => '=',
                'field' => $ewRelOrdersTable.'.wb_order_type',
                'lang_dependency' => true,
                'url' => $urlWbOrderType,
            ],
            [
                'id' => 'f_carrier',
                'type' => CommonModel::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('logisticreport', 'Перевозчик по связанной накладной') . ':',
                'items' => ListCarrier::getList(true),
                'operation' => '=',
                'field' => $ewRelOrdersTable.'.carrier_id',
                'lang_dependency' => true,
                'url' => $urlListCarrier,
            ],

        ];
    }

    public static function GetEwIds($filter) {

        $ewTable = ExpressWaybill::tableName();
        $mnewTable = '{{%mn_ew}}';
        $manifestTable = Manifest::tableName();
        $ewHistoryStatusesTable =  EwHistoryStatuses::tableName();
        $ewStatusesTable = ListStatusesEw::tableName();
        $addressTable = CounterpartyManualAdress::tableName();
        $cargoTypeTable = ListCargoType::tableName();
        $ewRelOrdersTable = EwRelatedOrder::tableName();

        $models = ExpressWaybill::find()
            ->select([$ewTable.'.id'])
            ->leftJoin("$mnewTable", "$mnewTable.ew_id = $ewTable.id")
            ->leftJoin("$manifestTable", "$manifestTable.id = $mnewTable.mn_id")
            ->leftJoin($ewHistoryStatusesTable,"$ewHistoryStatusesTable.ew_id = $ewTable.id and $ewHistoryStatusesTable.id = (select max(id) from $ewHistoryStatusesTable where ew_id=$ewTable.id and date=(select max(date) from $ewHistoryStatusesTable where ew_id=$ewTable.id))")
            ->leftJoin("$ewStatusesTable", "$ewStatusesTable.id = $ewHistoryStatusesTable.status_ew_id")
            ->leftJoin("$addressTable sender_cp_addres","sender_cp_addres.id = $ewTable.sender_cp_address_id")
            ->leftJoin("$addressTable receiver_cp_addres","receiver_cp_addres.id = $ewTable.receiver_cp_address_id")
            ->leftJoin("$cargoTypeTable", "$cargoTypeTable.id = $ewTable.shipment_type")
            ->leftJoin("$ewRelOrdersTable", "$ewRelOrdersTable.ew_id = $ewTable.id")
            ->where($filter)->asArray(true)->all();

        return ArrayHelper::map($models, 'id', 'id') + [-1];
    }

    /**
     * Формирование данных для отчета
     * @return array
     * @internal param array $filters
     */
    public function actionReportLogisticGetData() {

        $lang = Yii::$app->language;

        $ewTable = ExpressWaybill::tableName();
        $mnewTable = '{{%mn_ew}}';
        $manifestTable = Manifest::tableName();
        $ewHistoryStatusesTable =  EwHistoryStatuses::tableName();
        $ewStatusesTable = ListStatusesEw::tableName();
        $ewStatusesTranslateTable = '{{%list_statuses_ew_translate}}';
        $listStatusEwTable = ListStatusesEw::tableName();
        $countryTable = Country::tableName();
        $countryTranslateTable = Country::translateTableName();
        $cpTable = Counterparty::tableName();
        $privatePersTable = CounterpartyPrivatPers::tableName();
        $legalEntityTable = CounterpartyLegalEntity::tableName();
        $addressTable = CounterpartyManualAdress::tableName();
        $cargoTypeTable = ListCargoType::tableName();
        $currencyTable = Currency::tableName();
        $currencyTranslateTable =Currency::translateTableName();
        $ewRelOrdersTable = EwRelatedOrder::tableName();
        $orderTypeTable = WbOrderType::tableName();
        $carrierTable = '{{%list_carrier}}';
        $payerTypeTable = '{{%payer_type}}';
        $costTable = EwCost::tableName();
        $addServiceTable = EwAddService::tableName();

        $delState = CommonModel::STATE_DELETED;

        $where = '';
        //выбираем иды подходящих моделей
        $filter = $this->getFiltersWhere(null, ReportLogisticController::reportLogistic_filters());
        if ($filter) {
            $ids =  ReportLogisticController::GetEwIds($filter);
            $where = "$ewTable.id in (" . implode(", ", $ids) . ")";
        }

        $columns = [
            "$ewTable.id",
            "$ewTable.state",
            "$ewTable.ew_num",
            "(select GROUP_CONCAT(mn_num SEPARATOR '; ') from $mnewTable left join $manifestTable on $mnewTable.mn_id=$manifestTable.id
                where ew_id = $ewTable.id and $manifestTable.state<>$delState and mn_num is not null and mn_num<>'') as mn_num",
            "(select GROUP_CONCAT(utd_num SEPARATOR '; ') from $mnewTable left join $manifestTable on $mnewTable.mn_id=$manifestTable.id
                where ew_id = $ewTable.id and $manifestTable.state<>$delState and utd_num is not null and utd_num<>'') as utd_num",
            "$ewStatusesTranslateTable.title_short as status_name",
            "DATE_FORMAT($ewHistoryStatusesTable.date,'%d.%m.%Y %H:%i:%s') as status_date",
            "status_country_trans_tbl.name_short as status_country",

            "(select name_short from $countryTranslateTable where lang = '$lang' and country_id = sender_cp_addres.country_id) as sender_country",
            "(select name_short from $countryTranslateTable where lang = '$lang' and country_id = receiver_cp_addres.country_id) as receiver_country",
            "ma_get_city(sender_cp_addres.id,'$lang') as sender_city",
            "ma_get_city(receiver_cp_addres.id,'$lang') as receiver_city",
            "ma_get_address(sender_cp_addres.id,'$lang') as sender_address",
            "ma_get_address(receiver_cp_addres.id,'$lang') as receiver_address",
            "coalesce(sender_pp.display_name_$lang,sender_le.display_name_$lang) as sender_counterparty",
            "coalesce(receiver_pp.display_name_$lang,receiver_le.display_name_$lang) as receiver_counterparty",

            "$cargoTypeTable.name_$lang as shipment_type_name",
            "$ewTable.general_desc",
            "$ewTable.customs_declaration_cost",
            "customs_declaration_cost_cur.name_short as customs_declaration_cost_cur_name",
            "DATE_FORMAT($ewTable.est_delivery_date,'%d.%m.%Y %H:%i:%s') as est_delivery_date",

            "(select GROUP_CONCAT(CONCAT_WS(' - ', wb_order_num, $orderTypeTable.name_$lang, $carrierTable.name_$lang,
                    DATE_FORMAT(wb_order_date,'%d.%m.%Y %H:%i:%s')) SEPARATOR '; ')
                from $ewRelOrdersTable left join $orderTypeTable on $ewRelOrdersTable.wb_order_type=$orderTypeTable.id
                        left join $carrierTable on carrier_id=$carrierTable.id
                    where ew_id = $ewTable.id) as related_order",

            "$payerTypeTable.name_$lang as payer_type_name",
            "$ewTable.total_dimensional_weight_kg",
            "$ewTable.total_actual_weight_kg",
            "$ewTable.cargo_est_weight_kg",
            "$costTable.int_delivery_cost_full as delivery_cost",
            "cost_cur.name_short as cost_cur_name",
            "(select coalesce((select 'checked' from $addServiceTable where ew_id = $ewTable.id group by ew_id), 'notchecked')) as add_service_count_checked",
            "CASE WHEN $listStatusEwTable.code = '1-3' THEN '" . self::STATUS_COLOR_13 . "'
                WHEN $listStatusEwTable.code = '3-1' THEN '" . self::STATUS_COLOR_31 . "'
                WHEN $listStatusEwTable.code like '5-%' THEN '" . self::STATUS_COLOR_5 . "' ELSE '' END as row_color"
        ];

        $report_data = (new Query)
            ->select($columns)
            ->from($ewTable)
            ->leftJoin($ewHistoryStatusesTable,"$ewHistoryStatusesTable.ew_id = $ewTable.id and $ewHistoryStatusesTable.id = (select max(id) from $ewHistoryStatusesTable where ew_id=$ewTable.id and date=(select max(date) from $ewHistoryStatusesTable where ew_id=$ewTable.id))")
            ->leftJoin("$ewStatusesTranslateTable","$ewStatusesTranslateTable.status_ew_id = $ewHistoryStatusesTable.status_ew_id and $ewStatusesTranslateTable.lang='$lang'")
            ->leftJoin("$listStatusEwTable", "$listStatusEwTable.id = $ewHistoryStatusesTable.status_ew_id")
            ->leftJoin("$countryTranslateTable status_country_trans_tbl", "status_country_trans_tbl.country_id = $ewHistoryStatusesTable.status_country and status_country_trans_tbl.lang='$lang'")
            ->leftJoin("$addressTable sender_cp_addres","sender_cp_addres.id = $ewTable.sender_cp_address_id")
            ->leftJoin("$addressTable receiver_cp_addres","receiver_cp_addres.id = $ewTable.receiver_cp_address_id")
            ->leftJoin("$privatePersTable sender_pp", "sender_pp.counterparty = $ewTable.sender_counterparty_id")
            ->leftJoin("$legalEntityTable sender_le", "sender_le.counterparty = $ewTable.sender_counterparty_id")
            ->leftJoin("$privatePersTable receiver_pp", "receiver_pp.counterparty = $ewTable.receiver_counterparty_id")
            ->leftJoin("$legalEntityTable receiver_le", "receiver_le.counterparty = $ewTable.receiver_counterparty_id")
            ->leftJoin("$cargoTypeTable", "$cargoTypeTable.id = $ewTable.shipment_type")
            ->leftJoin("$currencyTranslateTable customs_declaration_cost_cur", "customs_declaration_cost_cur.currency_id = $ewTable.customs_declaration_currency and customs_declaration_cost_cur.lang='$lang'")
            ->leftJoin("$payerTypeTable", "$payerTypeTable.id = $ewTable.payer_type")
            ->leftJoin("$costTable", "$costTable.ew_id = $ewTable.id")
            ->leftJoin("$currencyTranslateTable cost_cur", "cost_cur.currency_id = $costTable.int_delivery_full_currency and cost_cur.lang='$lang'")
            ->where($where)
            ->orderBy("$ewTable.date desc");

        /*echo '<pre>';
        var_dump(CommonModel::getDataWithLimits($report_data));
        exit;*/

        return json_encode(CommonModel::getDataWithLimits($report_data));
    }



}
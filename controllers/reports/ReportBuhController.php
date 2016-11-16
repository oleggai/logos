<?php

namespace app\controllers\reports;

use app\controllers\CommonController;
use app\models\counterparty\Counterparty;
use app\models\counterparty\ListPersonType;
use app\models\dictionaries\currency\Currency;
use app\models\ew\PayerType;
use Yii;
use app\models\ew\ExpressWaybill;
use app\models\common\CommonModel;
use yii\helpers\Url;

class ReportBuhController extends CommonController {

    /**
     * ОТЧЕТ
     * Для проверки счетов оплаченных физическими лицами
     * @param bool $withItems
     * @return array
     */
    /*
     * Фильтры для грида отчета
     */
    public function provschetafizlic_filters($withItems = true) {
        return [
            [
                'id' => 'f_receiver_counterparty',
                'label' => Yii::t('buhreports', 'Counterparty') . ':',
                'field' => 'receiver_counterparty_id',
                'operation' => '=',
                'type' => CommonModel::FILTER_SELECT2,
                'items' => $withItems ? Counterparty::getListFast() : [],

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'counterparty_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['counterparty/counterparty/index']),
                'select_tab_uniqname' => 'filterfizlic_counterparty',
                'view_tab_title' => Yii::t('tab_title', 'counterparty_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['counterparty/counterparty/view']),
                'view_tab_uniqname' => 'counterparty_{0}',
            ],
            [
                'id' => 'f_payer_type',
                'label' => Yii::t('buhreports', 'Payer type') . ':',
                'field' => 'payer_type',
                'operation' => '=',
                'type' => CommonModel::FILTER_DROPDOWN,
                'items'=>$withItems ? PayerType::getList('name',true):[],
            ],
            [
                'id' => 'f_ew_date_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date create from') . ':',
                'operation' => '>=',
                'field' => 'date'
            ],
            [
                'id' => 'f_ew_date_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date create to') . ':',
                'operation' => '<=',
                'field' => 'date'
            ],
            [
                'id' => 'f_ew_date_close_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date close from') . ':',
                'operation' => '>=',
                'field' => 'closing_date'
            ],
            [
                'id' => 'f_ew_date_close_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date close to') . ':',
                'operation' => '<=',
                'field' => 'closing_date'
            ],
        ];
    }

    /**
     * формирование url для отчета (вывод грида)
     */
    public function actionProvschetafizlic() {
        return $this->render('provschetafizlic', ['filters' => $this->provschetafizlic_filters()]);
    }

    /**
     * Формирование данных для отчета
     * @param bool $all
     * @return array
     * @internal param array $filters
     */
    public function provschetafizlic_getdata($all=true) {

        $ewTable = ExpressWaybill::tableName();
        $constDeleted = CommonModel::STATE_DELETED;
        $constPrivate = ListPersonType::PERSON_TYPE_PRIVATE;
        $const3Party = ExpressWaybill::PAYER_TYPE_THIRDPARTY;

        $where  = " $ewTable.state!= $constDeleted and $ewTable.payer_type != $const3Party and".
                  "  ( cp_sender.person_type = $constPrivate or cp_receiver.person_type = $constPrivate) ";

        //добавление фильтров
        $dop_where = $this->getFiltersWhere(null, $this->provschetafizlic_filters(false));
        if ($dop_where != '') {
            $where = $where . ' and ' . $dop_where;
        };

        $ews = $this->getEwsWhere($where, $all, $data);

        $report_data = [];
        $payer_spr = [
            '1' => Yii::t('buhreports', 'Sender'),
            '2' => Yii::t('buhreports', 'Receiver'),
            '3' => Yii::t('buhreports', 'Third party'),
        ];

        $npp = 1;
        foreach ($ews as $ew) {

            //определяем тип услуги
            $field_service_type_str = '';
            $field_service_type = $ew->service_type;
            if (($field_service_type == '1') || ($field_service_type == '3')) {
                $field_service_type_str = Yii::t('buhreports', 'To station');
            };
            if (($field_service_type == '2') || ($field_service_type == '4')) {
                $field_service_type_str = Yii::t('buhreports', 'To home');
            };
            if (($field_service_type == '5') || ($field_service_type == '6')) {
                $field_service_type_str = Yii::t('buhreports', 'By self from CSS');
            };

            //формируем поле - плательщик
            $payer_str = $payer_spr[$ew->payer_type];


            //формируем поле - итоговый вес
            $itog_en = $ew->getItogiArray();


            //формируем Дата выпуска из ЦСС ЕН
            $date_vip_iz_css = '';
            //TODO-Logos: после реализации реестра
            /* if (isset($pari_ews_reestr[$en_->title]))
              {
              $date_vip_iz_css=date("d.m.Y", strtotime(node_load($pari_ews_reestr[$en_->title])->field_registry_date['und'][0]['value']));
              }; */

            //формируем итоговые строки результируещенго массива
            $report_data[] = array(
                'id' => $ew->id,
                'ew_num' => $ew->ew_num,
                'state' => $ew->state,
                'npp' => $npp++,
                //Дата выпуска из ЦСС ЕН
                'F1' => $date_vip_iz_css,
                //Дата закрытия ЕН
                'F2' => $ew->_closing_date,
                //Тип услуги
                'F3' => $field_service_type_str,
                //ЕН
                'F4' => $ew->ew_num,
                //Вес, кг
                'F5' => $itog_en['itog_weight'],
                //Стоимость доставки МЕО
                'F6' => $ew->int_delivery_cost_full,
                'F6a' => Currency::getById($ew->int_delivery_full_currency)->nameFull,
                //Стоимость доставки МЕО, грн
                'F7' => $ew->int_delivery_cost_full_uah,
                //Стоимость доставки по Украине, ГРН
                'F8' => '',
                //Стоимость брокерских услуг, грн
                'F9' => $ew->clearance_cost,
                //Стоимость таможенных платежей, грн
                'F10' => $ew->customs_clearance_charge,
                //ИТОГО ПО ЕН:
                'F11' => $ew->total_pay_cost_uah,
                'payer' => $payer_str,
                'payer_contragent' => $ew->receiverCounterparty->counterpartyName,
                'en_dt_create' => date("d.m.Y", strtotime($ew->date)),
                    //'entitle_withurl'=>'<a href="'.$base_url.'/node/'.$en_->nid.'/edit">'.$en_->title."</a>"
            );
        }

        if ($all)
            return $report_data;

        $data['data'] = $report_data;
        return $data;
    }

    /**
     * callback для получения данных для грида отчета
     */
    public function actionGetProvschetafizlic() {
        $data = $this->provschetafizlic_getdata(false);
        return json_encode($data);
    }

    /**
     * callback для формирования xls отчета
     */
    public function actionProvschetafizlicxls() {
        $data = $this->provschetafizlic_getdata();
        return $this->render('provschetafizlicxls', ['data' => $data]);
    }

    /**
     * ОТЧЕТ
     * Для проверки счетов оплаченных юридическими лицами
     * @param bool $withItems
     * @return array
     */
    /*
     * Фильтры для грида отчета
     */
    public function provschetaurlic_filters($withItems = true) {
        return [
            [
                'id' => 'f_receiver_counterparty',
                'label' => Yii::t('buhreports', 'Counterparty') . ':',
                'field' => 'receiver_counterparty_id',
                'operation' => '=',
                'type' => CommonModel::FILTER_SELECT2,
                'items' => $withItems?Counterparty::getListFast():[],

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'counterparty_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['counterparty/counterparty/index']),
                'select_tab_uniqname' => 'filterfizlic_counterparty',
                'view_tab_title' => Yii::t('tab_title', 'counterparty_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['counterparty/counterparty/view']),
                'view_tab_uniqname' => 'counterparty_{0}',
            ],
            [
                'id' => 'f_payer_type',
                'label' => Yii::t('buhreports', 'Payer type') . ':',
                'field' => 'payer_type',
                'operation' => '=',
                'type' => CommonModel::FILTER_DROPDOWN,
                'items'=>$withItems?PayerType::getList('name',true):[],
            ],
            [
                'id' => 'f_ew_date_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date create from') . ':',
                'operation' => '>=',
                'field' => 'date'
            ],
            [
                'id' => 'f_ew_date_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date create to') . ':',
                'operation' => '<=',
                'field' => 'date'
            ],
            [
                'id' => 'f_ew_date_close_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date close from') . ':',
                'operation' => '>=',
                'field' => 'closing_date'
            ],
            [
                'id' => 'f_ew_date_close_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date close to') . ':',
                'operation' => '<=',
                'field' => 'closing_date'
            ],
        ];
    }

    /**
     * формирование url для отчета (вывод грида)
     */
    public function actionProvschetaurlic() {
        return $this->render('provschetaurlic', ['filters' => $this->provschetaurlic_filters()]);
    }

    /**
     * Формирование данных для отчета
     * @param array $filters
     * @return array
     */
    public function provschetaurlic_getdata($all=true) {

        $ewTable = ExpressWaybill::tableName();
        $constDeleted = CommonModel::STATE_DELETED;
        $constLegal = ListPersonType::PERSON_TYPE_LEGAL;
        $const3Party = ExpressWaybill::PAYER_TYPE_THIRDPARTY;

        $where  = " $ewTable.state!= $constDeleted and $ewTable.payer_type != $const3Party and".
            " cp_receiver.person_type = $constLegal ";

        //добавление фильтров
        $dop_where = $this->getFiltersWhere(null, $this->provschetafizlic_filters(false));
        if ($dop_where != '') {
            $where = $where . ' and ' . $dop_where;
        };

        $report_data = [];
        $ews = $this->getEwsWhere($where, $all, $data);

        $npp = 1;
        foreach ($ews as $ew) {


            //определяем тип услуги
            $field_service_type_str = '';
            $field_service_type = $ew->service_type;
            if (($field_service_type == '1') || ($field_service_type == '3')) {
                $field_service_type_str = Yii::t('buhreports', 'To station');
            };
            if (($field_service_type == '2') || ($field_service_type == '4')) {
                $field_service_type_str = Yii::t('buhreports', 'To home');
            };
            if (($field_service_type == '5') || ($field_service_type == '6')) {
                $field_service_type_str = Yii::t('buhreports', 'By self from CSS');
            };


            //формируем поле - итоговый вес
            $itog_en = $ew->getItogiArray();


            if ($ew->int_delivery_full_currency)
                $currency = Currency::getById($ew->int_delivery_full_currency)->getNameShortEn();

            //формируем итоговые строки результируещенго массива
            $report_data[] = array(
                'id' => $ew->id,
                'state' => $ew->state,
                'npp' => $npp++,
                'contragent' => $ew->receiverCounterparty->counterpartyName,
                'ew_dt_create' => isset($ew->date) ? date("d.m.Y", strtotime($ew->date)) : '',
                'dt_tamoj_ochistki' => $ew->doc_info_for_customs,
                'ew_dt_close' => $ew->date = null ? date("d.m.Y", strtotime($ew->closing_date)) : '',
                'tip_usligi' => $field_service_type_str,
                'ew_num' => $ew->ew_num,
                'ves' => $itog_en['itog_weight'],
                'stoim_meo' => $ew->int_delivery_cost_full,
                'stoim_meo_curr' => $currency,
                'stoim_meo_grn' => $ew->int_delivery_cost_full_uah,
                'stoim_dost_po_ukr' => '',
                'stoim_brok_uslug' => $ew->clearance_cost,
                'itogo' => $ew->total_pay_cost_uah,
            );
        }

        if ($all)
            return $report_data;

        $data['data'] = $report_data;
        return $data;
    }

    /**
     * callback для получения данных для грида отчета
     */
    public function actionGetProvschetaurlic() {
        return json_encode($this->provschetaurlic_getdata(false));
    }

    /**
     * callback для формирования xls отчета
     */
    public function actionProvschetaurlicxls() {
        $data = $this->provschetaurlic_getdata();
        return $this->render('provschetaurlicxls', ['data' => $data]);
    }

    /**
     * ОТЧЕТ
     * Счета партнеру
     */
    /*
     * Фильтры для грида отчета
     */
    public function schetapartneru_filters($withItems = true) {
        return [
            // Контрагент отправителя (max символов 128)
            [
                'id' => 'f_gruzootpravitel',
                'label' => Yii::t('buhreports', 'Counterparty') . ':',
                'type' => CommonModel::FILTER_SELECT2, 'operation' => '=', 'field' => 'sender_counterparty_id',
                'items' =>$withItems?Counterparty::getListFast():[],


                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'Calculations with partners').': '.Yii::t('tab_title', 'counterparty_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['counterparty/counterparty/index']),
                'select_tab_uniqname'=>'schetapartneru_findcounterparty',
                'view_tab_title'=>Yii::t('tab_title', 'counterparty_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['counterparty/counterparty/view']),
                'view_tab_uniqname'=>'counterparty_{0}',

            ],
            [
                // Перевозчик (max символов 128)
                // Дополнительный фильтр
                'id' => 'f_perevozchik',
                'label' => Yii::t('buhreports', 'Carrier') . ':',
                'type' => CommonModel::FILTER_SELECT2, 'operation' => '=', 'field' => 'lastManifest.carriers_id',
                'items' =>$withItems?Counterparty::getList('counterpartyName',true,null,1000):[], // перевозчики


                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'Calculations with partners').': '.Yii::t('buhreports', 'Carrier') .' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['counterparty/counterparty/index-carrier']),
                'select_tab_uniqname'=>'schetapartneru_findcounterparty_carriers',
                'view_tab_title'=>Yii::t('tab_title', 'counterparty_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['counterparty/counterparty/view']),
                'view_tab_uniqname'=>'counterparty_{0}',

            ],
            [
                'id' => 'f_ew_date_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date create from') . ':',
                'operation' => '>=',
                'field' => 'date'
            ],
            [
                'id' => 'f_ew_date_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date create to') . ':',
                'operation' => '<=',
                'field' => 'date'
            ],
            [
                'id' => 'f_ew_date_close_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date close from') . ':',
                'operation' => '>=',
                'field' => 'closing_date'
            ],
            [
                'id' => 'f_ew_date_close_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date close to') . ':',
                'operation' => '<=',
                'field' => 'closing_date'
            ],
        ];
    }

    /**
     * формирование url для отчета (вывод грида)
     */
    public function actionSchetapartneru() {
        return $this->render('schetapartneru', ['filters' => $this->schetapartneru_filters()]);
    }

    /**
     * Формирование данных для отчета
     * @param array $filters
     * @return array
     */
    public function schetapartneru_getdata($all=true) {

        $ewTable = ExpressWaybill::tableName();
        $constDeleted = CommonModel::STATE_DELETED;
        $constShipper = ExpressWaybill::PAYER_TYPE_SHIPPER;

        $where  = " $ewTable.state!= $constDeleted and $ewTable.payer_type = $constShipper";
        //добавление фильтров
        $dop_where = $this->getFiltersWhere(null, $this->schetapartneru_filters(false));
        if ($dop_where != '') {
            $where = $where . ' and ' . $dop_where;
        };


        $ews = $this->getEwsWhere($where, $all, $data);

        $report_data = [];
        $npp = 1;
        foreach ($ews as $ew) {

            $continue = false;

            //определяем тип услуги
            $field_service_type_str = '';
            $field_service_type = $ew->service_type;
            if (($field_service_type == '1') || ($field_service_type == '3')) {
                $field_service_type_str = Yii::t('buhreports', 'To station');
            };
            if (($field_service_type == '2') || ($field_service_type == '4')) {
                $field_service_type_str = Yii::t('buhreports', 'To home');
            };
            if (($field_service_type == '5') || ($field_service_type == '6')) {
                $field_service_type_str = Yii::t('buhreports', 'By self from CSS');
            };

            //формируем поле перевозчик
            $lastMn = $ew->lastManifest;
            $perevozchik = $lastMn->carriers_name_new;


            //if ($ew->receiver_type!='1') continue;
            //формируем поле - итоговый вес
            $itog_en = $ew->getItogiArray();


            //формируем Дата выпуска из ЦСС ЕН
            $date_vip_iz_css = '';
            //TODO-Logos: после реализации реестра
            /* if (isset($pari_ews_reestr[$en_->title]))
              {
              $date_vip_iz_css=date("d.m.Y", strtotime(node_load($pari_ews_reestr[$en_->title])->field_registry_date['und'][0]['value']));
              }; */

            $currency = '';
            if ($ew->int_delivery_full_currency)
                $currency = Currency::getById($ew->int_delivery_full_currency)->getNameShortEn();

            //формируем итоговые строки результируещенго массива

            $report_data[] = array(
                'id' => $ew->id,
                'state' => $ew->state,
                'npp' => $npp++,
                'ew_dt_create' => isset($ew->date) ? date("d.m.Y", strtotime($ew->date)) : '',
                'ew_dt_close' => isset($ew->closing_date) ? date("d.m.Y", strtotime($ew->closing_date)) : '',
                'tip_usligi' => $field_service_type_str,
                'ew_num' => $ew->ew_num,
                'ves' => $itog_en['itog_weight'],
                'stoim_meo_usd' => $ew->int_delivery_cost_full_usd,
                'stoim_meo' => $ew->int_delivery_cost_full,
                'stoim_meo_curr' => $currency,
                'gruzootpravitel' => $ew->senderCounterparty->counterpartyName,
                'strana_otpav' => $ew->sender_cp_address->countryModel->nameShort,
                'adres_otprav' => $ew->sender_cp_address->adress_full,
                'gruzopuluch' => $ew->receiverCounterparty->counterpartyName,
                'perevozchik' => $perevozchik,
            );
        }

        if ($all)
            return $report_data;

        $data['data'] = $report_data;
        return $data;
    }

    /**
     * callback для получения данных для грида отчета
     */
    public function actionGetSchetapartneru() {
        return json_encode($this->schetapartneru_getdata(false));
    }

    /**
     * callback для формирования xls отчета
     */
    public function actionSchetapartneruxls() {
        $data = $this->schetapartneru_getdata();
        return $this->render('schetapartneruxls', ['data' => $data]);
    }

    /**
     * ОТЧЕТ
     * Проверки входящих счетов
     */
    /*
     * Фильтры для грида отчета
     */
    public function provschetavhod_filters($withItems = true) {
        $payer = [
            '' => '',
            '1' => Yii::t('buhreports', 'Sender'),
            '2' => Yii::t('buhreports', 'Receiver'),
            '3' => Yii::t('buhreports', 'Third party'),
        ];
        $type = [
            '' => '',
            1 => Yii::t('buhreports', 'To station'),
            2 => Yii::t('buhreports', 'To home'),
            5 => Yii::t('buhreports', 'By self from CSS'),
        ];
        return [
            // Плательщик (выбор: отправитель, получатель, третье лицо)
            [
                'id' => 'f_payer',
                'type' => CommonModel::FILTER_DROPDOWN,
                'items' => $payer,
                'label' => Yii::t('buhreports', 'Payer') . ':',
                'field' => 'payer_type',
                'operation' => '=',
            ],
            [
                'id' => 'f_receiver_counterparty',
                'label' => Yii::t('buhreports', 'Counterparty') . ':',
                'field' => '(case payer_type when 1 then sender_counterparty_id when 2 then receiver_counterparty_id else payer_third_party_id end )',
                'operation' => '=',
                'type' => CommonModel::FILTER_SELECT2,
                'items' => $withItems?Counterparty::getListFast():[],

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'Incloming calculations').': '.Yii::t('tab_title', 'counterparty_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['counterparty/counterparty/index']),
                'select_tab_uniqname'=>'provschetavhod_findcounterparty',
                'view_tab_title'=>Yii::t('tab_title', 'counterparty_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['counterparty/counterparty/view']),
                'view_tab_uniqname'=>'counterparty_{0}',

            ],
            // Плательщик (выбор: отправитель, получатель, третье лицо)
            [
                'id' => 'f_service_type',
                'type' => CommonModel::FILTER_DROPDOWN,
                'items' => $type,
                'label' => Yii::t('buhreports', 'Service type') . ':',
                'field' => 'service_type',
                'operation' => '=',
            ],
            [
                'id' => 'f_ew_date_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date create from') . ':',
                'operation' => '>=',
                'field' => 'date'
            ],
            [
                'id' => 'f_ew_date_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date create to') . ':',
                'operation' => '<=',
                'field' => 'date'
            ],
            [
                'id' => 'f_ew_date_close_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date close from') . ':',
                'operation' => '>=',
                'field' =>
                'closing_date'
            ],
            [
                'id' => 'f_ew_date_close_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Date close to') . ':',
                'operation' => '<=',
                'field' => 'closing_date'
            ],
        ];
    }

    /**
     * формирование url для отчета (вывод грида)
     */
    public function actionProvschetavhod() {
        return $this->render('provschetavhod', ['filters' => $this->provschetavhod_filters()]);
    }

    /**
     * Формирование данных для отчета
     * @param bool $all
     * @return array
     * @internal param array $filters
     */
    public function provschetavhod_getdata($all=true) {


        $ewTable = ExpressWaybill::tableName();
        $where = $ewTable.'.state!=' . CommonModel::STATE_DELETED;
        //добавление фильтров
        $dop_where = $this->getFiltersWhere(null, $this->provschetavhod_filters());
        if ($dop_where != '') {
            $where = $where . ' and ' . $dop_where;
        };

        $ews = $this->getEwsWhere($where, $all, $data);

        $report_data=[];
        $npp = 1;
        foreach ($ews as $ew) {

            //определяем тип услуги
            $field_service_type_str = '';
            $field_service_type = $ew->service_type;
            if (($field_service_type == '1') || ($field_service_type == '3')) {
                $field_service_type_str = Yii::t('buhreports', 'To station');
            };
            if (($field_service_type == '2') || ($field_service_type == '4')) {
                $field_service_type_str = Yii::t('buhreports', 'To home');
            };
            if (($field_service_type == '5') || ($field_service_type == '6')) {
                $field_service_type_str = Yii::t('buhreports', 'By self from CSS');
            };

            //формируем поле - плательщик
            $payer_spr = [
                '1' => 'Отправитель',
                '2' => 'Получатель',
                '3' => 'Третье лицо',
            ];
            $payer_str = $payer_spr[$ew->payer_type];

            //формируем поле - итоговый вес
            $itog_en = $ew->getItogiArray();


            //формируем Дата выпуска из ЦСС ЕН
            $date_vip_iz_css = '';
            //TODO-Logos: после реализации реестра
            /* if (isset($pari_ews_reestr[$en_->title]))
              {
              $date_vip_iz_css=date("d.m.Y", strtotime(node_load($pari_ews_reestr[$en_->title])->field_registry_date['und'][0]['value']));
              }; */


            $stoimost_dost_ukr_grn = 0;

            //формируем итоговые строки результируещенго массива
            $report_data[] = array(
                'id' => $ew->id,
                'state' => $ew->state,
                'npp' => $npp++,
                'date_vip_iz_css' => $date_vip_iz_css,
                'ew_dt_close' => $ew->closing_date != null ? date("d.m.Y", strtotime($ew->closing_date)) : '',
                'tip_usligi' => $field_service_type_str,
                'ew_num' => $ew->ew_num,
                'ves' => $itog_en['itog_weight'],
                'payer' => $payer_str,
                'payer_contragent' => $ew->payerContragent,
                'ew_dt_create' => $ew->date != null ? date("d.m.Y", strtotime($ew->date)) : '',
                'stoimost_dost_ukr_grn' => $stoimost_dost_ukr_grn
            );
        }

        if ($all)
            return $report_data;

        $data['data'] = $report_data;
        return $data;
    }

    /**
     * callback для получения данных для грида отчета
     */
    public function actionGetProvschetavhod() {
        return json_encode($this->provschetavhod_getdata(false));
    }

    /**
     * callback для формирования xls отчета
     */
    public function actionProvschetavhodxls() {
        $data = $this->provschetavhod_getdata();
        return $this->render('provschetavhodxls', ['data' => $data]);
    }

    /**
     * ОТЧЕТ
     * журнал МЕВ
     */

    /**
     * Список всех МЭБ
     * @return mixed текст контента страницы
     */
    public function actionMebjournal() {
        return $this->render('mebjournalgrid', ['filters' => $this->mebjournalgrid_filters()]);
    }

    public function mebjournalgrid_filters($withItems = true) {
        $meb_type = [
            '' => '',
            1 => 'WPX',
            2 => 'DOX'
        ];
        $t12_who = [
            '' => '',
            1 => 'Ф',
            2 => 'Ю',
        ];
        return [
            // Номер ЭН (max символов 128)
            ['id' => 'f_meb_ew_num',
                'operation' => 'like',
                'field' => 'ew_num',
                'value' => '',
                'label' => Yii::t('buhreports', 'Номер ЕН') . ':',
            ],
            // Дата ЭН
            ['id' => 'f_ew_date_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Дата ЕН з') . ':',
                'operation' => '>=',
                'field' => 'date'
            ],
            ['id' => 'f_ew_date_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Дата ЕН по') . ':',
                'operation' => '<=',
                'field' => 'date'
            ],
            // Тип МЕВ (выбор: WPX, DOX)
            ['id' => 'f_meb_type',
                'type' => CommonModel::FILTER_DROPDOWN,
                'items' => $meb_type,
                'operation' => '=',
                'field' => 'shipment_type',
                'value' => '',
                'label' => Yii::t('buhreports', 'Тип МЕВ') . ':',
            ],
            // дополнительный фильтр mn_num в коде ниже
            // Номер МН (max символов 128)
            ['id' => 'f_mn_num',
                'operation' => '<',
                'field' => '0',
                'value' => '',
                'label' => Yii::t('buhreports', 'Номер МН') . ':',
            ],
            // ??? data_postup_meb_na_css = ''
            // Дата поступления МЕВ на ЦСС с…
            ['id' => 'f_data_postup_meb_na_css_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Дата МЕВ з') . ':',
                'operation' => '<>',
                'field' => 'date'
            ],
            ['id' => 'f_data_postup_meb_na_css_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Дата МЕВ по') . ':',
                'operation' => '<>',
                'field' => 'date'
            ],
            // дополнительный фильтр в коде ниже
            // Номер ЭТД (max символов 128)
            ['id' => 'f_utd_num',
                'operation' => '<',
                'field' => '0',
                'value' => '',
                'label' => Yii::t('buhreports', 'Номер ЄТД') . ':',
            ],
            // дополнительный фильтр в коде ниже
            // Код ЭТД (max символов 128)
            ['id' => 'f_utd_code',
                'operation' => '<',
                'field' => '0',
                'value' => '',
                'label' => Yii::t('buhreports', 'Код ЄТД') . ':',
            ],
            // дополнительный фильтр в коде ниже
            // Дата ЄТД
            ['id' => 'f_data_utd_begin',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhrebuhreportsports', 'Дата ЄТД з') . ':',
                'operation' => '<>',
                'field' => 'date'
            ],
            ['id' => 'f_data_utd_end',
                'type' => CommonModel::FILTER_DATETIME,
                'label' => Yii::t('buhreports', 'Дата ЄТД по') . ':',
                'operation' => '<>',
                'field' => 'date'
            ],
            // дополнительный фильтр в коде ниже
            // Док. ( выбор: Ю, Ф)
            ['id' => 'f_t12_who',
                'type' => CommonModel::FILTER_DROPDOWN,
                'items' => $t12_who,
                'operation' => '<',
                'field' => '0',
                'value' => '',
                'label' => Yii::t('buhreports', 'Док.') . ':',
            ],
        ];
    }

    /**
     * Формирование данных для отчета
     * @return array
     * @internal param array $filters
     */
    public function actionMebjournalGetData($all=false) {

        $npp = 1;
        $report_data = [];
        $ewTable = ExpressWaybill::tableName();

        // Получаем массив аттрибутов ЭН прямым SQL запросом
        $where = "$ewTable.state!=" . CommonModel::STATE_DELETED;
        //добавление фильтров
        $dop_where = $this->getFiltersWhere(null, $this->mebjournalgrid_filters(false));
        if ($dop_where) {
            $where = $where . ' and ' . $dop_where;
        };

        $ews = $this->getEwsWhere($where, $all, $data);

        //получаем курсы валют для евро (код 2)
        $a_rates = Yii::$app->db->createCommand('SELECT * FROM {{%exchange_rates}}')->queryAll();
        $exc_rates_evr = [];
        foreach ($a_rates as $rate) {
            if ($rate['currency_parent'] == 2) {
                $exc_rates_evr[$rate['currency_child']] = $rate['ratio'];
            }
        }

        foreach ($ews as $ew) {

            $continue = false;

            $sender_country = $ew->sender_cp_address->countryModel;
            $receiver_country = $ew->receiver_cp_address->countryModel;


            //определяем тип услуги
            $field_service_type_str = '';
            $field_service_type = $ew->service_type;
            if (($field_service_type == '1') || ($field_service_type == '3')) {
                $field_service_type_str = Yii::t('buhreports', 'До станції');
            };
            if (($field_service_type == '2') || ($field_service_type == '4')) {
                $field_service_type_str = Yii::t('buhreports', 'До дверей');
            };
            if (($field_service_type == '5') || ($field_service_type == '6')) {
                $field_service_type_str = Yii::t('buhreports', 'Саморуч із ЦСС');
            };

            //формируем поле - плательщик
            $payer_spr = array('1' => 'Відправник', '2' => 'Отримувач', '3' => 'Третя особа');
            $payer_str = $payer_spr[$ew->payer_type];

            //формируем поле "контрагент плательщика"
            $payer_code = $ew->payer_type;
            $payer_contragent = '';

            if ($payer_code == 'sender') {
                $payer_contragent = $ew->senderCounterparty->counterpartyName;
                //$payer_contragent = $ew['sender_counterparty'];
            }
            if ($payer_code == 'receiver') {
                $payer_contragent = $ew->receiverCounterparty->counterpartyName;
                //$payer_contragent = $ew['receiver_counterparty'];
            }
            if ($payer_code == 'third_party') {
                $payer_contragent = $ew->payerThirdPartyCounterparty->counterpartyName;
                //$payer_contragent = $ew['payer_third_party'];
            }

            // формируем поле - итоговый вес
            $itog_en = $ew->getItogiArray();


            // ???
            $date_first_ew_place_scanning = '';
            $manif = $ew->lastManifest;

            /** todo реализовать фильтр
            // дополнительный фильтр mn_num
            if (Yii::$app->request->get('f_mn_num'))
                if (strpos($manif->mn_num, Yii::$app->request->get('f_mn_num')) === false)
                    $continue = true;
            // дополнительный фильтр utd_num
            if (Yii::$app->request->get('f_utd_num'))
                if (strpos($manif->utd_num, Yii::$app->request->get('f_utd_num')) === false)
                    $continue = true;
            // дополнительный фильтр utd_code
            if (Yii::$app->request->get('f_utd_code'))
                if (strpos($manif->utd_code, Yii::$app->request->get('f_utd_code')) === false)
                    $continue = true;
            // дополнительный фильтр utd_date
            if (Yii::$app->request->get('f_data_utd_begin'))
                if (strtotime($manif->utd_date) < strtotime(Yii::$app->request->get('f_data_utd_begin')))
                    $continue = true;
            if (Yii::$app->request->get('f_data_utd_end'))
                if (strtotime($manif->utd_date) > strtotime(Yii::$app->request->get('f_data_utd_end')))
                    $continue = true;
            **/

            //определяем направление перемещения
            $naprav_perem = Yii::t('buhreports', 'Експорт');
            $naprav_perem_code = 'tr';
            //if (($ew['sender_country'] != 828) && ($ew['receiver_countr'] == 828)) {
            if ($sender_country->id != 828 && $receiver_country->id == 828) {
                $naprav_perem = Yii::t('buhreports', 'Імпорт');
                $naprav_perem_code = 'in';
            }

            //if (($ew['sender_country'] == 828) && ($ew['receiver_country'] = 828)) {
            if ($sender_country->id == 828 && $receiver_country->id == 828) {
                $naprav_perem = Yii::t('buhreports', 'Транзит');
                $naprav_perem_code = 'out';
            }

            if ($sender_country->id != 828 && $receiver_country->id != 828) {
                $naprav_perem = Yii::t('buhreports', 'Транзит');
                $naprav_perem_code = 'out';
            }

            //определяем тип МЕБ
            $meb_type = '';
            if ($ew->shipment_type == '1')
                $meb_type = 'WPX';
            if ($ew->shipment_type == '2')
                $meb_type = 'DOX';



            //определяем данные для поля "Док."
            if ($naprav_perem_code == 'out') {
                //$t12_who = $ew['sender_type']
                //    ? $ew['sender_type']
                //    : ($o_ew->senderCounterparty->isPrivatePerson ? 1 : 2);
                $t12_who = $ew->senderCounterparty->isPrivatePerson ? 1 : 2;
            } else {
                //$t12_who = $ew['receiver_type']
                //    ?$ew['receiver_type']
                //    :($o_ew->receiverCounterparty->isPrivatePerson ? 1 : 2);
                $t12_who = $ew->receiverCounterparty->isPrivatePerson ? 1 : 2;
            }
            /** todo реализовать фильтр
            if (Yii::$app->request->get('f_t12_who') && Yii::$app->request->get('f_t12_who') != $t12_who) {
                $continue = true;
            }
             */
            switch ($t12_who) {
                case '1':$t12_who = 'Ф';
                    break;
                case '2':$t12_who = 'Ю';
                    break;
                default:$t12_who = '';
            }



            $en_vartist = $ew->customs_declaration_cost;

            //получаем данные по стоимости

            $currency = $ew->customsDeclarationCurrency;
            $valuta_name = strtoupper($currency->getNameShortEn());
            if ($currency->id != 2) {
                $kurs = $exc_rates_evr[$currency->id];
                if (!isset($kurs) || ($kurs == 0))
                    $inevro = 0;
                else
                    $inevro = $en_vartist / $kurs;
            }else {
                $inevro = $en_vartist;
                $kurs = 1;
            }




            //получаем данные по инвойсам в ЭН
            $vmist_invoice = '';
            $vmist_invoice_arr = [];
            if ($ew) {
                if ($ew->ewPositions) {
                    foreach ($ew->ewPositions as $inv) {
                        $vmist_invoice_arr[] = $inv->full_desc . ' - ' .
                                $inv->pieces_quantity . ' ' . $inv->piecesUnits->nameShort;
                    }
                }
            }
            $vmist_invoice = implode('; ', $vmist_invoice_arr);

            if ($vmist_invoice == '') {
                $vmist_invoice = $ew->general_desc;
            }


            if (!$continue)
            //формируем итоговые строки результируещенго массива
                $report_data[] = array(
                    'id' => $ew->id,
                    'ew_id' => $ew->id,
                    'state' => $ew->state,
                    'npp' => $npp++,
                    'naprav_perem' => $naprav_perem,
                    // манифест
                    'utd_num' => $manif->utd_num,
                    'utd_code' => $manif->utd_code,
                    'utd_date' => ($manif->utd_date != null) ? date("d.m.Y", strtotime($manif->utd_date)) : '',
                    'mn_num' => $manif->mn_num,
                    //Дата надходження МЕВ на ЦСС
                    'data_postup_meb_na_css' => $date_first_ew_place_scanning,
                    //Дата розміщення МЕВ у місці зберігання МЕВ на ЦСС
                    'data_razm_meb_na_css' => $date_first_ew_place_scanning,
                    //Дата початку строку зберігання МЕВ
                    'data_nach_sroka_hran' => $date_first_ew_place_scanning,
                    //Дата закінчення строку зберігання МЕВ
                    'data_okon_sroka_hran' => $ew->storage_expiration_date != null ? date("d.m.Y H:i:s", strtotime($ew->storage_expiration_date)) : '',
                    //Номер міжнародного транспортного документу
                    'ew_num' => $ew->ew_num,
                    //Дата міжнародного транспортного документу
                    'ew_date' => ($ew->date != 0) ? date("d.m.Y H:i:s", strtotime($ew->date)) : '',
                    //Тип МЕВ
                    'meb_type' => $meb_type,
                    // Док.
                    't12_who' => $t12_who,
                    //Кількість місць
                    'place_amount' => $itog_en['ews_place_count'],
                    //Вага
                    'general_weight' => Yii::$app->formatter->asDecimal($itog_en['general_weight'], 2),
                    //Вартість
                    'stoimost' => Yii::$app->formatter->asDecimal($en_vartist, 2),
                    //Валюта
                    'valuta' => $valuta_name,
                    //Курс
                    'kurs' => Yii::$app->formatter->asDecimal($kurs, 2),
                    //Вартiсть EUR
                    'inevro' => Yii::$app->formatter->asDecimal($inevro, 2),
                    //Вміст
                    'vmist_invoice' => $vmist_invoice,
                    //Країна відправлення
                    'sender_country' => $sender_country->alpha2_code,
                    //Відправник
                    'sender_name' => $ew->senderCounterparty->counterpartyName,
                    //Адреса відправника
                    'sender_adres' => $ew->sender_cp_address->adress_full,
                    //Отримувач
                    'receiver_name' => $ew->receiverCounterparty->counterpartyName,
                    //Адреса отримувача
                    'receiver_adres' => $ew->receiver_cp_address->adress_full,
                    //Станція відправлення
                    'dep_station' => $manif->dep_station,
                    //Станція призначення
                    'des_station' => $manif->des_station,
                    //Прізвище працівника ЦСС, що прийняв МЕВ на зберігання
                    'prinyal_meb' => $ew->issue_css_responsible_pers,
                    //Прізвище працівника ЦСС, що видав МЕВ
                    'vidal_meb' => $ew->doc_info_for_package_issue,
                    //Назва,  номер і дата документа на підставі якого МЕВ випущено з місця зберігання
                    'doc_info_for_package_issue' => $ew->doc_info_for_package_issue,
                    //Назва,  номер і дата документа на підставі якого здійснено митне оформлення предметів у МЕВ
                    'doc_info_for_customs' => $ew->doc_info_for_customs,
                    //Номер ОНП посадової особи, яка дозволила видачу МЕВ
                    'nomer_npo_osobi' => $ew->rec_num_allowed_issue,
                    //Примітки
                    'closing_shipment_notes' => $ew->closing_shipment_notes,
                    //Викл
                    'onoff' => 1 ? 'Так' : 'Ні',
                );
        }


        if ($all)
            return json_encode($report_data);

        $data['data'] = $report_data;
        return json_encode($data);
    }

    /**
     * @param $where
     * @param $all
     * @param $data
     * @return \app\models\ew\ExpressWaybill[]
     */
    private function getEwsWhere($where, $all, &$data) {

        $cpTable = Counterparty::tableName();
        $ewTable = ExpressWaybill::tableName();

        $query = ExpressWaybill::find()
                        ->leftJoin("$cpTable cp_receiver", "cp_receiver.id = $ewTable.receiver_counterparty_id")
                        ->leftJoin("$cpTable cp_sender", "cp_sender.id = $ewTable.sender_counterparty_id")
                        ->where($where)
                        ->orderBy("$ewTable.date desc");

        if ($all)
            return $query->all();

        $data = CommonModel::getDataWithLimits($query, false);
        return $data['data'];
    }

    public function actionLoadGuideToExcel() {
        $data = json_decode(Yii::$app->request->post()['grid-data']);
        $guideName = Yii::$app->request->post()['guide-name'];
        $model = new ExpressWaybill();
        $this->renderFile('@app/views/guide-xls/guidexls.php', ['data' => $data, 'model' => $model, 'guideName' => $guideName]);
    }

    /**
     * @param $id
     * @return ExpressWaybill
     */
    private function findEw($id){
        return ExpressWaybill::findOne($id);
    }

}

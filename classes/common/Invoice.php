<?php
namespace app\classes\common;

use app\classes\DateTimeFormatter;
use app\models\counterparty\Counterparty;
use app\models\counterparty\CounterpartyContactPers;
use app\models\counterparty\CounterpartyContactPersEmail;
use app\models\counterparty\CounterpartyContactPersPhones;
use app\models\counterparty\CounterpartyManualAdress;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\currency\Currency;
use app\models\dictionaries\exchangerate\ExchangeRate;
use app\models\ew\Units;
use yii\web\NotFoundHttpException;

class Invoice extends Common {

    public $invoicePrintCss = ['css/printforms/invoiceprintform.css', 'css/print_break.css'];

    /**
     * Формирование данных для форм печати инвойсов ЭН
     * @param $id int код ЭН
     * @param bool $boolTranslit
     * @param $patternLang
     * @param bool $apiPrintSetup Флаг обращения с АПИ
     * @param array $printSetup Настройки печати с АПИ
     * @return array массив с данными
     * @throws NotFoundHttpException
     */
    public function getDataInvoice($id, $boolTranslit = false, $patternLang, $apiPrintSetup = false, array $printSetup = []) {

        $pattern = \app\models\ew\Invoice::getPattern($patternLang);

        $model = $this->findModel($id);
        $data_inv_to_data = array();
        $invoices = $model->ewPositionsArray;
        $exc_rates = ExchangeRate::getList();
        $i = 1;
        $sum_in_euro = 0;
        $costDeclaration = 0;
        $total_weight = 0;
        $nOfPieces = 0;

        $source = 'en';
        $target = 'uk';
        $zag_var = 0;

        //получаем курсы валют для евро (код 2)
        foreach ($exc_rates as $rate) {
            if ($rate['id_parent'] == 2)
                $exc_rates_evr[$rate['id_child']] = $rate['ratio'];
        }
        $exc_rates_evr[2] = 1;

        $inv_cur = Currency::findOne(['id' => $model->invoice->currency]);
        $inv_cur_name = $inv_cur->nameShortEn;

        $senderCountry = '';
        $receiverCountry = '';
        $materialInfo = '';
        $customsCommodityCodeInfo = '';
        $kg = '';
        $senderCounterparty = '';
        $senderAssignee = '';
        $senderAddress = '';
        $receiverCounterparty = '';
        $receiverAssignee = '';
        $receiverAddress = '';
        $senderCity = '';
        $receiverCity = '';

        $cpartyCounterparty = '';
        $cpartyAddress = '';
        $cpartyCity = '';
        $cpartyPostcode = '';
        $cpartyPhoneNum = '';
        $cpartyEmail = '';
        $cpartyCountry = '';
        $cpartyAssignee = '';

        $counterPartyInvoice = Counterparty::findOne(['id' => $model->invoice->counterparty_id]);
        $counterpartyPrimaryPhone = CounterpartyContactPersPhones::findOne(['id' => $model->invoice->cp_phonenum_id]);
        $counterpartyPrimaryEmail = CounterpartyContactPersEmail::findOne(['id' => $model->invoice->cp_email_id]);
        $counterpartyPrimaryAdress = CounterpartyManualAdress::findOne(['id' => $model->invoice->cp_address_id]);
        $counterpartyPrimaryPers = CounterpartyContactPers::findOne(['id' => $model->invoice->cp_contactpers_id]);
        $counterpartyDataBool = $counterPartyInvoice ? true : false;

        $deliveryCostDataBool = true;
        $actualWeightDataBool = true;

        // Если обращение не с АПИ
        if(!$apiPrintSetup) {
            $printSetupUser = unserialize(\Yii::$app->session->get('printSetupUserModel'));
            $printSetupUserOld = unserialize(\Yii::$app->session->get('printSetupUserModelOld'));

            if ($printSetupUser) {
                if (!$printSetupUser->print_disp_third_party) {
                    $counterpartyDataBool = false;
                }
                if (!$printSetupUser->print_disp_delivery_cost) {
                    $deliveryCostDataBool = false;
                }
                if (!$printSetupUser->print_disp_actual_weight) {
                    $actualWeightDataBool = false;
                }
            }
            if ($printSetupUserOld) {
                $printSetupUser->print_disp_third_party = $printSetupUserOld->print_disp_third_party;
                $printSetupUser->print_disp_delivery_cost = $printSetupUserOld->print_disp_delivery_cost;
                $printSetupUser->print_disp_actual_weight = $printSetupUserOld->print_disp_actual_weight;
                $printSetupUser->save();
            }
        }
        // Обращение с АПИ
        else {
            if(array_key_exists('PrintDispThirdParty', $printSetup)) {
                switch ($printSetup['PrintDispThirdParty']) {
                    case 1:
                        //
                        $counterpartyDataBool = !$counterpartyDataBool ? false : true;
                        break;
                    case 0:
                        //
                        $counterpartyDataBool = false;
                        break;
                }
            }
            if(array_key_exists('PrintDispDeliveryCost', $printSetup)) {
                switch ($printSetup['PrintDispDeliveryCost']) {
                    case 1:
                        //
                        $deliveryCostDataBool = true;
                        break;
                    case 0:
                        //
                        $deliveryCostDataBool = false;
                        break;
                }
            }
            if(array_key_exists('PrintDispActualWeight', $printSetup)) {
                switch ($printSetup['PrintDispActualWeight']) {
                    case 1:
                        //
                        $actualWeightDataBool = true;
                        break;
                    case 0:
                        //
                        $actualWeightDataBool = false;
                        break;
                }
            }
        }
        $countryModel = null;
        if($model->sender_cp_address) {
            if($model->sender_cp_address->countryModel) {
                $countryModel = $model->sender_cp_address->countryModel;
            }
        }

        if($counterpartyPrimaryPhone) {
            $cpartyPhoneNum = $counterpartyPrimaryPhone->displayPhone;
        }
        if($counterpartyPrimaryEmail) {
            $cpartyEmail = $counterpartyPrimaryEmail->email;
        }

        switch($patternLang) {
            case 'en-ru':
                //
                if($counterpartyDataBool) {
                    $cpartyCountry = $countryModel->getNameOfficialEn().'/ '.$countryModel->getNameOfficialRu();

                    $cpartyCounterparty = $counterPartyInvoice->counterpartyName_en.'/ '.$counterPartyInvoice->counterpartyName_ru;
                    if($counterpartyPrimaryPers) {
                        $cpartyAssignee = $counterpartyPrimaryPers->display_name_en.'/ '.$counterpartyPrimaryPers->display_name_ru;
                    }
                    if($counterpartyPrimaryAdress) {
                        $cpartyAddress = $counterpartyPrimaryAdress->getAddressInput('en').'/ '.$counterpartyPrimaryAdress->getAddressInput('ru');
                        $cpartyCity = $counterpartyPrimaryAdress->getCityName('en', true).'/ '.$counterpartyPrimaryAdress->getCityName('ru', true);
                        $cpartyPostcode = $counterpartyPrimaryAdress->index;
                    }
                }
                //$invoiceType = InvoiceTypes::findOne($model->invoice->invoice_type)->name_en.'/ '.InvoiceTypes::findOne($model->invoice->invoice_type)->name_ru;
                $senderCountry = $model->sender_cp_address->countryModel->nameOfficialEn.'/ '.$model->sender_cp_address->countryModel->nameOfficialRu;
                $receiverCountry = $model->receiver_cp_address->countryModel->nameOfficialEn.'/ '.$model->receiver_cp_address->countryModel->nameOfficialRu;
                $customsCommodityCodeInfo = 'Customs Commodity code/ Таможенный код: ';
                $materialInfo = 'Material/ Материал: ';
                $kg = 'kg/ кг';
                $pcs = 'pcs/ шт';
                $meter = 'm/ м';
                $pack = 'pckg/ пак';
                if ($model->senderCounterparty)
                    $senderCounterparty =  $model->senderCounterparty->counterpartyName_en .'/ '.  $model->senderCounterparty->counterpartyName_ru;
                if ($model->sender_cp_contactpers)
                    $senderAssignee = $model->sender_cp_contactpers->display_name_en.'/ '. $model->sender_cp_contactpers->display_name_ru;
                if ($model->sender_cp_address)
                    $senderAddress =  $model->sender_cp_address->getAddressName('en').'/ '.$model->sender_cp_address->getAddressName('ru');
                if ($model->receiverCounterparty)
                    $receiverCounterparty =  $model->receiverCounterparty->counterpartyName_en .'/ '.  $model->receiverCounterparty->counterpartyName_ru;
                if ($model->receiver_cp_contactpers)
                    $receiverAssignee = $model->receiver_cp_contactpers->display_name_en.'/ '. $model->receiver_cp_contactpers->display_name_ru;
                if ($model->receiver_cp_address)
                    $receiverAddress =  $model->receiver_cp_address->getAddressName('en').'/ '.$model->receiver_cp_address->getAddressName('ru');
                if ($model->sender_cp_address)
                    $senderCity = $model->sender_cp_address->getCityName('en', true).'/ '.$model->sender_cp_address->getCityName('ru', true);
                if ($model->receiver_cp_address)
                    $receiverCity = $model->receiver_cp_address->getCityName('en', true).'/ '.$model->receiver_cp_address->getCityName('ru', true);
                break;
            case 'en-uk':
                //
                if($counterpartyDataBool) {
                    $cpartyCountry = $countryModel->getNameOfficialEn().'/ '.$countryModel->getNameOfficialUk();

                    $cpartyCounterparty = $counterPartyInvoice->counterpartyName_en.'/ '.$counterPartyInvoice->counterpartyName_uk;
                    if($counterpartyPrimaryPers) {
                        $cpartyAssignee = $counterpartyPrimaryPers->display_name_en.'/ '.$counterpartyPrimaryPers->display_name_uk;
                    }
                    if($counterpartyPrimaryAdress) {
                        $cpartyAddress = $counterpartyPrimaryAdress->getAddressInput('en').'/ '.$counterpartyPrimaryAdress->getAddressInput('uk');
                        $cpartyCity = $counterpartyPrimaryAdress->getCityName('en', true).'/ '.$counterpartyPrimaryAdress->getCityName('uk', true);
                        $cpartyPostcode = $counterpartyPrimaryAdress->index;
                    }
                }
                //$invoiceType = InvoiceTypes::findOne($model->invoice->invoice_type)->name_en.'/ '.InvoiceTypes::findOne($model->invoice->invoice_type)->name_uk;
                $senderCountry = $model->sender_cp_address->countryModel->nameOfficialEn.'/ '.$model->sender_cp_address->countryModel->nameOfficialUk;
                $receiverCountry = $model->receiver_cp_address->countryModel->nameOfficialEn.'/ '.$model->receiver_cp_address->countryModel->nameOfficialUk;
                $customsCommodityCodeInfo = 'Customs Commodity code/ Митний код: ';
                $materialInfo = 'Material/ Матеріал: ';
                $kg = 'kg/ кг';
                $pcs = 'pcs/ шт';
                $meter = 'm/ м';
                $pack = 'pckg/ пак';
                if ($model->senderCounterparty)
                    $senderCounterparty =  $model->senderCounterparty->counterpartyName_en .'/ '.  $model->senderCounterparty->counterpartyName_uk;
                if ($model->sender_cp_contactpers)
                    $senderAssignee = $model->sender_cp_contactpers->display_name_en.'/ '. $model->sender_cp_contactpers->display_name_uk;
                if ($model->sender_cp_address)
                    $senderAddress =  $model->sender_cp_address->getAddressName('en').'/ '.$model->sender_cp_address->getAddressName('uk');
                if ($model->receiverCounterparty)
                    $receiverCounterparty =  $model->receiverCounterparty->counterpartyName_en .'/ '.  $model->receiverCounterparty->counterpartyName_uk;
                if ($model->receiver_cp_contactpers)
                    $receiverAssignee = $model->receiver_cp_contactpers->display_name_en.'/ '. $model->receiver_cp_contactpers->display_name_uk;
                if ($model->receiver_cp_address)
                    $receiverAddress =  $model->receiver_cp_address->getAddressName('en').'/ '.$model->receiver_cp_address->getAddressName('uk');
                if ($model->sender_cp_address)
                    $senderCity = $model->sender_cp_address->getCityName('en', true).'/ '.$model->sender_cp_address->getCityName('uk', true);
                if ($model->receiver_cp_address)
                    $receiverCity = $model->receiver_cp_address->getCityName('en', true).'/ '.$model->receiver_cp_address->getCityName('uk', true);
                break;
            case 'en':
                //
                if($counterpartyDataBool) {
                    $cpartyCountry = $countryModel->getNameOfficialEn();

                    $cpartyCounterparty = $counterPartyInvoice->counterpartyName_en;
                    if($counterpartyPrimaryPers) {
                        $cpartyAssignee = $counterpartyPrimaryPers->display_name_en;
                    }
                    if($counterpartyPrimaryAdress) {
                        $cpartyAddress = $counterpartyPrimaryAdress->getAddressInput('en');
                        $cpartyCity = $counterpartyPrimaryAdress->getCityName('en', true);
                        $cpartyPostcode = $counterpartyPrimaryAdress->index;
                    }
                }
                //$invoiceType = InvoiceTypes::findOne($model->invoice->invoice_type)->name_en;
                $senderCountry = $model->sender_cp_address->countryModel->nameOfficialEn;
                $receiverCountry = $model->receiver_cp_address->countryModel->nameOfficialEn;
                $customsCommodityCodeInfo = 'Customs Commodity code: ';
                $materialInfo = 'Material: ';
                $kg = 'kg';
                $pcs = 'pcs';
                $meter = 'm';
                $pack = 'pckg';
                if ($model->senderCounterparty)
                    $senderCounterparty =  $model->senderCounterparty->counterpartyName_en;
                if ($model->sender_cp_contactpers)
                    $senderAssignee = $model->sender_cp_contactpers->display_name_en;
                if ($model->sender_cp_address)
                    $senderAddress =  $model->sender_cp_address->getAddressName('en');
                if ($model->receiverCounterparty)
                    $receiverCounterparty =  $model->receiverCounterparty->counterpartyName_en;
                if ($model->receiver_cp_contactpers)
                    $receiverAssignee = $model->receiver_cp_contactpers->display_name_en;
                if ($model->receiver_cp_address)
                    $receiverAddress =  $model->receiver_cp_address->getAddressName('en');
                if ($model->sender_cp_address)
                    $senderCity = $model->sender_cp_address->getCityName('en', true);
                if ($model->receiver_cp_address)
                    $receiverCity = $model->receiver_cp_address->getCityName('en', true);
                break;
            case 'ru':
                //
                if($counterpartyDataBool) {
                    $cpartyCountry = $countryModel->getNameOfficialRu();

                    $cpartyCounterparty = $counterPartyInvoice->counterpartyName_ru;
                    if($counterpartyPrimaryPers) {
                        $cpartyAssignee = $counterpartyPrimaryPers->display_name_ru;
                    }
                    if($counterpartyPrimaryAdress) {
                        $cpartyAddress = $counterpartyPrimaryAdress->getAddressInput('ru');
                        $cpartyCity = $counterpartyPrimaryAdress->getCityName('ru', true);
                        $cpartyPostcode = $counterpartyPrimaryAdress->index;
                    }
                }
                //$invoiceType = InvoiceTypes::findOne($model->invoice->invoice_type)->name_ru;
                $senderCountry = $model->sender_cp_address->countryModel->nameOfficialRu;
                $receiverCountry = $model->receiver_cp_address->countryModel->nameOfficialRu;
                $customsCommodityCodeInfo = 'Таможенный код: ';
                $materialInfo = 'Материал: ';
                $kg = 'кг';
                $pcs = 'шт';
                $meter = 'м';
                $pack = 'пак';
                if ($model->senderCounterparty)
                    $senderCounterparty =  $model->senderCounterparty->counterpartyName_ru;
                if ($model->sender_cp_contactpers)
                    $senderAssignee = $model->sender_cp_contactpers->display_name_ru;
                if ($model->sender_cp_address)
                    $senderAddress =  $model->sender_cp_address->getAddressName('ru');
                if ($model->receiverCounterparty)
                    $receiverCounterparty =  $model->receiverCounterparty->counterpartyName_ru;
                if ($model->receiver_cp_contactpers)
                    $receiverAssignee = $model->receiver_cp_contactpers->display_name_ru;
                if ($model->receiver_cp_address)
                    $receiverAddress =  $model->receiver_cp_address->getAddressName('ru');
                if ($model->sender_cp_address)
                    $senderCity = $model->sender_cp_address->getCityName('ru', true);
                if ($model->receiver_cp_address)
                    $receiverCity = $model->receiver_cp_address->getCityName('ru', true);
                break;
            case 'uk':
                //
                if($counterpartyDataBool) {
                    $cpartyCountry = $countryModel->getNameOfficialUk();

                    $cpartyCounterparty = $counterPartyInvoice->counterpartyName_uk;
                    if($counterpartyPrimaryPers) {
                        $cpartyAssignee = $counterpartyPrimaryPers->display_name_uk;
                    }
                    if($counterpartyPrimaryAdress) {
                        $cpartyAddress = $counterpartyPrimaryAdress->getAddressInput('uk');
                        $cpartyCity = $counterpartyPrimaryAdress->getCityName('uk', true);
                        $cpartyPostcode = $counterpartyPrimaryAdress->index;
                    }
                }
                //$invoiceType = InvoiceTypes::findOne($model->invoice->invoice_type)->name_uk;
                $senderCountry = $model->sender_cp_address->countryModel->nameOfficialUk;
                $receiverCountry = $model->receiver_cp_address->countryModel->nameOfficialUk;
                $customsCommodityCodeInfo = 'Митний код: ';
                $materialInfo = 'Матеріал: ';
                $kg = 'кг';
                $pcs = 'шт';
                $meter = 'м';
                $pack = 'пак';
                if ($model->senderCounterparty)
                    $senderCounterparty =  $model->senderCounterparty->counterpartyName_uk;
                if ($model->sender_cp_contactpers)
                    $senderAssignee = $model->sender_cp_contactpers->display_name_uk;
                if ($model->sender_cp_address)
                    $senderAddress =  $model->sender_cp_address->getAddressName('uk');
                if ($model->receiverCounterparty)
                    $receiverCounterparty =  $model->receiverCounterparty->counterpartyName_uk;
                if ($model->receiver_cp_contactpers)
                    $receiverAssignee = $model->receiver_cp_contactpers->display_name_uk;
                if ($model->receiver_cp_address)
                    $receiverAddress =  $model->receiver_cp_address->getAddressName('uk');
                if ($model->sender_cp_address)
                    $senderCity = $model->sender_cp_address->getCityName('uk', true);
                if ($model->receiver_cp_address)
                    $receiverCity = $model->receiver_cp_address->getCityName('uk', true);
                break;
        }

        $counterpartyData = [
            'cpartyCounterparty' => $cpartyCounterparty,
            'cpartyAssignee' => $cpartyAssignee,
            'cpartyAddress' => $cpartyAddress,
            'cpartyCity' => $cpartyCity,
            'cpartyPostcode' => $cpartyPostcode,
            'cpartyPhoneNum' => $cpartyPhoneNum,
            'cpartyEmail' => $cpartyEmail,
            'cpartyCountry' => $cpartyCountry
        ];

        foreach ($invoices as $key => $inv)
        {
            $totalCost = 0;
            switch($inv['unitModel']->id) {
                case Units::KG_UNIT:
                    $unit = $kg;
                    break;
                case Units::PCS_UNIT:
                    $unit = $pcs;
                    break;
                case Units::M_UNIT:
                    $unit = $meter;
                    break;
                case Units::PACK_UNIT:
                    $unit = $pack;
                    break;
            }
            $totalCost = $inv['pieces_quantity']*$inv['cost_per_piece'];

            $units_of_mesure=Units::getById($inv['units_of_measurement']);
            $zag_var += $totalCost;
            $data_inv_to_data[]=array(
                'fullDesc'     => $inv['full_desc'],
                'country'     => Country::findOne(['id' => $inv['manufacturer_country_code']])->alpha2_code,
                'unitUk'    => $units_of_mesure->name_short_uk,
                'unitEn'    => $units_of_mesure->name_short_en,
                'totalCost' => \Yii::$app->formatter->asDecimal($totalCost, 2),
                'pieces_currency_text'  => $inv['pieces_currency_text'],
                'customsGoodsCode'     => $inv['customs_goods_code'],
                'piecesQuantity'     => \Yii::$app->formatter->asDecimal($inv['pieces_quantity'], 2),
                'piecesWeight'     => $inv['pieces_weight'],
                'costPerPiece'     => $inv['cost_per_piece'],
                'material' => $inv['material'],
                'unit' => $unit
            );

            //$total_weight+=$inv['pieces_amount']*$inv['pieces_weight'];
            if (isset($exc_rates_evr[$inv['pieces_currency']])&&$exc_rates_evr[$inv['pieces_currency']]!=0) {
                //$sum_in_euro+=$inv['pieces_amount'] * $inv['cost_per_piece']/$exc_rates_evr[$inv['pieces_currency']];
                $costDeclaration += $data_inv_to_data[$key]['totalCost'];
            }

        }

        $deliveryCost = '';
        $totalWeight = '';
        if($deliveryCostDataBool) {
            $deliveryCost = $model->int_delivery_cost_full? $model->int_delivery_cost_full.' '.Currency::getById($model->int_delivery_full_currency)->nameShortEn : '';
        }
        if($actualWeightDataBool) {
            $totalWeight = $model->total_actual_weight_kg ? \Yii::$app->formatter->asDecimal($model->total_actual_weight_kg, 2) : '';
        }

        $data=array(
            'invoiceNum'     => $model->invoice->invoice_num,
            'invoiceDate'     => DateTimeFormatter::formatDateWithoutHis($model->invoice_date),
            'ewNum'     => $model->ew_num,
            'senderCounterparty'     => $senderCounterparty,
            'senderAssignee'     => $senderAssignee,
            'senderAddress'     => $senderAddress,
            'senderCountry'     => $senderCountry,
            'senderPhoneNum'     => $model->sender_cp_phonenum->displayPhone,
            'receiverCounterparty'     => $receiverCounterparty,
            'receiverAssignee'    => $receiverAssignee,
            'receiverAddress'    => $receiverAddress,
            'receiverCountry'    => $receiverCountry,
            'receiverPhoneNum'    => $model->receiver_cp_phonenum->displayPhone,
            'invoices' => $data_inv_to_data,
            'deliveryCost' => $deliveryCost,
            'zagVar'    => $model->customs_declaration_cost ? \Yii::$app->formatter->asDecimal(/*$zag_var*/$model->customs_declaration_cost, 2) : '',
            'totalWeight'    => $totalWeight,
            //'f1_24'    => count($data_inv_to_data),
            'invoiceExportPurpose'    => $model->invoice->invoice_export_purpose,
            'invoiceType' => $model->invoice->invoice_type,
            'senderPostCode' => $model->sender_cp_address->index,
            'receiverPostCode' => $model->receiver_cp_address->index,
            'nOfPieces' => $model->ewPlacesCount,
            'invCurrName'=>$inv_cur_name,
            'senderCity'   => $senderCity,
            'receiverCity' => $receiverCity,
            'ewPlacesCount' => $model->ewPlacesCount,

            // Данные о контрагенте (третья сторона)
            'counterpartyData' => $counterpartyData,
            'counterpartyDataBool' => $counterpartyDataBool,
            'incotermsName' => $model->invoice->incotermsName,
            'hotecList' => $model->invoice->invoiceStHotecList,
            'materialInfo' => $materialInfo,
            'customsCommodityCodeInfo' => $customsCommodityCodeInfo,
            'kg' => $kg
        );

        return [
            'data'    => $data,
            'pattern' => $pattern
        ];
    }

}
<?php
namespace app\classes\common;

use app\assets\AppAsset;
use app\models\dictionaries\access\User;
use app\models\dictionaries\currency\Currency;
use app\models\dictionaries\employee\Employee;
use app\models\ew\EwRelatedOrder;
use app\models\ew\WbOrderType;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class ExpressWaybill extends Common {

    public static $expressWaybillPrintCss = ['css/drupal/print.css','css/drupal/bootstrap.min.css',
        'css/printforms/print-form-ews-mesta.css','css/printforms/ew1placeprintform.css', 'css/print_break.css'];

    public function getDataMarking($id) {
        $EW_model = $this->findModel($id);
        $cr_user_id=$EW_model->creator_user_id;
        $cr_empl_id=User::findOne(['user_id'=>$cr_user_id])->employee_id;
        $ew_itogi=$EW_model->getItogiArray();
        $employee = Employee::findOne(['id'=>$cr_empl_id]);

        $source = 'en';
        $target = 'uk';

        $numbersOrder = [];

        $ewRelatedOrders = EwRelatedOrder::find()->where(['ew_id' => $EW_model->id, 'wb_order_type' => WbOrderType::ClientOrderId])->all();
        foreach($ewRelatedOrders as $ewRelatedOrder) {
            $numbersOrder[] = $ewRelatedOrder->wb_order_num;
        }
        // Sender
        $senderCountry = '';
        $senderAddress = '';
        $senderCountryNameEnUk = '';
        $senderCity = '';
        if($EW_model->sender_cp_address) {
            $counterpartyPrimaryAdress = $EW_model->sender_cp_address;
            if($countryModel = $counterpartyPrimaryAdress->countryModel) {
                $senderCountry = $countryModel->alpha2_code;
                $senderCountryNameEnUk = $countryModel->getNameOfficialEn().'/ '.$countryModel->getNameOfficialUk();
            }

            $senderCity = $counterpartyPrimaryAdress->getCityName('en', true).'/ '.$counterpartyPrimaryAdress->getCityName('uk', true);
            $slash = ($counterpartyPrimaryAdress->adress_full_en && $counterpartyPrimaryAdress->adress_full_uk) ? '/ ' : '';
            $senderAddress = $counterpartyPrimaryAdress->getAddressInput('en').$slash.$counterpartyPrimaryAdress->getAddressInput('uk');
        }
        $slash = ($EW_model->senderCounterparty->counterpartyName_en && $EW_model->senderCounterparty->counterpartyName_uk) ? '/ ': '';
        $senderContractor = $EW_model->senderCounterparty->counterpartyName_en.$slash.$EW_model->senderCounterparty->counterpartyName_uk;
        $senderRepresentative = '';
        if($EW_model->sender_cp_contactpers) {
            $counterpartyPrimaryPers = $EW_model->sender_cp_contactpers;
            $slash = ($counterpartyPrimaryPers->display_name_en && $counterpartyPrimaryPers->display_name_uk) ? '/ ' : '';
            $senderRepresentative = $counterpartyPrimaryPers->display_name_en.$slash.$counterpartyPrimaryPers->display_name_uk;
        }
        // Receiver
        $receiverCountry = '';
        $receiverAddress = '';
        $receiverCountryNameEnUk = '';
        $receiverCity = '';
        if($EW_model->receiver_cp_address) {
            $counterpartyPrimaryAdress = $EW_model->receiver_cp_address;
            if($countryModel = $counterpartyPrimaryAdress->countryModel) {
                $receiverCountry = $countryModel->alpha2_code;
                $receiverCountryNameEnUk = $countryModel->getNameOfficialEn().'/ '.$countryModel->getNameOfficialUk();
            }

            $receiverCity = $counterpartyPrimaryAdress->getCityName('en', true).'/ '.$counterpartyPrimaryAdress->getCityName('uk', true);
            $slash = ($counterpartyPrimaryAdress->adress_full_en && $counterpartyPrimaryAdress->adress_full_uk) ? '/ ' : '';
            $receiverAddress = $counterpartyPrimaryAdress->getAddressInput('en').$slash.$counterpartyPrimaryAdress->getAddressInput('uk');
        }
        $slash = ($EW_model->receiverCounterparty->counterpartyName_en && $EW_model->receiverCounterparty->counterpartyName_uk) ? '/ ': '';
        $receiverContractor = $EW_model->receiverCounterparty->counterpartyName_en.$slash.$EW_model->receiverCounterparty->counterpartyName_uk;
        $receiverRepresentative = '';
        $closingSendingReceiver = '';
        if($EW_model->receiver_cp_contactpers) {
            $counterpartyPrimaryPers = $EW_model->receiver_cp_contactpers;
            $slash = ($counterpartyPrimaryPers->display_name_en && $counterpartyPrimaryPers->display_name_uk) ? '/ ' : '';
            $receiverRepresentative = $counterpartyPrimaryPers->display_name_en.$slash.$counterpartyPrimaryPers->display_name_uk;
            $closingSendingReceiver = $EW_model->closing_sending_receiver ? $counterpartyPrimaryPers->display_name_en.$slash.$counterpartyPrimaryPers->display_name_uk : '';
        }

        $EW=array(
            'ew_num'=>$EW_model->ew_num,
            'order_num'=>$EW_model->order_num,
            'sender_country_alpha2' => $senderCountry,
            //Проставляється україномовним значенням відповідної країни. У випадку відсутності такого значення – автоматичний переклад
            'sender_country_name_en' => $senderCountryNameEnUk,
            'sender_city' => $senderCity,
            'sender_address' => $senderAddress,
            'sender_postcode'=>$EW_model->sender_cp_address->index,
            'sender_contractor'=>$senderContractor,
            'sender_id' => $EW_model->senderCounterparty->counterparty_id,
            'sender_representative'=> $senderRepresentative,
            'sender_phone_number'=>$EW_model->sender_cp_phonenum->displayPhone,
            'receiver_country_alpha_2' => $receiverCountry,
            //Проставляється україномовним значенням відповідної країни. У випадку відсутності такого значення – автоматичний переклад
            'receiver_country_name_en' =>  $receiverCountryNameEnUk,
            'receiver_city' => $receiverCity,
            'receiver_address'=>$receiverAddress,
            'receiver_postcode' => $EW_model->receiver_cp_address->index,
            'receiver_contractor'=>$receiverContractor,
            'receiver_id' => $EW_model->receiverCounterparty->counterparty_id,
            'receiver_representative'=>$receiverRepresentative,
            'receiver_phone_number'=>$EW_model->receiver_cp_phonenum->displayPhone,
            'payer_type'=>$EW_model->payer_type,
            'payer_payment_type'=>$EW_model->payer_payment_type,
            'sending_declared_value'=>$EW_model->customs_declaration_cost,
            'declared_value_currency'=>Currency::getById($EW_model->customs_declaration_currency)->nameShortEn,
            'int_delivery_cost_full_usd'=>$EW_model->int_delivery_cost_full_usd,
            'int_delivery_cost_full'=>$EW_model->int_delivery_cost_full,
            'int_delivery_full_currency'=>Currency::getById($EW_model->int_delivery_full_currency)->nameShortEn,
            'service_additional_info'=>$EW_model->closing_add_shipment_info,
            'wb_date'=>$EW_model->date,
            'employee_full_name_en'=> $employee!=null ? $employee->surnameFullEn : '',
            'employee_short_name_en'=> $employee!=null ? $employee->surnameShortEn.'/ '.$employee->surnameShortUk : '',
            'shipment_type'=>$EW_model->shipment_type,
            'general_desc'=>$EW_model->general_desc,
            'service_type'=>$EW_model->service_type,
            'sending_receiver'=>$closingSendingReceiver,
            'receiver_document_type'=>$EW_model->closing_receiver_doc_type,
            'receiver_doc_serial_num'=>$EW_model->closing_receiver_doc_serial_num,
            'document_number'=>$EW_model->closing_doc_num,
            'est_delivery_date'=>$EW_model->est_delivery_date,

            'places_array'=>$EW_model->getEwPlacesArray(true),

            'place_amount'=>$EW_model->ewPlacesCount,
            'general_volume_weight'=>$ew_itogi['general_volume_weight'],
            'general_weight'=>$ew_itogi['general_weight'],
            'docTypeList' => $EW_model->getDocTypeList(),
            'dimen_cntrl_weight_kg' => $EW_model->dimen_cntrl_weight_kg,
            'actual_cntrl_weight_kg' => $EW_model->actual_cntrl_weight_kg,

            'qrCodeLink' => Url::toRoute(['//common/qr/print', 'text' => 'http://tracking.novaposhta.international/en/?ew=' . $EW_model->ew_num, 'format' => 1]),
            'qrCodeText' => 'http://tracking.novaposhta.international/en/?ew=' . $EW_model->ew_num,
            'deliveryTypeId' => $EW_model->deliveryType->id,
            'ewAddServices' => $EW_model->ewAddServices,

            'closing_date' => $EW_model->closing_date,

            'numbersOrder' => $numbersOrder
        );
        return $EW;
    }

    /**
     * @param $html
     * @param bool $api Если пришли с АПИ то заменяем ссылки на абсолютные
     * @return string
     */
    public static function render($html, $api = false) {

        $document = new \DOMDocument();

        $hostInfo = $api ? \Yii::$app->urlManager->hostInfo : '';

        libxml_use_internal_errors(true);
        $document->loadHTML(mb_convert_encoding($html,'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $finder = new \DOMXPath($document);

        // Поиск всех елементов с указанным атрибутом
        $tds = $finder->query('//td[@data-width]');
        foreach($tds as $td) {
            $dataWidth = (int)$td->getAttribute('data-width');

            $countSymbols = strlen(trim($td->nodeValue));
            if($countSymbols == 0) {
                $countSymbols = 1;
            }
            $coefficient = $dataWidth / $countSymbols;
            if($coefficient <= 3.6) {
                $td->setAttribute('style', 'font-size: 8px;');
            }
            if($coefficient > 3.6 && $coefficient <= 4.8) {
                $td->setAttribute('style', 'font-size: 9px;');
            }
            if($coefficient > 4.8 && $coefficient <= 5.5) {
                $td->setAttribute('style', 'font-size: 10px;');
            }
            if($coefficient > 5.5 && $coefficient <= 6.4) {
                $td->setAttribute('style', 'font-size: 11px;');
            }
            if($coefficient > 6.4) {
                $td->setAttribute('style', 'font-size: 12px;');
            }
        }
        // вычисляем размер шрифта для номера ЕН
        $className = "tracking-info-2";
        $numberEwTds = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
        foreach ($numberEwTds as $numberEwTd) {
            $span = $numberEwTd->getElementsByTagName('span')->item(0);
            $countSymbols = strlen(trim($span->nodeValue));
            if($countSymbols < 25) {
                $numberEwTd->setAttribute('style', 'font-size: 15.5px;');
            }
            if($countSymbols >= 25 && $countSymbols <= 28) {
                $numberEwTd->setAttribute('style', 'font-size: 14.5px;');
            }
            if($countSymbols > 28 && $countSymbols <= 30) {
                $numberEwTd->setAttribute('style', 'font-size: 13px;');
            }
            if($countSymbols > 30 && $countSymbols <= 33) {
                $numberEwTd->setAttribute('style', 'font-size: 11px;');
            }
        }
        $elemHead = $document->createElement('head');
        $elemBody = $document->getElementsByTagName('body')->item(0);
        $elemHtml = $document->getElementsByTagName('html')->item(0);

        $elemBody->setAttribute('tabindex', '1');

        $elemHtml->setAttribute('style', 'overflow-x: hidden; height: initial;');

        $elemMeta = $document->createElement('meta');
        $elemMeta->setAttribute('name', 'viewport');
        $elemMeta->setAttribute('content', 'width=device-width, initial-scale=1');
        $elemHead->appendChild($elemMeta);


        $appAsset = new AppAsset();
        $hrefs = ArrayHelper::merge(ExpressWaybill::$expressWaybillPrintCss, $appAsset->css);
        foreach($hrefs as $href) {
            $elemLink = $document->createElement('link');

            $elemLink->setAttribute('rel', 'stylesheet');
            $href  = $hostInfo.\Yii::$app->request->baseUrl.'/'.$href;
            $elemLink->setAttribute('href', $href);

            $elemHead->appendChild($elemLink);
        }

        $elemHtml->insertBefore($elemHead, $elemBody);

        // Заменяем в картинках все ссылки на абсолютные
        $images = $document->getElementsByTagName('img');
        foreach($images as $img) {
            $src = $img->getAttribute('src');

            $img->setAttribute('src', $hostInfo.$src);
        }
        $data = $document->saveHTML();
        if(!$api) {
            // Смена доктайпа, если не сменить добавляет почему-то пустую страницу
            $pos = strpos($data, '>');
            $data = substr($data, $pos + 2);
            $data = "<!DOCTYPE html>".$data;
        }
        return $data;
    }
}
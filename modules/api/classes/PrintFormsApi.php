<?php
/**
 * Файл класса PrintForms
 * Использование
 * Пример 1
{
"appKey": "internal-app-key",
"apiKey": "e5a303bc-ec5c-40b5-b504-1bcbfdd29f87",
"modelName": "PrintForms",
"calledMethod": "printEWMarking",
"methodProperties":{
"Ref": ["2336", "2329"]
}
}
 * Пример 2 строка запроса
 * r=api/get&appKey=internal-app-key&apiKey=e5a303bc-ec5c-40b5-b504-1bcbfdd29f87&modelName=PrintForms&calledMethod=printInvoice&methodProperties[@EWNumber]=3015001969&methodProperties[TemplateLanguage]=EN-UK&Type=xlsx
 * @author Гайдаенко Олег
 * @category API/classes
 */

namespace app\modules\api\classes;

use app\assets\AppAsset;
use app\classes\common\Common;
use app\classes\common\XlsxFile;
use app\models\ew\EwRelatedOrder;
use app\models\ew\ExpressWaybill;
use app\modules\api\classes\base\BaseApiResponse;
use app\modules\api\classes\base\ErrorMsg;
use yii\base\View;
use app\classes\common\Invoice;
use yii\helpers\ArrayHelper;

/**
 * PrintForms класс
 * Отдает маркировку ЕН + динамические шрифты, инвойса
 * Используется API контроллерами
 */

class PrintFormsApi extends Common {

    // Флаг обращения с АПИ
    private $apiPrintSetup = true;

    // Параметр для печати, скрывает или нет window.print(), по умолчанию скрыто
    private $sendToPrint = 0;

    // по умолчанию 2 копии документа ЕН
    private $documentCopiesEw = 2;

    // по умолчанию 0 копии документа инвойса
    private $documentCopiesInvoice = 1;


    /**
     * @param $params
     * @return BaseApiResponse
     */
    public function printEWMarking($params) {

        $data = '';
        $ews = [];
        $arrType = ['html', 'pdf'];
        $carrier = false;

        // нужно для сортировки. Данные должны идти в той последовательности, в которой парметры в запросе
        $arrRefEws = [];
        $arrEWNumberEws = [];
        $arrRelatedWaybillNumberEws = [];


        if(array_key_exists('Ref', $params)) {

            if(count($params['Ref']) > 1000) {
                return $this->getResponse($data, ErrorMsg::GetError(515), false);
            }
            if(!$params['Ref'][0]) {
                return $this->getResponse($data, ErrorMsg::GetError(514), false);
            }

            if(!is_array($params['Ref'])) {
                $params['Ref'] = [$params['Ref']];
            }

            $ewIds = [];
            foreach($params['Ref'] as $ewId) {
                if(!$ewId) continue;
                $ewIds[] = +$ewId;
            }
            $where = 'id IN ('.implode(',', $ewIds).')';
            $a = ExpressWaybill::find()->select('id')->where($where)->all();
            foreach($a as $ew) {
                //$ews[$ew->id] = $ew;
                $arrRefEws[$ew->id] = $ew;
            }
            // Сортировка в соответствии с парметрами в запросе
            $sort = $arrRefEws;
            $arrRefEws = [];
            foreach($ewIds as $key => $ewId) {
                $ewId = (string)$ewId;
                if(!$sort[$ewId]) continue;
                $arrRefEws[$ewId] = $sort[$ewId];
            }
        }

        if(array_key_exists('EWNumber', $params)) {
            // Помилка генерується у випадку зазначення більше 1000 ідентифікаторів ІД
            if(count($params['EWNumber']) > 1000) {
                return $this->getResponse($data, ErrorMsg::GetError(515), false);
            }
            if(!$params['EWNumber'][0]) {
                return $this->getResponse($data, ErrorMsg::GetError(513), false);
            }
            if(!is_array($params['EWNumber'])) {
                $params['EWNumber'] = [$params['EWNumber']];
            }
            $ewNums = [];
            foreach($params['EWNumber'] as $ewNum) {
                if(!$ewNum) continue;
                $ewNums[] = +$ewNum;
            }
            $where = 'ew_num IN ('.implode(',', $ewNums).')';
            $a = ExpressWaybill::find()->select('id, ew_num')->where($where)->all();
            foreach($a as $ew) {
                //$ews[$ew->id] = $ew;
                $arrEWNumberEws[$ew->ew_num] = $ew;
            }
            // Сортировка в соответствии с парметрами в запросе
            $sort = $arrEWNumberEws;
            $arrEWNumberEws = [];
            $i = 0;
            foreach($ewNums as $key => $ewNum) {
                $ewNum = (string)$ewNum;
                if(!$sort[$ewNum]) continue;
                $arrEWNumberEws[$sort[$ewNum]->id] = $sort[$ewNum];
                $i++;
            }
        }

        if(array_key_exists('RelatedWaybillCarrier', $params) && !array_key_exists('RelatedWaybillNumber', $params)) {
            return $this->getResponse($data, ErrorMsg::GetError(512), false);
        }

        if(array_key_exists('RelatedWaybillNumber', $params)) {
            // Помилка генерується у випадку зазначення більше 1000 ідентифікаторів ІД
            if(count($params['RelatedWaybillNumber']) > 1000) {
                return $this->getResponse($data, ErrorMsg::GetError(515), false);
            }

            if(!is_array($params['RelatedWaybillNumber'])) {
                $params['RelatedWaybillNumber'] = [$params['RelatedWaybillNumber']];
            }

            if(!$params['RelatedWaybillNumber'][0]) {
                return $this->getResponse($data, ErrorMsg::GetError(511), false);
            }

            if(array_key_exists('RelatedWaybillCarrier', $params)) {

                if(!is_array($params['RelatedWaybillCarrier'])) {
                    $params['RelatedWaybillCarrier'] = [$params['RelatedWaybillCarrier']];
                }

                if(!$params['RelatedWaybillCarrier'][0]) {
                    return $this->getResponse($data, ErrorMsg::GetError(510), false);
                }
                $relatedWaybillCarrierIds = [];
                foreach($params['RelatedWaybillCarrier'] as $relatedWaybillCarrierId) {
                    if(!$relatedWaybillCarrierId) continue;
                    $relatedWaybillCarrierIds[] = $relatedWaybillCarrierId;
                }
            }

            $relatedWaybillNums = [];
            foreach($params['RelatedWaybillNumber'] as $relatedWaybillNum) {
                if(!$relatedWaybillNum) continue;
                $relatedWaybillNums[] = +$relatedWaybillNum;
            }
            // Формируем where
            $where = '';
            $or = ' OR ';
            $i = 1;
            if($relatedWaybillNums && $relatedWaybillCarrierIds) {
                foreach($relatedWaybillNums as $key => $relatedWaybillNum) {

                    if(!$relatedWaybillCarrierIds[$key]) {
                        $i++;
                        continue;
                    }

                    if($i == count($relatedWaybillNums)) $or = '';

                    $where .= '(wb_order_num = "'.$relatedWaybillNum.'" AND '.'carrier_id='.$relatedWaybillCarrierIds[$key].')'.$or;
                    $i++;
                }
            }
            if(!$relatedWaybillCarrierIds) {
                $str = implode(',', $relatedWaybillNums);
                $where .= 'wb_order_num IN ('.$str.')';
            }
            $orders = EwRelatedOrder::find()->select('id, ew_id, wb_order_num')->where($where)->all();
            foreach ($orders as $order) {
                //$ews[$order->ew->id] = $order->ew;
                $arrRelatedWaybillNumberEws[$order->wb_order_num] = $order->ew;
            }
            // Сортировка в соответствии с парметрами в запросе
            $sort = $arrRelatedWaybillNumberEws;
            $arrRelatedWaybillNumberEws = [];
            $i = 0;
            foreach($relatedWaybillNums as $key => $relNum) {
                $relNum = (string)$relNum;
                if(!$sort[$relNum]) continue;
                $arrRelatedWaybillNumberEws[$sort[$relNum]->id] = $sort[$relNum];
                $i++;
            }
        }


        if(array_key_exists('Type', $params)) {
            if(!in_array($params['Type'], $arrType)) {
                return $this->getResponse($data, ErrorMsg::GetError(509), false);
            }
        }
        else {
            return $this->getResponse($data, ErrorMsg::GetError(501), false);
        }

        if(array_key_exists('SendToPrint', $params)) {
            $this->sendToPrint = +$params['SendToPrint'];
        }

        if(array_key_exists('DocumentCopies', $params)) {
            if($params['DocumentCopies'] > 100) {
                return $this->getResponse($data, ErrorMsg::GetError(508));
            }
            $this->documentCopiesEw = +$params['DocumentCopies'];
            if($this->documentCopiesEw == 0) {
                $this->documentCopiesEw = 1;
            }
        }

        $ews = $this->sequence($arrRefEws, $arrEWNumberEws, $arrRelatedWaybillNumberEws, $params);

        if(!count($ews)) {
            return $this->getResponse($data, ErrorMsg::GetError(431));
        }

        $i = 1;
        $countEws = count($ews);


        switch ($params['Type']) {
            case 'html':
                foreach ($ews as $key => $ew) {
                    $data .= $this->EWMarkingHtml($ew, $countEws, $i);
                    $i++;
                }
                // Смена доктайпа, если не сменить добавляет почему-то пустую страницу
                $pos = strpos($data, '>');
                $data = substr($data, $pos + 2);
                $data = "<!DOCTYPE html>".$data;
                break;
            case 'pdf':
                $data = $this->EWMarkingPdf($ews, $countEws, $i,$params['SendToPrint'],$params['DocumentCopies']);
                break;
        }

        return $this->getResponse($data);
    }

    /**
     * @param $params
     * @return BaseApiResponse|string
     */
    public function printInvoice($params) {
        $invoices = [];
        $TemplateLanguage = 'EN';
        $arrLanguage = ['EN', 'UK', 'RU', 'EN-UK', 'EN-RU'];
        $arrType = ['html', 'pdf', 'xlsx'];
        $data = '';
        $carrier = false;

        // нужно для сортировки. Данные должны идти в той последовательности, в которой парметры в запросе
        $arrRefEws = [];
        $arrEWNumberEws = [];
        $arrRelatedWaybillNumberEws = [];

        if(array_key_exists('Ref', $params)) {
            // Помилка генерується у випадку зазначення більше 1000 ідентифікаторів ІД
            if(count($params['Ref']) > 1000) {
                return $this->getResponse($data, ErrorMsg::GetError(515), false);
            }

            if(!is_array($params['Ref'])) {
                $params['Ref'] = [$params['Ref']];
            }

            if(!$params['Ref'][0]) {
                return $this->getResponse($data, ErrorMsg::GetError(514), false);
            }

            $ewIds = [];
            foreach($params['Ref'] as $ewId) {
                if(!$ewId) continue;
                $ewIds[] = +$ewId;
            }
            $where = 'id IN ('.implode(',', $ewIds).')';
            $ews = ExpressWaybill::find()->select('id')->where($where)->all();
            foreach($ews as $ew) {
                //$invoices[$ew->id] = $ew->invoice;
                $arrRefEws[$ew->id] = $ew->invoice;
            }
            // Сортировка в соответствии с парметрами в запросе
            $sort = $arrRefEws;
            $arrRefEws = [];
            foreach($ewIds as $key => $ewId) {
                $ewId = (string)$ewId;
                if(!$sort[$ewId]) continue;
                $arrRefEws[$ewId] = $sort[$ewId];
            }
        }

        if(array_key_exists('EWNumber', $params)) {
            // Помилка генерується у випадку зазначення більше 1000 ідентифікаторів ІД
            if(count($params['EWNumber']) > 1000) {
                return $this->getResponse($data, ErrorMsg::GetError(515), false);
            }

            if(!is_array($params['EWNumber'])) {
                $params['EWNumber'] = [$params['EWNumber']];
            }

            if(!$params['EWNumber'][0]) {
                return $this->getResponse($data, ErrorMsg::GetError(513), false);
            }

            $ewNums = [];
            foreach($params['EWNumber'] as $ewNum) {
                if(!$ewNum) continue;
                $ewNums[] = +$ewNum;
            }
            $where = 'ew_num IN ('.implode(',', $ewNums).')';
            $ews = ExpressWaybill::find()->select('id, ew_num')->where($where)->all();
            foreach($ews as $ew) {
                //$invoices[$ew->id] = $ew->invoice;
                $arrEWNumberEws[$ew->ew_num] = $ew->invoice;
            }
            // Сортировка в соответствии с парметрами в запросе
            $sort = $arrEWNumberEws;
            $arrEWNumberEws = [];
            $i = 0;
            foreach($ewNums as $key => $ewNum) {
                $ewNum = (string)$ewNum;
                if(!$sort[$ewNum]) continue;
                $arrEWNumberEws[$sort[$ewNum]->ew->id] = $sort[$ewNum];
                $i++;
            }
        }

        if(array_key_exists('RelatedWaybillCarrier', $params) && !array_key_exists('RelatedWaybillNumber', $params)) {
            return $this->getResponse($data, ErrorMsg::GetError(512), false);
        }

        if(array_key_exists('RelatedWaybillNumber', $params)) {

            if(count($params['RelatedWaybillNumber']) > 1000) {
                return $this->getResponse($data, ErrorMsg::GetError(515), false);
            }

            if(!is_array($params['RelatedWaybillNumber'])) {
                $params['RelatedWaybillNumber'] = [$params['RelatedWaybillNumber']];
            }

            if(!$params['RelatedWaybillNumber'][0]) {
                return $this->getResponse($data, ErrorMsg::GetError(511), false);
            }


            if(array_key_exists('RelatedWaybillCarrier', $params)) {

                if(!is_array($params['RelatedWaybillCarrier'])) {
                    $params['RelatedWaybillCarrier'] = [$params['RelatedWaybillCarrier']];
                }

                if(!$params['RelatedWaybillCarrier'][0]) {
                    return $this->getResponse($data, ErrorMsg::GetError(510), false);
                }
                $relatedWaybillCarrierIds = [];
                foreach($params['RelatedWaybillCarrier'] as $relatedWaybillCarrierId) {
                    if(!$relatedWaybillCarrierId) continue;
                    $relatedWaybillCarrierIds[] = $relatedWaybillCarrierId;
                }
            }
            $relatedWaybillNums = [];
            foreach($params['RelatedWaybillNumber'] as $relatedWaybillNum) {
                if(!$relatedWaybillNum) continue;
                $relatedWaybillNums[] = +$relatedWaybillNum;
            }
            // Формируем where
            $where = '';
            $or = ' OR ';
            $i = 1;
            if($relatedWaybillNums && $relatedWaybillCarrierIds) {
                foreach($relatedWaybillNums as $key => $relatedWaybillNum) {

                    if(!$relatedWaybillCarrierIds[$key]) {
                        $i++;
                        continue;
                    }

                    if($i == count($relatedWaybillNums)) $or = '';

                    $where .= '(wb_order_num = "'.$relatedWaybillNum.'" AND '.'carrier_id='.$relatedWaybillCarrierIds[$key].')'.$or;
                    $i++;
                }
            }
            if(!$relatedWaybillCarrierIds) {
                $str = implode(',', $relatedWaybillNums);
                $where .= 'wb_order_num IN ('.$str.')';
            }
            $orders = EwRelatedOrder::find()->select('id, ew_id, wb_order_num')->where($where)->all();
            $ewIds = [];
            foreach ($orders as $order) {
                //$invoices[$order->ew->id] = $order->ew->invoice;
                $ewIds[] = $order->ew->id;
                $arrRelatedWaybillNumberEws[$order->wb_order_num] = $order->ew->invoice;
            }
            // Сортировка в соответствии с парметрами в запросе
            $sort = $arrRelatedWaybillNumberEws;
            $arrRelatedWaybillNumberEws = [];
            $i = 0;
            foreach($relatedWaybillNums as $key => $relNum) {
                $relNum = (string)$relNum;
                if(!$sort[$relNum]) continue;
                $arrRelatedWaybillNumberEws[$sort[$relNum]->ew->id] = $sort[$relNum];
                $i++;
            }
        }

        if(array_key_exists('Type', $params)) {
            if(!in_array($params['Type'], $arrType)) {
                return $this->getResponse($data, ErrorMsg::GetError(507), false);
            }
        }
        else {
            return $this->getResponse($data, ErrorMsg::GetError(501), false);
        }
        if(array_key_exists('TemplateLanguage', $params)) {
            if(!in_array($params['TemplateLanguage'], $arrLanguage)) {
                return $this->getResponse($data, ErrorMsg::GetError(506), false);
            }
            else {
                $TemplateLanguage = $params['TemplateLanguage'];
            }
        }

        if(array_key_exists('SendToPrint', $params)) {
            $this->sendToPrint = +$params['SendToPrint'];
        }

        $invoices = $this->sequence($arrRefEws, $arrEWNumberEws, $arrRelatedWaybillNumberEws, $params);

        if(!$invoices) {
            return $this->getResponse($data, ErrorMsg::GetError(431));
        }

        // Массив настроек печати
        $printSetup = [];
        // Настройки печати, третья сторона
        if(array_key_exists('PrintDispThirdParty', $params)) {
            if($params['PrintDispThirdParty'] != 0 && $params['PrintDispThirdParty'] != 1) {
                return $this->getResponse($data, ErrorMsg::GetError(505));
            }
            $printSetup['PrintDispThirdParty'] = $params['PrintDispThirdParty'];
        }
        // Настройки печати, стоимость доставки
        if(array_key_exists('PrintDispDeliveryCost', $params)) {
            if($params['PrintDispDeliveryCost'] != 0 && $params['PrintDispDeliveryCost'] != 1) {
                return $this->getResponse($data, ErrorMsg::GetError(504));
            }
            $printSetup['PrintDispDeliveryCost'] = $params['PrintDispDeliveryCost'];
        }
        // Настройки печати, вес
        if(array_key_exists('PrintDispActualWeight', $params)) {
            if($params['PrintDispActualWeight'] != 0 && $params['PrintDispActualWeight'] != 1) {
                return $this->getResponse($data, ErrorMsg::GetError(503));
            }
            $printSetup['PrintDispActualWeight'] = $params['PrintDispActualWeight'];
        }

        if(array_key_exists('DocumentCopies', $params)) {
            if($params['DocumentCopies'] > 100) {
                return $this->getResponse($data, ErrorMsg::GetError(508));
            }
            $this->documentCopiesInvoice = +$params['DocumentCopies'];
            if($this->documentCopiesInvoice == 0) {
                $this->documentCopiesInvoice = 1;
            }
        }

        // Нужно для печати инвойса с новой страницы. Если нет, то первый инвойс переноситься
        $i = 1;
        $k = 1;
        foreach ($invoices as $ewId => $invoice) {
            // Если один инвойс и нету позиций ИЛИ перебрали все инвойсы и оказалось что во всех нет позиций, то просто ошибка
            if((count($invoice->invoicePositions) == 0 && count($invoices) == 1)) {
                return $this->getResponse($data, ErrorMsg::GetError(531));
            }
            if(count($invoice->invoicePositions) == 0) {
                $i++;
                continue;
            }
            switch($params['Type']) {
                case 'html':
                    //
                    $data .= $this->invoiceHtml($ewId, $TemplateLanguage, $printSetup, $k);
                    break;
                case 'xlsx':
                    //
                    $ewIds = array_keys($invoices);
                    $this->invoiceXlsx($ewIds, $TemplateLanguage, $printSetup);
                    break;
                case 'pdf':
                    //
                    $this->invoicePdf($invoices, $TemplateLanguage, $printSetup,$params['SendToPrint'],$params['DocumentCopies']);
                    exit();
                    break;
            }
            $k++;
            $i++;
        }
        return $this->getResponse($data);
    }

    /**
     * Возвращает хтмл
     * @param $ew
     * @param integer $countEws Нужно для определения конца массива ЕН
     * @param integer $loopCount Нужно для определения конца массива ЕН и вставить скрипт window.print() для печати
     * @return null|string
     */
    private function EWMarkingHtml($ew, $countEws, $loopCount) {

        if(!$ew) return null;

        $view = new View();
        $expressWaybillObj = new \app\classes\common\ExpressWaybill();
        $s = DIRECTORY_SEPARATOR;
        $path = \Yii::getAlias('@app') . $s . 'views' . $s . 'ew' . $s . 'express-waybill' . $s . 'ewprintform.php';

        $html = $view->renderFile($path, ['EW' => $expressWaybillObj->getDataMarking($ew->id),
            'amount_firts_place' => $this->documentCopiesEw,'amount_another_place' => 0, 'countEws' => $countEws, 'loopCount' => $loopCount, 'api' => true, 'sendToPrint' => $this->sendToPrint]);

        return \app\classes\common\ExpressWaybill::render($html, true);
    }

    private function EWMarkingPdf($ews, $countEws, $loopCount,$send_to_print,$document_copies) {

        $view = new View();
        $expressWaybillObj = new \app\classes\common\ExpressWaybill();
        $s = DIRECTORY_SEPARATOR;
        $path = \Yii::getAlias('@app') . $s . 'views' . $s . 'ew' . $s . 'express-waybill' . $s . 'ewPdf.php';
        $EWmas=[];
        foreach($ews as $ew)
            array_push($EWmas,$expressWaybillObj->getDataMarking($ew->id));

        $html = $view->renderFile($path, ['EWS' => $EWmas,'amount_firts_place' => 2,'amount_another_place' => 0, 'countEws' => $countEws,
            'loopCount' => $loopCount, 'api' => true, 'send_to_print'=>$send_to_print,'document_copies'=>$document_copies]);

        return \app\classes\common\ExpressWaybill::render($html, true);

    }

    /**
     * Возвращает хтмл
     * @param $ewId
     * @param $TemplateLanguage
     * @param $printSetup
     * @param $pageBreakCount
     * @return string
     */
    private function invoiceHtml($ewId, $TemplateLanguage, $printSetup, $pageBreakCount) {
        $data = '';
        $view = new View();
        $invoiceObj = new Invoice();
        $s = DIRECTORY_SEPARATOR;
        $path = \Yii::getAlias('@app') . $s . 'views' . $s . 'ew' . $s . 'express-waybill' . $s . 'invoiceprintform.php';
        $dataArr = $invoiceObj->getDataInvoice($ewId, false, mb_strtolower($TemplateLanguage), $this->apiPrintSetup, $printSetup);

       for($m = 0; $m < $this->documentCopiesInvoice; $m++) {
           $html = $view->renderFile($path, ['data' => $dataArr['data'], 'pattern' => $dataArr['pattern'], 'pageBreakCount' => $pageBreakCount, 'api' => true, 'sendToPrint' => $this->sendToPrint]);

           $document = new \DOMDocument();
           libxml_use_internal_errors(true);
           $document->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
           libxml_clear_errors();

           $elemHead = $document->createElement('head');
           $elemBody = $document->getElementsByTagName('body')->item(0);
           // Добавляем параметры, если етого не сделать, то добавляет почему-то пустую страницу
           $elemBody->setAttribute('tabindex', '1');

           $elemHtml = $document->getElementsByTagName('html')->item(0);
           $elemHtml->setAttribute('style', 'overflow-x: hidden; height: initial;');

           $elemMeta = $document->createElement('meta');
           $elemMeta->setAttribute('name', 'viewport');
           $elemMeta->setAttribute('content', 'width=device-width, initial-scale=1');
           $elemHead->appendChild($elemMeta);

           $appAsset = new AppAsset();
           $hrefs = ArrayHelper::merge($invoiceObj->invoicePrintCss, $appAsset->css);
           foreach($hrefs as $href) {
               $elemLink = $document->createElement('link');
               $hostInfo = \Yii::$app->urlManager->hostInfo;

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
               $hostInfo = \Yii::$app->urlManager->hostInfo;
               $img->setAttribute('src', $hostInfo.$src);
           }
           $pageBreakCount++;
           $data .= $document->saveHTML();
       }
        // Смена доктайпа, если не сменить добавляет почему-то пустую страницу
        $pos = strpos($data, '>');
        $data = substr($data, $pos + 2);
        $data = "<!DOCTYPE html>".$data;
        return $data;
    }

    /**
     * Возвращает файл екселя
     * @param $ewIds
     * @param $TemplateLanguage
     * @param $printSetup
     * @return string
     */
    private function invoiceXlsx($ewIds, $TemplateLanguage, $printSetup) {
        foreach($ewIds as $ewId) {
            $view = new View();
            $invoiceObj = new Invoice();
            $s = DIRECTORY_SEPARATOR;
            $dataArr = $invoiceObj->getDataInvoice($ewId, false, mb_strtolower($TemplateLanguage), $this->apiPrintSetup, $printSetup);
            $path = $dataArr['data']['counterpartyDataBool']
                ?
                \Yii::getAlias('@app') . $s . 'views' . $s . 'ew' . $s .  'express-waybill' . $s .'invoice-xls-with-counterparty.php'
                :
                \Yii::getAlias('@app') . $s . 'views' . $s . 'ew' . $s .  'express-waybill' . $s . 'invoicexls.php';

            // Помещаем файл в архив
            $api = true;
            if(count($ewIds) == 1) {
                $api = false;
            }
            $view->renderFile($path, ['data' => $dataArr['data'], 'pattern' => $dataArr['pattern'], 'api' => $api]);
        }

        $xlsFileObj = XlsxFile::getInstance();
        // Отдаем архив пользователю
        $xlsFileObj->loadArchFile();
    }


    /**
     * Возвращает файл pdf
     * @param array $invoices
     * @param $TemplateLanguage
     * @param $printSetup
     * @return string
     */
    private function invoicePdf(array $invoices, $TemplateLanguage, $printSetup,$send_to_print,$document_copies) {
        $view = new View();
        $invoiceObj = new Invoice();
        $s = DIRECTORY_SEPARATOR;
        $data = [];
        // Сбор данных
        foreach($invoices as $ewId => $invoice) {
            $data[] = $invoiceObj->getDataInvoice($ewId, false, mb_strtolower($TemplateLanguage), $this->apiPrintSetup, $printSetup);
        }
        $path = \Yii::getAlias('@app') . $s . 'views' . $s . 'ew' . $s .  'express-waybill' . $s . 'invoicepdf.php';
        return $view->renderFile($path, ['data' => $data,'send_to_print'=>$send_to_print,'document_copies'=>$document_copies]);
    }



    /**
     * @param $data
     * @param $errors
     * @return BaseApiResponse
     */
    private function getResponse($data, $errors = [], $isfileformat = true) {
        $response = new BaseApiResponse();
        $response->data = $data;
        if(count($errors)) {
            $response->errors = [$errors];
        }
        $response->isfileformat = $isfileformat;
        return $response;
    }


    /**
     * Метод возвращает массив ЕН в такой последовательности как в запросе
     * @param array $Ref
     * @param array $EWNumber
     * @param array $RelatedWaybillNumber
     * @param array $params
     * @return array
     */
    private function sequence(array $Ref = [], array $EWNumber = [], array $RelatedWaybillNumber = [], array $params = []) {
        $arrKeys = [];
        // Заполняем наш массив значениями с теми ключами которые пришли в запросе, в $params
        if($this->getIndex($params, 'EWNumber') !== -1) {
            $arrKeys[$this->getIndex($params, 'EWNumber')] = 'EWNumber';
        }
        if($this->getIndex($params, 'RelatedWaybillNumber') !== -1) {
            $arrKeys[$this->getIndex($params, 'RelatedWaybillNumber')] = 'RelatedWaybillNumber';
        }
        if($this->getIndex($params, 'Ref') !== -1) {
            $arrKeys[$this->getIndex($params, 'Ref')] = 'Ref';
        }
        // делаем сортировку по ключам
        ksort($arrKeys);
        $a = $arrKeys;
        $arrKeys = [];
        // сбрасываем ключи
        foreach($a as $key => $val) {
            $arrKeys[] = $val;
        }
        // Если нет ключа, то добавляем его. Нужно для ArrayHelper, ето поможет избежать множество комбинаций передачи массивов в marge
        if(!in_array('EWNumber', $arrKeys)) {
            $arrKeys[] = 'EWNumber';
        }
        if(!in_array('Ref', $arrKeys)) {
            $arrKeys[] = 'Ref';
        }
        if(!in_array('RelatedWaybillNumber', $arrKeys)) {
            $arrKeys[] = 'RelatedWaybillNumber';
        }
        return ArrayHelper::merge($$arrKeys[0], $$arrKeys[1], $$arrKeys[2]);
    }

    /**
     * Возвращает позицию ключа в ассоциативном массиве
     * @param $a
     * @param $n
     * @return int
     */
    private function getIndex($a,$n)
    {
        $i = 0;
        foreach($a as $key => $value)
        {
            if($key == $n) return $i;
            $i++;
        }

        return -1;
    }
}
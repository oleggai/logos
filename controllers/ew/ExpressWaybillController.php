<?php

namespace app\controllers\ew;


use app\classes\DateTimeFormatter;
use app\controllers\CommonController;
use app\controllers\reports\ReportLogisticController;
use app\models\attached_doc\EwAttachedDoc;
use app\models\counterparty\CounterpartyManualAdress;
use app\models\common\CommonModel;
use app\models\counterparty\Counterparty;
use app\models\counterparty\CounterpartyContactPers;
use app\models\counterparty\CounterpartyContactPersEmail;
use app\models\counterparty\CounterpartyContactPersPhones;
use app\models\counterparty\ListPersonType;
use app\models\dictionaries\currency\Currency;
use app\models\dictionaries\access\User;
use app\models\dictionaries\employee\Employee;
use app\models\dictionaries\events\Event;
use app\models\ew\EwHistoryStatuses;
use app\models\ew\EwPlace;
use app\models\ew\EwRelatedOrder;
use app\models\ew\Invoice;
use app\models\ew\Units;
use app\models\ew\WbOrderType;
use app\models\common\Setup;
use app\models\common\sys\SysEntity;
use app\widgets\BtnCreateTab;
use Yii;
use app\models\ew\ExpressWaybill;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\exchangerate\ExchangeRate;
use app\models\ew\LogEw;
use app\models\manifest\Manifest;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use  \app\models\ew\EwAddService;

/**
 * Контроллер экспресс накладной
 */
class ExpressWaybillController extends CommonController {

    public function init() {

        $this->accessCheck = 'expresswaybill'; // имя сущности для проверки
        parent::init();
    }


    /**
     * Возвращает список прикрепленных документов
     * @return int|string
     */
    public function actionGetDocList() {
        $result = [];
        $id = Yii::$app->request->get('id');
        if (!$id) {
            return Json::encode($result);
        }
        
        $ew = ExpressWaybill::findOne($id);
        if ($ew === null) {
            return Json::encode($result);
        }
        
        foreach($ew->getAttachedDocs()->all() as $attachedDoc) {
            $result[] = $attachedDoc->toJson();
        }
        
        return Json::encode($result);
    }


    /**
     * Список ExpressWaybill моделей.
     * @param bool $printAr
     * @param bool $registryAr
     * @return string
     */
    public function actionIndex($printAr = false, $registryAr = false) {
        if ($printAr) {
            return $this->render('grid', ['model' => new ExpressWaybill(), 'printAr' => $printAr]);
        }
        if ($registryAr) {
            return $this->render('grid', ['model' => new ExpressWaybill(), 'registryAr' => $registryAr]);
        }
        return $this->render('grid', ['model' => new ExpressWaybill()]);

        /*
          $searchModel = new ExpressWaybillSearch();
          $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

          return $this->render('index', [
          'searchModel' => $searchModel,
          'dataProvider' => $dataProvider,
          ]);
         */
    }

    /**
     * Расширенній список ExpressWaybill моделей.
     * @param bool $printAr
     * @param bool $registryAr
     * @return string
     */
    public function actionAIndex($printAr = false, $registryAr = false) {
        if ($printAr) {
            return $this->render('agrid', ['model' => new ExpressWaybill(), 'printAr' => $printAr]);
        }
        if ($registryAr) {
            return $this->render('agrid', ['model' => new ExpressWaybill(), 'registryAr' => $registryAr]);
        }
        return $this->render('agrid', ['model' => new ExpressWaybill()]);
    }

    /**
     * Создание новой ExpressWaybill модели.
     * @return mixed
     */
    public function actionCreate() {
        $model = new ExpressWaybill();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->getRequest()->get());
            return $this->render('form', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * общий экшн закрытия сущности
     * @param $id int ид сущности
     * @return \yii\web\Response
     */
    public function actionClose($id) {

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $getParams = Yii::$app->getRequest()->get();
            if ($getParams['current_operation'] == CommonModel::OPERATION_VIEW)
                return $this->redirect(['view', 'id' => $model->id]);

            if ($getParams['current_operation'] == CommonModel::OPERATION_GRIDVIEW)
                return json_encode('item.state = ' . $model->state . ';item.stateText="' . $model->stateText . '"');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)) {
            return $this->redirect(['view', 'id' => $id, 'errors' => $model->errors]);
        }

        $model->closing_issued_shipment = Yii::$app->user->identity->employee_id;
        $model->closing_receiver_doc_type = null;
        $model->operation = CommonModel::OPERATION_CLOSE;
        SysEntity::saveOperation($model->getEntityCode(), $id, CommonModel::OPERATION_BEGIN_CLOSE);
        return $this->render($this->getForm(), ['model' => $model,]);

    }

    /**
     * Эншн редактирования статуса трекинга ЭН
     * @param $id ид ЭН
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditStatus($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $getParams = Yii::$app->getRequest()->get();
            if ($getParams['current_operation'] == CommonModel::OPERATION_VIEW) {
                return $this->redirect(['view', 'id' => $id]);
            }

            return $this->redirect(['view', 'id' => $id]);
        }

        if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)) {
            return $this->redirect(['view', 'id' => $id, 'errors' => $model->errors]);
        }

        $model->operation = CommonModel::OPERATION_CHANGE_STATUS;
        return $this->render($this->getForm(), ['model' => $model]);
    }

    /**
     * Эншн редактирования причины недоставки ЭН
     * @param $id ид ЭН
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditNondelivery($id) {

        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $getParams = Yii::$app->getRequest()->get();
            if ($getParams['current_operation'] == CommonModel::OPERATION_VIEW) {
                return $this->redirect(['view', 'id' => $id]);
            }

            return $this->redirect(['view', 'id' => $id]);
        }

        if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)) {
            return $this->redirect(['view', 'id' => $id, 'errors' => $model->errors]);
        }

        $model->operation = CommonModel::OPERATION_CHANGE_NONDELIVERY;
        return $this->render($this->getForm(), ['model' => $model]);
    }

    /**
     * Удаление ExpressWaybill модели.
     * @param string $id
     * @return mixed
     */
    /* public function actionDelete($id)
      {
      $this->findModel($id)->delete();

      return $this->redirect(['update', 'id' => $id]);
      } */

    /**
     * Поиск ExpressWaybill модели по первичному ключу.
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id
     * @return ExpressWaybill найденая модель
     * @throws NotFoundHttpException в случае неудачного поиска
     */
    protected function findModel($id) {
        if (($model = ExpressWaybill::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }


    /**
     * Вывод печатной формы инвойса в формат xls
     * @param $id int код ЭН
     * @param bool $boolTranslit
     * @param string $patternLang
     * @return string
     */
    public function actionInvoicexls($id, $boolTranslit = false, $patternLang = 'en-uk')
    {
        Event::callEvent(Event::EW_INVOICE_EXCEL, $id);

        $invoiceObj = new \app\classes\common\Invoice();
        $dataArr = $invoiceObj->getDataInvoice($id, $boolTranslit, $patternLang);

        return $dataArr['data']['counterpartyDataBool'] ? $this->render('invoice-xls-with-counterparty', ['data' => $dataArr['data'], 'pattern' => $dataArr['pattern']]) :
            $this->render('invoicexls', ['data' => $dataArr['data'], 'pattern' => $dataArr['pattern']]);
    }


    /**
     * Вывод печатной формы инвойса.
     * @param string $id код ЭН
     * @param bool $boolTranslit
     * @param string $patternLang
     * @return mixed
     */
    public function actionInvoiceprintform($id, $boolTranslit = false, $patternLang = 'en-uk')
    {

        Event::callEvent(Event::EW_PRINT_INVOICE, $id);

        $invoiceObj = new \app\classes\common\Invoice();
        $dataArr = $invoiceObj->getDataInvoice($id, $boolTranslit, $patternLang);

        $this->layout = 'for_print';
        $this->view->params['cssfiles'] = $invoiceObj->invoicePrintCss;

        return $this->render('invoiceprintform', ['data' => $dataArr['data'], 'pattern' => $dataArr['pattern']]);

    }




    /**
     * Формирование печатных форм ЭН (первое и остальные места)
     * http://site/web/grid.php?r=express-waybill%2Few-printform&id=7&amount_firts_place=2&amount_another_place=0
     * @param $id int код ЭН
     * @param $amount_firts_place int кол-во форм первого места на печать
     * @param $amount_another_place int кол-во форм остальных мест на печать
     * @param bool $boolTranslit
     * @param integer $numberPlace
     * @param bool $zebra
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionEwPrintform($id,$amount_firts_place,$amount_another_place, $boolTranslit = false, $numberPlace = 0, $zebra = false)
    {

        if ($amount_firts_place == 1 && $amount_another_place == 0) {
            Event::callEvent(Event::EW_PRINT_1MARK, $id);
        }
        else if ($amount_firts_place == 0 && $amount_another_place == 1) {
            Event::callEvent(Event::EW_PRINT_STICKER, $id);
        }
        else if ($amount_firts_place == 2 && $amount_another_place == 0) {
            Event::callEvent(Event::EW_PRINT_1MARK, $id);
        }

        else if ($amount_firts_place > 2 && $amount_another_place == 1) {
            Event::callEvent(Event::EW_PRINT_1MARK, $id);
            Event::callEvent(Event::EW_PRINT_STICKER, $id);
        }

        $expressWaybillObj = new \app\classes\common\ExpressWaybill();
        $EW = $expressWaybillObj->getDataMarking($id);

        // Парсим маркировку и делаем динамические шрифты
        $s = DIRECTORY_SEPARATOR;
        $path = \Yii::getAlias('@app') . $s . 'views' . $s . 'ew' . $s . 'express-waybill' . $s . 'ewprintform.php';
        $html = $this->renderFile($path, ['EW' => $EW,'amount_firts_place'=>$amount_firts_place,'amount_another_place'=>$amount_another_place, 'numberPlace' => $numberPlace, 'zebra' => $zebra]);

        return \app\classes\common\ExpressWaybill::render($html);
    }


    /**
     * Метод для печати заявив митницю в браузере
     * @return string
     */
    public function actionPrintStatement($id) {
        Event::callEvent(Event::EW_PRINT_STATEMENT, $id);
        $data = $this->getPrintStatementData($id);

        $this->layout = 'for_print';
        $this->view->params['cssfiles'] = array('css/drupal/bootstrap.min.css','css/forms.css', 'css/print_break.css', 'css/main.css');

        return $this->render('statementprintform', ['data' => $data]);
    }

    /**
     * Метод для выгрузки данных заяви в митницю в ексель
     * @return string
     */
    public function actionStatementxls($id) {
        Event::callEvent(Event::EW_STATEMENT_EXCEL, $id);
        $data = $this->getPrintStatementData($id);

        return $this->render('statementxls', ['data' => $data]);
    }

    /**
     * Метод для выгрузки данных акта ПП в ексель
     * @return string
     */
    public function actionArxls() {

        Yii::trace('PHPExcel action');
        $ews = $this->getEwsFromChecks();
        Yii::trace('PHPExcel action data loaded');
        $data = $this->getPrintArData($ews, true, false);
        Yii::trace('PHPExcel action massive loaded');
        $r = $this->render('arxls', ['data' => $data]);

        return $r;
    }


    public function getPrintStatementData($id) {
        $model = $this->findModel($id);
        $dateEW = DateTimeFormatter::format($model->date, 'uk');
        $dateEwNpiFormat = DateTimeFormatter::npiFormat($model->date);
        $invoice = $model->invoices[0];
        $totalCost = 0;
        $zagVar = 0;
        $invoicePositions = $model->ewPositionsArray;

        $positions = [];

        $invCur = Currency::findOne(['id' => $model->invoice->currency]);
        $invCurName = $invCur->nameShortEn;
        $displayNameUkPrivate = '';
        $displayNameUkLegal = '';
        $edrpuCode = '';
        $counterpartyNameUk = '';
        if($model->senderCounterparty->counterpartyLegalEntity) {
            $counterpartyLegalEntity = $model->senderCounterparty->counterpartyLegalEntity;

            switch($model->senderCounterparty->person_type) {
                case ListPersonType::PERSON_TYPE_LEGAL:
                    $displayNameUkLegal = $counterpartyLegalEntity->display_name_uk;
                    $counterpartyNameUk = $model->sender_cp_contactpers->full_name_uk;
                    $edrpuCode = $counterpartyLegalEntity->edrpou_code;
                    $koma = $displayNameUkLegal ? ', ' : '';
                    $edrpuCode = $edrpuCode ? $koma.'код ЄДРПОУ: '.$edrpuCode : '';
                    break;
                case ListPersonType::PERSON_TYPE_PRIVATE:
                    $displayNameUkPrivate = $model->sender_cp_contactpers->full_name_uk;
                    $counterpartyNameUk = $model->senderCounterparty->counterpartyName_uk;
                    $koma = $displayNameUkPrivate ? ', ' : '';
                    $taxNumber = $model->senderCounterparty->counterpartyPrivatPers->tax_number ? $koma.'Податковий номер: '.$model->senderCounterparty->counterpartyPrivatPers->tax_number : '';
                    break;
            }
        }

        foreach($invoicePositions as $key => $invoicePosition) {
            $totalCost = $invoicePosition['pieces_quantity']*$invoicePosition['cost_per_piece'];
            $zagVar += $totalCost;
            $positions[] = [
                // Повний опис вкладення
                'fullDesc' => $invoicePosition['full_desc'],
                // Митний код товару
                'customsGoodsCode' => $invoicePosition['customs_goods_code'],
                // Повна вартість вкладення
                'totalCost' => Yii::$app->formatter->asDecimal($totalCost, 2)
            ];
        }
        $counterpartyPrimaryAddress = $model->sender_cp_address;

        $counterpartyLegalAddress = '';
        $counterpartyRegistrationAddress = '';
        $counterpartyCountry = '';
        $receiverCountry = '';


            $counterpartyCountry = $counterpartyPrimaryAddress->countryModel->getNameOfficialUk();

            $arr = [];
            $counterpartyAddresses = $model->senderCounterparty->counterpartyManualAdresses;

            foreach ($counterpartyAddresses as $address) {
                if($address->adress_type == CounterpartyManualAdress::ADDRESS_TYPE_LEGAL && $address->state == 1) {
                    $arr['legal'][] = $address;
                }
                if($address->adress_type == CounterpartyManualAdress::ADDRESS_TYPE_REGISTRATION && $address->state == 1) {
                    $arr['registration'][] = $address;
                }
            }

            if($model->senderCounterparty->isLegalPerson) {
                if(count($arr['legal']) == 1) {
                    $counterpartyLegalAddress = $arr['legal'][0]->getAddressInput('uk', true);
                }
            }
            if($model->senderCounterparty->isPrivatePerson) {
                if(count($arr['registration']) == 1) {
                    $counterpartyRegistrationAddress = $arr['registration'][0]->getAddressInput('uk', true);
                }
            }


        if($model->receiver_cp_address) {
            $receiverCountry = $model->receiver_cp_address->countryModel->getNameOfficialUk();
        }

        $data = [
            // Номер ЭН
            'ewNum' => $model->ew_num,
            // Дата ЕН
            'dateEW' => $dateEW,
            'dateEwNpiFormat' => $dateEwNpiFormat,
            // Контактна особа відправника
            'counterpartyNameUk' => $counterpartyNameUk,

            'counterpartyLegalAddress' => $counterpartyLegalAddress,
            'counterpartyRegistrationAddress' => $counterpartyRegistrationAddress,

            'counterpartyLegalEntityDisplayNameUkLegal' => $displayNameUkLegal,
            'edrpuCode' => $edrpuCode,

            'counterpartyLegalEntityDisplayNameUkPrivate' => $displayNameUkPrivate,
            'taxNumber' => $taxNumber,

            'counterpartyCountry' => $counterpartyCountry,
            'receiverCountry' => $receiverCountry,

            // Номер інвойсу
            'invoiceNum' => $invoice->invoice_num,
            // Дата інвойсу
            'invoiceDate' => DateTimeFormatter::npiFormat($invoice->invoice_date),
            // Контрагент-відправник
            'senderCounterparty' => $model->senderCounterparty->counterpartyName_uk,

            // Контрагент-отримувач
            'receiverCounterparty' => $model->receiverCounterparty->counterpartyName_uk,
            // Загальна кількість місць
            'ewPlacesCount' => $model->ewPlacesCount,
            // Загальна фактична вага, кг
            'totalActualWeightKg' => Yii::$app->formatter->asDecimal($model->total_actual_weight_kg, 2),
            // Вартість митного декларування
            'zagVar' => Yii::$app->formatter->asDecimal($zagVar, 2),
            // Валюта вартості митного декларування та Валюта вартості по інвойсу
            'invCurName' => $invCurName,
            // Позиції інвойсу
            'invoicePositions' => $positions,
        ];
        return $data;
    }


    /**
     * Метод для печати акта ПП в браузере
     * @return string
     */
    public function actionPrintAr() {
        $ews = $this->getEwsFromChecks();

        $data = $this->getPrintArData($ews, false, true);
        $this->layout = 'for_print';
        $this->view->params['cssfiles'] = array('css/drupal/bootstrap.min.css','css/forms.css', 'css/print_break.css', 'css/main.css');

        return $this->render('arprintform', ['data' => $data]);
    }

    /**
     * Метод для получения данных акта ПП
     * @param ExpressWaybill[] $ews
     * @param $excel
     * @param $print
     * @return array
     * @throws NotFoundHttpException
     */
    public function getPrintArData($ews, $excel, $print) {
        $ewsData = [];
        $ewData = [];
        foreach($ews as $ew) {
            if($excel) {
                Event::callEvent(Event::EW_FORM_CARGO_AR_EXCEL, $ew['id']);
            }
            if($print) {
                Event::callEvent(Event::EW_PRINT_FORM_CARGO_AR, $ew['id']);
            }

            $ewData['ewNum'] = $ew['ew_num'];
            $ewData['ewPlacesCount'] = $ew['ewPlacesCount'];
            $ewData['totalActualWeightKg'] = $ew['total_actual_weight_kg'];
            $ewData['generalDesc'] = $ew['general_desc'];
            $ewData['zagVar'] = $ew['customs_declaration_cost'];
            $ewData['invCurName'] = $ew['customs_declaration_currency'];

            $ewsData[] = $ewData;
        }
        return $ewsData;
    }

    /**
     * Метод для получения данных о регистре ПП
     * @param ExpressWaybill[] $ews
     * @return array
     * @throws NotFoundHttpException
     */
    public function getRegistryArData($ews) {
        $ewsData = [];

        foreach($ews as $ew) {
            Event::callEvent(Event::EW_REGISTRY_TMM_EXCEL, $ew['id']);

            $ewData = [
                // Контрагент-відправник
                'senderCounterparty' => $ew['sender_counterparty'],
                // Адреса відправника
                'senderAddress' => $ew['sender_address'],
                // Код Альфа-2 країни відправника
                'senderCountryAlpha2Code' => $ew['sender_country_code'],
                // Поштовий індекс відправника
                'senderPostCode' => $ew['sender_postcode'],
                // Населений пункт відправника
                'senderCity' => $ew['sender_city'],
                // Контактна особа відправника
                'senderAssignee' => $ew['sender_pers_name'],
                //Телефон відправника
                'senderPhoneNum' => $ew['sender_phone_num'],
                // Номер ЕН
                'ewNum' => $ew['ew_num'],
                // Контрагент-отримувач
                'receiverCounterparty' => $ew['receiver_counterparty'],
                // Адреса отримувача
                'receiverAddress' =>  $ew['receiver_address'],
                // Код Альфа-2 країни отримувача
                'receiverCountryAlpha2Code' =>  $ew['receiver_country_code'],
                // Поштовий індекс отримувача
                'receiverPostCode' => $ew['receiver_postcode'],
                // Населений пункт отримувача
                'receiverCity' => $ew['receiver_city'],
                // Контактна особа отримувача
                'receiverAssignee' => $ew['receiver_pers_name'],
                //Телефон отримувача
                'receiverPhoneNum' => $ew['receiver_phone_num'],
                // Кількість місць
                'ewPlacesCount' => $ew['ewPlacesCount'],
                // массив мест ЕН
                'places' => EwPlace::find()->where('ew_id = '.$ew['id'])->asArray(true)->all(),
                // Загальний опис відправлення
                'generalDesc' => $ew['general_desc'],
                // Загальна вартість митного декларування
                'zagVar' => $ew['customs_declaration_cost'],
                // Символ валюти вартості митного
                'currSymbol' => $ew['customs_declaration_currency'],
            ];
            $ewsData[] = $ewData;
        }
        return $ewsData;
    }

    /**
     * Метод для выгрузки данных реестра в ексель
     * @return string
     */
    public function actionRegistryArXls() {

        Yii::trace('PHPExcel action');
        $ews = $this->getEwsFromChecks();

        Yii::trace('PHPExcel action get data');
        $data = $this->getRegistryArData($ews);
        Yii::trace('PHPExcel action get array');
        return $this->render('registry-ar-xls', ['data' => $data]);
    }

    /**
     * Метод для печати данных об пакувальном листе в браузере
     * @param $id
     * @return string
     */
    public function actionPrintPackingList($id) {
        Event::callEvent(Event::EW_PRINT_PACKING_LIST, $id);
        $data = $this->getPackingListData($id);
        $this->layout = 'for_print';
        $this->view->params['cssfiles'] = array('css/drupal/bootstrap.min.css','css/forms.css', 'css/print_break.css', 'css/main.css');
        return $this->render('packing-list', ['data' => $data]);
    }

    /**
     * Метод выгрузки данных об пакувальном листе в ексель
     * @param $id
     * @return string
     */
    public function actionPrintPackingListXls($id) {
        Event::callEvent(Event::EW_PACKING_LIST_EXCEL, $id);
        $data = $this->getPackingListData($id);
        return $this->render('packing-list-xls', ['data' => $data]);
    }

    /**
     * Метод для получения данных об пакувальном листе
     * @param $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function getPackingListData($id) {
        $ew = $this->findModel($id);

        $invoice = $ew->invoice;
        $invoicePositions = $ew->ewPositionsArray;
        $positions = [];

        $customsCommodityCodeInfo = 'Customs Commodity code/ Митний код: ';
        $materialInfo = 'Material/ Матеріал: ';

        $zagVar = 0;

        $invCur = Currency::findOne(['id' => $ew->invoice->currency]);
        $nOfPieces = 0;
        $totalWeight = 0;
        foreach($invoicePositions as $invoicePosition) {
            $totalCost = $invoicePosition['pieces_quantity']*$invoicePosition['cost_per_piece'];
            $zagVar += $totalCost;
            $piecesQuantity = 0;
            $piecesWeight = 0;
            switch($invoicePosition['unitModel']->id) {
                case Units::KG_UNIT:
                    $piecesWeight = $invoicePosition['pieces_quantity'];
                    $piecesQuantity = '';
                    $totalWeight += $piecesWeight;
                    break;
                case Units::PCS_UNIT:
                    $nOfPieces += $invoicePosition['pieces_quantity'];
                    $piecesWeight = '';
                    $piecesQuantity = $invoicePosition['pieces_quantity'];
                    break;
                case Units::M_UNIT:
                    $nOfPieces += $invoicePosition['pieces_quantity'];
                    $piecesWeight = '';
                    $piecesQuantity = $invoicePosition['pieces_quantity'];
                    break;
                case Units::PACK_UNIT:
                    $nOfPieces += $invoicePosition['pieces_quantity'];
                    $piecesWeight = '';
                    $piecesQuantity = $invoicePosition['pieces_quantity'];
                    break;
            }
            $positions[] = [
                'piecesQuantity' => $piecesQuantity,
                'piecesWeight' => $piecesWeight,
                'fullDesc' => $invoicePosition['full_desc'],
                'totalCost' => Yii::$app->formatter->asDecimal($totalCost, 2),
                'material' => $invoicePosition['material'],
                'customsGoodsCode' => $invoicePosition['customs_goods_code'],
            ];
        }
        $cpartyCounterparty = '';
        $cpartyAddress = '';
        $cpartyCity = '';
        $cpartyPostcode = '';
        $cpartyPhoneNum = '';
        $cpartyEmail = '';
        $cpartyCountry = '';
        if($counterPartyInvoice = Counterparty::findOne(['id' => $invoice->counterparty_id])) {
            $cpartyCounterparty = $counterPartyInvoice->counterpartyPrimaryPers ? $counterPartyInvoice->counterpartyPrimaryPers->display_name_en : '';
            $invoiceAddress = CounterpartyManualAdress::findOne(['id' => $invoice->cp_address_id]);
            $cpartyAddress = $invoiceAddress->getAddressInput('en');
            $cpartyCity = $invoiceAddress->getCityName('en', true);
            $cpartyCountry = $invoiceAddress->countryModel->alpha2_code;
            $cpartyPostcode = $invoiceAddress->index;
            $cpartyPhoneNum = $counterPartyInvoice->counterpartyPrimaryPhone->displayPhone;
            $cpartyEmail = $counterPartyInvoice->counterpartyPrimaryEmail->email;
        }
        $counterpartyData = [
            'cpartyCounterparty' => $cpartyCounterparty,
            'cpartyAddress' => $cpartyAddress,
            'cpartyCity' => $cpartyCity,
            'cpartyPostcode' => $cpartyPostcode,
            'cpartyPhoneNum' => $cpartyPhoneNum,
            'cpartyEmail' => $cpartyEmail,
            'cpartyCountry' => $cpartyCountry
        ];
        $counterpartyDataBool = ($cpartyCounterparty || $cpartyAddress || $cpartyCity ||
            $cpartyPostcode || $cpartyPhoneNum || $cpartyEmail
        );
        $senderAddress = '';
        $receiverAddress = '';
        if($ew->sender_cp_address) {
            $senderAddress = $ew->sender_cp_address->getAddressInput('en');
        }
        if($ew->receiver_cp_address) {
            $receiverAddress = $ew->receiver_cp_address->getAddressInput('en');
        }

        $data = [
            'invoiceNum' => $invoice->invoice_num,
            // Код Альфа-2 країни відправника
            'senderCountryAlpha2Code' => $ew->sender_cp_address->countryModel->alpha2_code,
            // ПІБ/ Назва відправника
            'senderCounterparty' => $ew->senderCounterparty->counterpartyName_en,
            // Адреса відправника
            'senderAddress' => $senderAddress,
            // Населений пункт відправника
            'senderCity' => $ew->sender_cp_address->getCityName('en', true),
            // Поштовий індекс відправника
            'senderPostCode' => $ew->sender_cp_address->index,
            //Телефон відправника
            'senderPhoneNum' => $ew->sender_cp_phonenum->displayPhone,
            // E-mail відправника
            'senderEmail' => $ew->sender_cp_email->email,
            // Код Альфа-2 країни отримувача
            'receiverCountryAlpha2Code' => $ew->receiver_cp_address->countryModel->alpha2_code,
            // ПІБ/ Назва отримувача
            'receiverCounterparty' => $ew->receiverCounterparty->counterpartyName_en,
            // Адреса отримувача
            'receiverAddress' => $receiverAddress,
            // Населений пункт отримувача
            'receiverCity' => $ew->receiver_cp_address->getCityName('en', true),
            // Поштовий індекс отримувача
            'receiverPostCode' => $ew->receiver_cp_address->index,
            // Телефон отримувача
            'receiverPhoneNum' => $ew->receiver_cp_phonenum->displayPhone,
            // E-mail отримувача
            'receiverEmail' => $ew->receiver_cp_email->email,
            // Данные о контрагенте (третья сторона)
            'counterpartyData' => $counterpartyData,
            'counterpartyDataBool' => $counterpartyDataBool,
            // Символ валюти вартості вкладення
            'currSymbol' => $invCur->symbol,
            // Загальна кількість шт.
            'ewPlacesCount' => /*$ew->ewPlacesCount*/$nOfPieces,
            // Загальна фактична вага
            'totalWeight' => Yii::$app->formatter->asDecimal($totalWeight, 2),
            // Загальна вартість митного декларування
            'zagVar' => Yii::$app->formatter->asDecimal($zagVar, 2),
            'hotecList' => $invoice->invoiceStHotecList,
            'positions' => $positions,
            'customsCommodityCodeInfo' => $customsCommodityCodeInfo,
            'materialInfo' => $materialInfo
        ];
        return $data;
    }

    /**
     * Получение данных для табличного отображения
     * @param $type
     * @param null $filters
     * @return string Данные в формате json
     */
    public function actionGetTable($filters = null) {

        $ewTable = ExpressWaybill::tableName();

        $filter = $this->getFiltersWhere((new ExpressWaybill), $filters);

        $query = (new Query())->select("$ewTable.id")
                ->distinct()
                ->from($ewTable)
                ->where($filter)
                ->orderBy($ewTable . '.date desc');

        if (!empty($filters) && is_array($filters)){
            foreach ($filters as $f) {
                if (!empty($f['join']))
                    foreach ($f['join'] as $join)
                        if (method_exists($query, $join['type']))
                            $query->$join['type']($join['from'], $join['on']);
                if (!empty($f['andWhere']))
                    $query->andWhere($f['andWhere']);
            }
        }
        
        $aquery = $this->getMoreAFilters($query);

//        die($aquery->createCommand()->rawSql);

        $result = ($aquery) ? $aquery->all() : [];
        $ids = ArrayHelper::map($result, 'id', 'id') + [-1];
        $where = "$ewTable.id in (" . implode(", ", $ids) . ")";
        return json_encode(ExpressWaybill::selectAsArray(null, $where));
    }

    /**
     * Получение данных для табличного отображения с расширенным фильтром
     * @return string Данные в формате json
     */
    public function actionGetATable(){

        return $this->actionGetTable((new ExpressWaybill())->afilters);

    }
    
    public function getMoreAFilters($query){
        
        $params = Yii::$app->getRequest()->get();
        foreach ($params as $k => $v) {
            $params[$k] = ($v == 'null') ? '' : $v;
        }
        $ewTable = ExpressWaybill::tableName();
        $mnTable = Manifest::tableName();
        $ewoTable = EwRelatedOrder::tableName();
        $cpTable = Counterparty::tableName();
        
        // фильтр даты
        if ($params['af_ew_entity_date_begin'] && $params['af_ew_entity_date_end']) {
            $date = " {date} >= '" . str_replace("'", '"', date("Y-m-d H:i:s", strtotime($params['af_ew_entity_date_begin']))) . "' "
                    . " AND {date} <= '" . str_replace("'", '"', date("Y-m-d H:i:s", strtotime($params['af_ew_entity_date_end']))) . "' ";
        }

// Сущности:
        
// Манифест
        if ($params['af_ew_entity'] == 3) {
            $mnNum = ($params['af_ew_entity_number']) ? [ 'mn.mn_num' => $params['af_ew_entity_number']] : [];
            //$mnState = ($params['af_entity_state']) ? 'mn.state in (' . $params['af_entity_state'] . ')' : [];
            $query
                    ->leftJoin('{{%mn_ew}} mnew', "mnew.ew_id = $ewTable.id")
                    ->leftJoin("$mnTable mn", "mn.id = mnew.mn_id")
                    ->andWhere($mnNum)
                    ->andWhere(str_replace('{date}', "$mnTable.date", $date));
                    //->andWhere($mnState);
        }

// Заказ
        else if ($params['af_ew_order_type']||$params['af_ew_order_carrier']) {
            // найти все ЕН по фильтру типа накладной
            $oNum = ($params['af_ew_entity_number']) ? [ 'ewo.wb_order_num' => $params['af_ew_entity_number']] : [];
            $oType = ($params['af_ew_order_type']) ? [ 'ewo.wb_order_type' => $params['af_ew_order_type']] : [];
            $oCarrier = ($params['af_ew_order_carrier']) ? [ 'ewo.carrier_id' => $params['af_ew_order_carrier']] : [];
            $query
                    ->leftJoin("$ewoTable ewo", "ewo.ew_id = $ewTable.id")
                    ->andWhere($oNum)
                    ->andWhere($oType)
                    ->andWhere($oCarrier)
                    ->andWhere(str_replace('{date}', "ewo.wb_order_date", $date));
        }

// ЕН
        else if ($params['af_ew_entity'] == 1) {
            // найти все ЕН по фильтру типа накладной
            $eNum = ($params['af_ew_entity_number']) ? ["$ewTable.ew_num" => $params['af_ew_entity_number']] : [];
            //$eState = ($params['af_entity_state']) ? "$ewTable.state in(". $params['af_entity_state'] . ')' : [];
            $query
                    ->andWhere($eNum)
                    ->andWhere(str_replace('{date}', "$ewTable.date", $date));
                    //->andWhere($eState);
        }

// Стоимость ТД от-до, валюта
        if (!empty($params['af_ew_cdcost_to']) && !empty($params['af_ew_cdcost_currency'])) {
            // взять все валюты
            $cdcost_currency = $params['af_ew_cdcost_currency'];
            $currencies = Currency::find()
                    ->where(['visible' => 1, 'state' => 1])
                    ->all();
            $cdcost_filter = " ( 0 OR ";
            // по циклу сформировать запросы whereOr
            // (cd_cost_from <= {value} AND cd_cost_to >= {value} AND cd_cost_currency = {id валюты})
            foreach ($currencies as $currency) {
                if ($rate = ExchangeRate::getExRate($cdcost_currency, $currency->id)) {
                    $cdcost_from = $params['af_ew_cdcost_from'] * $rate;
                    $cdcost_to = $params['af_ew_cdcost_to'] * $rate;
                    $cdcost_filter .= "($ewTable.customs_declaration_cost >= {$cdcost_from} AND $ewTable.customs_declaration_cost <= {$cdcost_to} AND $ewTable.customs_declaration_currency = {$currency->id}) OR ";
                }
            }
            $cdcost_filter .= " 0 ) ";
            $query
                    ->andWhere($cdcost_filter);
        }

//* Контрагент
        $cp_ = false;
        $cp_query = (new Query())->select("cp.id")
                ->distinct()
                ->from("$cpTable cp")
                ->leftJoin('{{%counterparty_manual_adress}} cma', 'cma.counterparty = cp.id'); // для адреса
        $cpids = [];
// Код контрагента
        if ($params['af_ew_counterparty_code']) {
            $cp_query->andWhere(["cp.counterparty_id" => $params['af_ew_counterparty_code']]);
            $cp_ = true;
        }
// Телефон
        if ($params['af_ew_counterparty_phone']) {
            $cp_query
                    //
                    ->leftJoin('{{%counterparty_contact_pers}} ccp', 'ccp.counterparty = cp.id')
                    ->leftJoin('{{%counterparty_contact_pers_phones}} ccpp', 'ccpp.counterparty_contact_pers = ccp.id and ccpp.primary = 1')
                    ->andWhere("CONCAT(COALESCE(operator_code,''), COALESCE(phone_number,'')) LIKE '%" . preg_replace("/[^0-9]+/", '%', $params['af_ew_counterparty_phone']) . "%'");
            $cp_ = true;
        }
// Вид лица
        if ($params['af_ew_person_type']) {
            $cp_query->andWhere(["cp.person_type" => $params['af_ew_person_type']]);
            $cp_ = true;
        }
// параметры адреса ЕН (НЕ контрагента а ЕН!!!)
// Не используется $if_if? Удалить.
//        $if_if = '';
//        if ($params['af_ew_counterparty_type'][0] == 1) {
//            $if_if = '(sender_cp_address_id = {{%counterparty_manual_adress}}.id)';
//        } elseif ($params['af_ew_counterparty_type'][0] == 2) {
//            $if_if = '(receiver_cp_address_id = {{%counterparty_manual_adress}}.id)';
//        } else {
//            $if_if = '(receiver_cp_address_id = {{%counterparty_manual_adress}}.id OR sender_cp_address_id = {{%counterparty_manual_adress}}.id)';
//        }
// Индекс
        if ($params['af_ew_counterparty_index']) {
//            $if_if .= ' AND  cma on cma.counterparty = cp.id';
            $cp_query
                    ->andWhere(['=', 'cma.index', $params['af_ew_counterparty_index']]);
            $cp_ = true;
            }
// Страна
        if ($params['af_ew_counterparty_country']) {
            $cp_query
                    ->andWhere(['=', 'cma.country_id', $params['af_ew_counterparty_country']]);
            $cp_ = true;
        }
// Город
        if ($params['af_ew_counterparty_city']) {
        $cp_query
                    ->andWhere(['=', 'cma.city_id', $params['af_ew_counterparty_city']]);
        $cp_ = true;
        }
        // die($cp_query->createCommand()->rawSql);
// Контрагент
        if ($params['af_ew_counterparty_counterparty']) {
            $cpids[] = $params['af_ew_counterparty_counterparty'];
            $cp_ = true;
        } else if($cp_) {
            $acpids = $cp_query->all();
            foreach ($acpids as $cp)
                if (!empty($cp))
                    $cpids[] = $cp['id'];
        }
        if (!empty($cpids))
            $query->andWhere($this->cpFilter($params, $cpids));
        else if($cp_) $query->andWhere('0');

        return $query;
    }
    
    private function cpFilter($params, $cpids) {
        
        $cpSenderFilter = $cpReceierFilter = '';
                
        // Заказзчик хочет чекбоксы... при варианте Все, Отправитель, Получатель.
        // т.е. если будет указан $params['af_ew_counterparty_type'][0], то будет только 2 варианта.
        if (!empty($cpids)&&$params['af_ew_counterparty_type'][0] != 3 ) {
            if ($params['af_ew_counterparty_type'][0] == 1 || empty($params['af_ew_counterparty_type'])) {
                $cpSenderFilter .= " {{%express_waybill}}.sender_counterparty_id in (0, "
                        . implode(', ', array_unique($cpids)) . ")";
            }
            if ($params['af_ew_counterparty_type'][0] == 2 || empty($params['af_ew_counterparty_type'])) {
                $cpReceierFilter .= " {{%express_waybill}}.receiver_counterparty_id in (0, "
                        . implode(', ', array_unique($cpids)) . ")";
            }
        }
        $filter = '';
        if ($cpSenderFilter && $cpReceierFilter)
            $filter .= "($cpSenderFilter OR $cpReceierFilter)";
        else if ($cpSenderFilter)
            $filter .= "$cpSenderFilter";
        else if ($cpReceierFilter)
            $filter .= "$cpReceierFilter";
//        die($filter);
        return $filter;
    }

    public function actionGetState() {

        $result = 0;

        $postParams = Yii::$app->getRequest()->post();
        $ew_num = $postParams['ew_num'];
        $ew = ExpressWaybill::findOne(['ew_num' => $ew_num]);
        if ($ew != null)
            $result = $ew->state;

        return $result;
    }

    public function actionGetNums() {

        $postParams = Yii::$app->getRequest()->post();
        $ew_num = json_decode($postParams['ew_num']);

        $where = $this->getCheckedWhere(new ExpressWaybill(),$ew_num);
        $ews = ExpressWaybill::selectAsArray(null,$where,['ew_num'],false);

        return json_encode($ews);
    }


    /**
     * Получение данных журнала событий
     * @return string Данные в формате json
     */
    public function actionGetLogs() {

        $getParams = Yii::$app->getRequest()->get();
        $f_ew_id = $getParams['f_ew_id'];

        if (!$f_ew_id)
            return;

        $result = array();
        $model = ExpressWaybill::findOne(['id' => $f_ew_id]);
        if ($model) {
            $logs = $model->logs;
            foreach ($logs as $log)
                $result[] = $log->toJson();
        }


        return json_encode($result);
    }

    /**
     * Получение данных дополнительных услуг
     * @return string Данные в формате json
     */
    public function actionGetAddService() {

        $getParams = Yii::$app->getRequest()->get();
        $f_ew_id = $getParams['f_ew_id'];

        if (!$f_ew_id)
            return;

        $result = array();
        $model = ExpressWaybill::findOne(['id' => $f_ew_id]);
        if ($model) {
            $ewAddServices = $model->ewAddServices;
            foreach ($ewAddServices as $ewAddService)
                $result[] = $ewAddService->toJson();
        }

        return json_encode($result);
    }

    /**
     * Получение данных  событий
     * @return string Данные в формате json
     */
    public function actionGetEvents() {

        $getParams = Yii::$app->getRequest()->get();
        $f_ew_id = $getParams['f_ew_id'];

        if (!$f_ew_id)
            return;

        $result = array();
        $model = ExpressWaybill::findOne(['id' => $f_ew_id]);
        if ($model) {
            foreach ($model->ewHistoryEvents as $event)
                $result[] = $event->toJson();
        }


        return json_encode($result);
    }

    public function actionCallEvent() {

        $postParams = Yii::$app->getRequest()->post();
        $entity_id = $postParams['entity_id'];
        $event = $postParams['event'];

        $result = Event::callEvent($event, $entity_id);

        if ($result !== true)
            $result = implode('.', $result);
        return $result;
    }

    public function actionValidateBarcode() {

        $postParams = Yii::$app->getRequest()->post();
        $bc_code = $postParams['bc_code'];


        $cnt = EwPlace::find()->where(['place_bc' => $bc_code])->count();

        return $cnt ? Yii::t('ew', "Attention! Barcode '$bc_code' already exists") : '';
    }
    
    public function actionGetEwTypeList() {
        $request = Yii::$app->getRequest()->get();
        $field = "name_" . $request['lang'];
        $models = \app\models\ew\EwType::find()->all();
        $result = [null => ''] + \yii\helpers\ArrayHelper::map($models, 'id', $field);
        foreach ($result as $key => $val)
                $result_id_txt[] = ['id' => $key, 'txt' => $val];
        return json_encode($result_id_txt);
    } 
        
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        return json_encode(ExpressWaybill::getList('name', true, $lang, '1=1'));
    }

    public function actionGetRelatedEntities($id) {

        $model = $this->findModel($id);
        if ($model)
            return $model->relatedEntities;
    }

    /**
     * Получение данных  событий
     * @return string Данные в формате json
     */
    public function actionGetHistoryStatuses($id) {

        $result = array();
        $model = $this->findModel($id);
        if ($model) {
             foreach ($model->ewHistoryStatuses as $status) {
                $result[] = $status->toJson();
            }
        }

        return json_encode($result);
    }

    /**
     * Пересчитывает значение поля cargo_est_weight_kg для тех ЭН, у которых это поле null, или 0.
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionCalculateEwWeight() {

        if (Yii::$app->user->identity->user_id != 1)
            return 'Current user are not a ROOT!';

        $ews = ExpressWaybill::find()->where('cargo_est_weight_kg is null OR cargo_est_weight_kg = 0')->all();

        foreach ($ews as $ew) {
            $recalc = false;
            if (!isset($ew->total_dimensional_weight_kg)) {
                $ew->total_dimensional_weight_kg = $ew->itogiArray['general_volume_weight'];
                $command = Yii::$app->db->createCommand();
                $command->update(ExpressWaybill::tableName(), ['total_dimensional_weight_kg' => $ew->total_dimensional_weight_kg],
                    ['id' => $ew->id])->execute();
                $recalc = true;
            }
            if (!isset($ew->total_actual_weight_kg)) {
                $ew->total_actual_weight_kg = $ew->itogiArray['general_weight'];
                $command = Yii::$app->db->createCommand();
                $command->update(ExpressWaybill::tableName(), ['total_actual_weight_kg' => $ew->total_actual_weight_kg],
                    ['id' => $ew->id])->execute();
                $recalc = true;
            }

            if ($recalc)
                $ew->calcCargo_est_weight_kg();

            $command = Yii::$app->db->createCommand();
            $command->update(ExpressWaybill::tableName(), ['cargo_est_weight_kg' => $ew->cargo_est_weight_kg], ['id' => $ew->id])->execute();
        }

        return 'Saved ' . count($ews) . ' EWs';
    }

    /**
     * Отображение формы массовой обработки
     * @return string
     */
    public function actionProcessing(){

        return $this->render('processing', ['model' => new ExpressWaybill()]);
    }

    /**
     * Массовая обработка
     * @return string
     */
    public function actionRunProcessing(){

        $postParams = Yii::$app->request->post();
        $ews_input = $postParams['ews'];
        $operation_input = $postParams['mode'];
        $params_input = $postParams['params'];


        $ews = ExpressWaybill::find()
            ->where("ew_num in ('".str_replace(",", "','", $ews_input)."')")
            ->all();

        $success_count = 0;
        $problem_ews = [];
        foreach ($ews as $ew){

            //$ew = new ExpressWaybill();
            $ew->operation = $operation_input;

            if ($ew->operation == CommonModel::OPERATION_CHANGE_STATUS){

                $status = new EwHistoryStatuses();
                $status->ew_id = $ew->id;
                $status->status_ew_id = $params_input['status_id'];
                $status->_date = $params_input['status_date'];
                $status->comment = $params_input['status_comment'];
                $status->creator_user_id = User::findOne(['employee_id'=>$params_input['status_employee']])->getIdentity();
                $status->status_country = $params_input['status_country'];//$status->creatorUser->employee->country_id; // значение по умолчанию

                if ($ew->state == CommonModel::STATE_CREATED &&
                    $status->status_country &&
                    $status->save()){

                    $success_count++;
                }
                else{

                    $problem_ews[]=$ew;
                }
                continue;
            }
            else if ($ew->operation == CommonModel::OPERATION_CLOSE){

                $ew->_closing_date = $params_input['close_date'];
                $ew->closing_sending_receiver = $params_input['close_receiver'];
                $ew->closing_receiver_post_id = $params_input['close_receiver_post'];
                $ew->closing_receiver_doc_type = $params_input['close_receiver_doc_type'];
                $ew->_closing_doc_issue_date = $params_input['close_doc_date'];
                $ew->closing_receiver_doc_serial_num = $params_input['close_doc_serial'];
                $ew->closing_doc_num = $params_input['close_doc_num'];
                $ew->closing_issued_shipment = $params_input['close_employee'];
            }
            else if ($ew->operation == CommonModel::OPERATION_DELETE){
                SysEntity::saveOperation($ew->getEntityCode(), $ew->id, CommonModel::OPERATION_BEGIN_DELETE);
            }
            else if ($ew->operation == CommonModel::OPERATION_CLOSE){
                SysEntity::saveOperation($ew->getEntityCode(), $ew->id, CommonModel::OPERATION_BEGIN_CLOSE);
            }
            else if ($ew->operation == CommonModel::OPERATION_CANCEL){
                SysEntity::saveOperation($ew->getEntityCode(), $ew->id, CommonModel::OPERATION_BEGIN_CANCEL);
            }

            if ($ew->save(false)){
                $success_count++;
            }
            else{
                $problem_ews[]=$ew;
            }
        }

        $result =  Yii::t('processing', 'Processed') . ": $success_count";

        if (sizeof($problem_ews)) {

            $result .= "<br>" . Yii::t('processing','There are errors while processing EWs. Further processing not possible: ')."<br>";
            $ew_for_br = 5;
            $curr_ew = 1;
            foreach ( array_reverse($problem_ews) as $problem_ew) {

                $result .=
                    BtnCreateTab::createLink(
                        $problem_ew->ew_num,
                        Yii::t('tab_title', 'EW_short_name')." {$problem_ew->ew_num} ".Yii::t('tab_title', 'view_command'),
                        Url::to(['ew/express-waybill/view', 'id'=> $problem_ew->id]),
                        '', "ew{$problem_ew->id}_")
                    . ","
                    . ( ($curr_ew == sizeof($problem_ews) || $curr_ew++ % $ew_for_br) ? '' : '<br>' );
            }
            $result = rtrim($result,",");
        }


        return json_encode($result);
    }

    private function getEwsFromChecks(){

        $params = Yii::$app->getRequest()->get();
        if ($params['lr_ew_arr']) {
            $where = $this->getCheckedWhere(new ExpressWaybill(), 'lr_ew_arr', ReportLogisticController::reportLogistic_filters());
            $ids = ReportLogisticController::GetEwIds($where);
            $where =  ExpressWaybill::tableName().".id in (" . implode(", ", $ids) . ")";
        }
        else
            $where = $this->getCheckedWhere(new ExpressWaybill(),'ews');
        $ews = ExpressWaybill::selectAsArray(null,$where,null,false);

        return $ews;
    }
}

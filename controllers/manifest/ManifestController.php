<?php
/**
 * В файле описан класс контроллера манифеста
 *
 * @author Мельник И.А.
 * @category Манифест
 */

namespace app\controllers\manifest;

use app\controllers\CommonController;
use app\controllers\reports\ReportLogisticController;
use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use app\models\dictionaries\address\ListCity;
use app\models\counterparty\Counterparty;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\currency\Currency;
use app\models\dictionaries\employee\Employee;
use app\models\dictionaries\events\Event;
use app\models\ew\EwRelatedOrder;
use app\models\ew\ExpressWaybill;
use app\models\dictionaries\warehouse\ListWarehouse;
use app\models\manifest\Manifest;

/**
 * Контроллер манифеста
 */
class ManifestController extends CommonController
{
    public function init(){

        $this->accessCheck = 'manifest'; // имя сущности для проверки
        parent::init();
    }



    /**
     * Получение списка манифестов
     * @return mixed Страница со списком манифестов
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new Manifest(),]);
    }

    public function actionAIndex()
    {
        return $this->render('agrid', ['model' => new Manifest(),]);
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

        $manifest = Manifest::findOne($id);
        if ($manifest === null) {
            return Json::encode($result);
        }

        foreach($manifest->getAttachedDocs()->all() as $attachedDoc) {
            $result[] = $attachedDoc->toJson();
        }

        return Json::encode($result);
    }


    /**
     * Создание нового манифеста
     * @return mixed Страница создания манифеста, если создание было успешным - страница редактирования
     */
    public function actionCreate()
    {
        $model = new Manifest();
        $ew_arr = null;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $params = Yii::$app->getRequest()->get();
            if ($params['lr_ew_arr']) {
                $where = $this->getCheckedWhere(new ExpressWaybill(), 'lr_ew_arr', ReportLogisticController::reportLogistic_filters());
                $ids = ReportLogisticController::GetEwIds($where);
                $where ="id in (" . implode(", ", $ids) . ")";
                $models = ExpressWaybill::find()->where($where)->all();
                $ew_arr = ArrayHelper::getColumn($models, 'ew_num');
            }
            $model->generateDefaults(Yii::$app->getRequest()->get(), $ew_arr);
            return $this->render('form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Удаление манифеста
     * @param string $id Идентификатор манифеста
     * @return mixed Страница со списком манифестов
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['update', 'id' => $id]);
    }*/

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id идентификатор манифеста
     * @return Manifest загруженная модель манифеста
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = Manifest::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Получение данных для табличного отображения
     * @param null $filters
     * @return string Данные в формате json
     */
    public function actionGetTable($filters=null){

        $model = new Manifest();
        $where = $this->getFiltersWhere($model, $filters?:$model->filters);

        return json_encode(Manifest::selectAsArray(null, $where));
    }

    /**
     * Метод для расширенного поиска
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetATable() {

        $model = new Manifest();
        $filters = $model->getAFilters(false);
        $getParams = Yii::$app->request->get();


        // обработка разных видов сущности. в зависимости от выбранного типа сущности - формируем разные наборы фильтров
        $mnTable = 'mainmanifest';

        // тип сущности ЭН. меняем поля фильтров под накладную
        if ($getParams['af_ew_entity'] == ExpressWaybill::ENTITY_TYPE){


            $ewTable = ExpressWaybill::tableName();
            $ewOrderTable = EwRelatedOrder::tableName();
            $mnEwTable = "{{%mn_ew}}";

            // поиск по номеру ЭН
            $filters[] = [ 'id'=>'af_ew_entity_num','operation'=>'exists',
               'field'=> "select id from $ewTable where id in (select ew_id from $mnEwTable where mn_id = $mnTable.id) and ew_num = '~exists_input_val~' "
            ];

            // поиск по дате ЭН
            $filters[] = [ 'id'=>'af_ew_entity_date_begin', 'operation'=>'exists','type'=> CommonModel::FILTER_DATETIME,
               'field'=> "select id from $ewTable where id in (select ew_id from $mnEwTable where mn_id = $mnTable.id) and date >= ~exists_input_val~ "
            ];

            // поиск по дате ЭН
            $filters[] = [ 'id'=>'af_ew_entity_date_end','operation'=>'exists','type'=> CommonModel::FILTER_DATETIME,
               'field'=> "select id from $ewTable where id in (select ew_id from $mnEwTable where mn_id = $mnTable.id) and date <= ~exists_input_val~ "
            ];

            // поиск по типу заказа в ЭН
            $filters[] = [ 'id'=>'af_ew_order_type', 'operation'=>'exists',
               'field'=> "select $ewTable.id from $ewTable, $ewOrderTable where $ewTable.id in (select ew_id from $mnEwTable where mn_id = $mnTable.id) ".
                         "and $ewOrderTable.ew_id = $ewTable.id and $ewOrderTable.wb_order_type = ~exists_input_val~ "
            ];

            // поиск по перевозчику
            $filters[] = [ 'id'=>'af_ew_order_carrier', 'operation'=>'exists',
                'field'=> "select $ewTable.id from $ewTable, $ewOrderTable where $ewTable.id in (select ew_id from $mnEwTable where mn_id = $mnTable.id) ".
                    "and $ewOrderTable.ew_id = $ewTable.id and $ewOrderTable.carrier_id = ~exists_input_val~ "
            ];

        }
        // тип сущности МН. меняем поля фильтров под манифест
        elseif ($getParams['af_ew_entity'] == Manifest::ENTITY_TYPE){

            $filters[] = [ 'id'=>'af_ew_entity_num', 'operation'=>'=', 'field'=> "$mnTable.mn_num"];
            $filters[] = ['id'=>'af_ew_entity_date_begin', 'operation'=>'>=','field'=> "$mnTable.date",'type'=> CommonModel::FILTER_DATETIME,];
            $filters[] = ['id'=>'af_ew_entity_date_end','operation'=>'<=','field'=> "$mnTable.date",'type'=> CommonModel::FILTER_DATETIME,];
        }


        return $this->actionGetTable($filters);
    }

    /**
     * Метод для расширенного поиска deprecated
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetATable_deprecated() {

        $params = Yii::$app->getRequest()->get();

        $paramsCreation = $params['af_ew_creation_country'] || $params['af_ew_creation_city'] ||
            $params['af_ew_creation_department'] || $params['af_ew_creation_user'] || $params['af_ew_date_begin'] || $params['af_ew_date_end'];

        $lang = $params['af_ew_lang'];
        $langUp = ucfirst($lang);

        $model = new Manifest();
        $result = array();
        $models = Manifest::find()
            ->where($this->getFiltersWhere($model, $model->aFilters))
            ->orderBy(['`date`'=> SORT_DESC])
            ->all();

        foreach($models as $manifest) {
            // фильтр по номеру выбраной сущьности
            switch($params['af_ew_entity']) {
                case ExpressWaybill::ENTITY_TYPE:
                    // ********** Фильтруем по номеру ЕН *********
                    $ews = $manifest->ews;
                    $countEws = count($ews);
                    $i = 1;
                    // Если у манифеста нет ЕН то не включаем его в результат
                    if(!$ews) {continue 2;}
                    foreach ($ews as $ew) {
                        // Если нету типа сущности, то ищем по номеру ЕН
                        if(!$params['af_ew_order_type']) {
                            // После выбора сущности заполнять можно или номер или дату
                            // Если есть номер сущности
                            if ($params['af_ew_entity_num']) {
                                if ($ew->ew_num != $params['af_ew_entity_num']) {
                                    // Если конец цикла
                                    if ($i == $countEws) {
                                        // След манифест
                                        continue 3;
                                    } // След ЕН
                                    else {
                                        $i++;
                                        continue;
                                    }
                                }
                            }
                        }// Если есть тип сущности, то ищем по номеру заказа
                        else {
                            $orders = $ew->ewRelatedOrders;
                            $countOrders = count($orders);
                            $k = 1;
                            // Если нету заказов в ЕН
                            if(!$orders) {
                                // и если конец цикла ЕН
                                if($i == $countEws) {
                                    // то след манифест
                                    continue 3;
                                }
                                // иначе след ЕН
                                else {
                                    $i++;
                                    continue ;
                                }
                            }
                            foreach($orders as $order) {
                                // Поиск по типу заказа
                                if($order->wb_order_type != $params['af_ew_order_type']) {
                                    if($k == $countOrders) {
                                        if($i == $countEws) {
                                            // След манифест
                                            continue 4;
                                        }
                                        else {
                                            // След ЕН
                                            $i++;
                                            continue 2;
                                        }
                                    }
                                    else {
                                        // След заказ
                                        $k++;
                                        continue;
                                    }
                                }
                                // Поиск по номеру заказа
                                if($order->wb_order_num != $params['af_ew_entity_num']) {
                                    if($k == $countOrders) {
                                        if($i == $countEws) {
                                            // След манифест
                                            continue 4;
                                        }
                                        else {
                                            // След ЕН
                                            $i++;
                                            continue 2;
                                        }
                                    }
                                    else {
                                        // След заказ
                                        $k++;
                                        continue;
                                    }
                                }

                            }
                        }

                        // Если есть даты
                        if($params['af_ew_entity_date_begin'] || $params['af_ew_entity_date_end']) {
                            // и дата ЕН в манифесте не попадает в етот диапазон то следующая ЕН
                            if(strtotime($ew->date) < strtotime($params['af_ew_entity_date_begin']) || strtotime($ew->date) > strtotime($params['af_ew_entity_date_end'])) {
                                // Если конец цикла
                                if($i == $countEws) {
                                    // След манифест
                                    continue 3;
                                }
                                // След ЕН
                                else {
                                    $i++;
                                    continue;
                                }
                            }
                        }
                        // Фильтр по заказам
                        // Если указан тип
                        if($params['af_ew_order_type']) {
/*                            $orders = $ew->ewRelatedOrders;
                            $countOrders = count($orders);
                            $k = 1;
                            // Если нету заказов в ЕН
                            if(!$orders) {
                                // и если конец цикла ЕН
                                if($i == $countEws) {
                                    // то след манифест
                                    continue 3;
                                }
                                // иначе след ЕН
                                else {
                                    $i++;
                                    continue ;
                                }
                            }
                            foreach($orders as $order) {
                                if($order->wb_order_type != $params['af_ew_order_type']) {
                                    if($k == $countOrders) {
                                        if($i == $countEws) {
                                            // След манифест
                                            continue 4;
                                        }
                                        else {
                                            // След ЕН
                                            $i++;
                                            continue 2;
                                        }
                                    }
                                    else {
                                        // След заказ
                                        $k++;
                                        continue;
                                    }
                                }

                            }*/
                        }
                        // Выход из свича
                        continue 2;
                    }
                    // ************************************
                    break;
                case Manifest::ENTITY_TYPE:
                    // После выбора сущности заполнять можно или номер или дату
                    // Если есть номер сущности
                    if($params['af_ew_entity_num']) {
                        // Если равен то добавление в результат
                        if ($manifest->mn_num == $params['af_ew_entity_num']) {
                            continue;
                        }
                        // Иначе следующая итерация
                        else {
                            continue 2;
                        }
                    }
                    if($params['af_ew_entity_date_begin'] || $params['af_ew_entity_date_end']) {
                        if(strtotime($manifest->date) > strtotime($params['af_ew_entity_date_begin']) && strtotime($manifest->date) < strtotime($params['af_ew_entity_date_end'])) {
                            continue;
                        }
                        else {
                            continue 2;
                        }
                    }
                    break;
                case EwRelatedOrder::ENTITY_TYPE:
                    //
                    // ********** Фильтруем по номеру ЕН *********
                    $ews = $manifest->ews;
                    $countEws = count($ews);
                    $i = 1;
                    // Если у манифеста нет ЕН то не включаем его в результат
                    if(!$ews) {continue 2;}
                    foreach ($ews as $ew) {
                        $orders = $ew->ewRelatedOrders;
                        $countOrders = count($orders);
                        $k = 1;
                        if(!$orders) {
                            // и если конец цикла ЕН
                            if($i == $countEws) {
                                // то след манифест
                                continue 3;
                            }
                            // иначе след ЕН
                            else {
                                $i++;
                                continue ;
                            }
                        }
                        foreach($orders as $order) {
                            if($params['af_ew_order_type']) {
                                if($order->wb_order_type != $params['af_ew_order_type']) {
                                    // и если конец цикла ЕН
                                    if($i == $countEws) {
                                        if($k == $countOrders) {
                                            // то след манифест
                                            continue 4;
                                        }
                                        else {
                                            $k++;
                                            continue;
                                        }
                                    }
                                    // иначе след
                                    else {
                                        $k++;
                                        continue ;
                                    }
                                }
                            }

                            if($params['af_ew_entity_num']) {
                                if($order->wb_order_num != $params['af_ew_entity_num']) {
                                    // и если конец цикла ЕН
                                    if($i == $countEws) {
                                        if($k == $countOrders) {
                                            // то след манифест
                                            continue 4;
                                        }
                                        else {
                                            $k++;
                                            continue;
                                        }
                                    }
                                    // иначе след
                                    else {
                                        $k++;
                                        continue ;
                                    }
                                }
                            }

                            // Если есть даты
                            if($params['af_ew_entity_date_begin'] || $params['af_ew_entity_date_end']) {
                                if(strtotime($order->wb_order_date) < strtotime($params['af_ew_entity_date_begin']) || strtotime($order->wb_order_date) > strtotime($params['af_ew_entity_date_end'])) {
                                    // и если конец цикла ЕН
                                    if($i == $countEws) {
                                        if($k == $countOrders) {
                                            // то след манифест
                                            continue 4;
                                        }
                                        else {
                                            $k++;
                                            continue;
                                        }
                                    }
                                    // иначе след
                                    else {
                                        $k++;
                                        continue ;
                                    }
                                }
                            }

                            continue 3;
                        }
                        $i++;
                    }
                    break;

                default:
                    // Когда не выбрано никакой сущности

                    break;
            }
            // Если нет данных с блока *Праметры создания* фильтра то не тащим с манифеста параметры создания
            if($paramsCreation) {
                $serviceData = $manifest->serviceData;
                // Даты создания
                if($params['af_ew_date_begin'] || $params['af_ew_date_end']) {
                    if(strtotime($serviceData['create_date']) < strtotime($params['af_ew_date_begin']) || strtotime($serviceData['create_date']) > strtotime($params['af_ew_date_end']) ) {
                        continue;
                    }
                }
                //Страна создания
                if($params['af_ew_creation_country']) {
                    $country = Country::findOne(['id' => $params['af_ew_creation_country']]);
                    if($serviceData['create_country'] !== $country->{"nameOfficial$langUp"}) {
                        continue;
                    }
                }
                // Город создания
                if($params['af_ew_creation_city']) {
                    $city = ListCity::findOne(['id' => $params['af_ew_creation_city']]);
                    if($serviceData['create_city'] !== $city->{"name_$lang"}) {
                        continue;
                    }
                }
                // Склад создания
                if($params['af_ew_creation_department']) {
                    $department = ListWarehouse::findOne(['id' => $params['af_ew_creation_department']]);
                    if($serviceData['create_departament'] !== $department->{"name_$lang"}) {
                        continue;
                    }
                }
                // Юзер создания
                if($params['af_ew_creation_user']) {
                    $employee = Employee::findOne(['id' => $params['af_ew_creation_user']]);
                    if($serviceData['create_surname'] !== $employee->{"surnameFull$langUp"}) {
                        continue;
                    }
                }
            }

            $result[] = $manifest->toJson();
        }

        return json_encode(CommonModel::getDataWithLimits($result));
    }

    public function actionGetListCarrier() {
        $lang = Yii::$app->request->get()['lang'];
        return $this->getFormatSelect2(Counterparty::getList('counterpartyName_'.$lang, true, null, 1000));
    }

    public function actionGetListCity() {
        $lang = Yii::$app->request->get()['lang'];
        return $this->getFormatSelect2(ListCity::getList('name', true, $lang));
    }

    public function getFormatSelect2(array $list) {
        $listJson = [];
        foreach($list as $key => $val) {
            $listJson[] = ['id' => $key, 'txt' => $val];
        }
        return json_encode($listJson);
    }



    /**
     * Формирование данных для форм печати МН
     * @param $id код МН
     * @return array массив с данными
     */
    private function printversionmanifest_getdata($id)
    {

        $manifest=$model = $this->findModel($id);

        $itogi_for_relation_ews['total_amount']=0;
        $itogi_for_relation_ews['total_weight']=0;

        //получаем список всех ЭН в этом манифесте
        $ewss=$manifest->ews;

        $total_shipment_weight = 0;

        //проходим по всем и формируем массив с данными
        foreach ($ewss as $ews)
        {
            $sender_address = $ews->sender_cp_address;
            //формируем строку адресс отправителя
            $sender_info=array();
            $sender_info[]=$ews->senderCounterparty->counterpartyName_en;
            $sender_info[]=$ews->sender_cp_phonenum->displayPhone;
            $sender_info[]=$ews->sender_cp_email->email;
            $sender_info[]=$sender_address ? $sender_address->getAddressName('en') : '';
            $sender_info[]=$sender_address ? $sender_address->countryModel->alpha2_code : '';
            $sender_info[]=$sender_address ? $sender_address->countryModel->nameShortEn : '';
            $sender_info[]=$sender_address ? $sender_address->getRegionName('en'): '';
            $sender_info[]=$sender_address ? $sender_address->getCityName('en',true) : '';
            $sender_info[]=$sender_address ? $sender_address->index : '';
            $sender_info_str='';
            foreach ($sender_info as $k=>$v) {if ($v!=null&&trim($v)!='') {$sender_info_str.=$v.', ';}}
            $sender_info_str=substr($sender_info_str,0,-2);


            $receiver_address = $ews->receiver_cp_address;
            //формируем строку адресс получателя
            $receiver_info=array();
            $receiver_info[]=$ews->receiverCounterparty->counterpartyName_en;
            $receiver_info[]=$ews->receiver_cp_phonenum->displayPhone;
            $receiver_info[]=$ews->receiver_cp_email->email;
            $receiver_info[]=$receiver_address ? $receiver_address->getAddressName('en') : '';
            $receiver_info[]=$receiver_address ? $receiver_address->countryModel->alpha2_code:'';
            $receiver_info[]=$receiver_address ? $receiver_address->countryModel->nameShortEn:'';
            $receiver_info[]=$receiver_address ? $receiver_address->getRegionName('en'): '';
            $receiver_info[]=$receiver_address ? $receiver_address->getCityName('en',true) : '';
            $receiver_info[]=$receiver_address ? $receiver_address->index : '';
            $receiver_info_str='';
            foreach ($receiver_info as $k=>$v) {if ($v!=null&&trim($v)!='') {$receiver_info_str.=$v.', ';}}
            $receiver_info_str=substr($receiver_info_str,0,-2);

            $ews_itogi=$ews->getItogiArray();
            //$total_shipment_weight += $ews_itogi['itog_weight'];
            $total_shipment_weight += $ews->total_actual_weight_kg;
            //формируем массив данных по ЭН
            $ews_data[]=array(
                'f1_9'=>$ews->ew_num,
                'f1_10'=>$ews_itogi['ews_place_count'],
                //'f1_11'=>$ews_itogi['itog_weight'],
                'f1_11'=>$ews->total_actual_weight_kg,
                'f1_12'=>$ews->general_desc,
                'f1_13'=>$ews->customs_declaration_cost,
                'f1_14'=>Currency::findOne(['id' => $ews->customs_declaration_currency])->nameShort,
                'f1_15'=>$sender_info_str,
                'f1_16'=>$manifest->dep_station_abbr,
                'f1_17'=>$receiver_info_str,
                'f1_18'=>$manifest->des_station_abbr,
                'f1_19'=>$ews->closing_add_shipment_info,
            );

            $itogi_for_relation_ews['total_amount']=+$ews_itogi['ews_place_count'];
            $itogi_for_relation_ews['total_weight']=+$ews_itogi['itog_weight'];
        }



        $data=array(
            'f1'=> $manifest->carriersModel ? $manifest->carriersModel->counterpartyName_en : '',
            'f2'=>$manifest->_date,
            'f3'=>$manifest->depPointModel ? $manifest->depPointModel->name_en : '',
            'f4'=>$manifest->mn_num,
            'f5'=>count($ews_data),
            'f6'=>$itogi_for_relation_ews['total_amount'],
            'f7'=>round($itogi_for_relation_ews['total_weight'],2),
            'ews_data'=>$ews_data,
            'total_pieces_amount' => $manifest->total_pieces_amount,
            'total_weight_with_pkg_auto' => $manifest->total_weight_with_pkg_auto,
            'total_amount_of_pieces' => $manifest->total_amount_of_pieces,
            'total_mn_weight_kg' => $manifest->total_mn_weight_kg,
            'utd_num' => $manifest->utd_num,
            'dep_point' => $manifest->depPointModel ? $manifest->depPointModel->getName('en',true) : '',
            'total_shipment_weight' => $total_shipment_weight,
        );


        return $data;

    }




    /**
     * Вывод печатной формы инвойса.
     * @param string $id код ЭН
     * @return mixed
     */
    public function actionManifestprintform($id)
    {

        Event::callEvent(Event::MN_PRINT, $id);
        $manifest = Manifest::findOne(['id'=>$id]);
        foreach ($manifest->ews as $ew)
            Event::callEvent(Event::EW_PRINT_MN, $ew->id);

        $data=$this->printversionmanifest_getdata($id);

        $this->layout = 'for_print';
        $this->view->params['cssfiles'] = array('/css/printforms/manifestprintform.css', '/css/print_break.css');

        return $this->render('printversionmanifest', ['data' => $data]);

    }


    /**
     * Вывод печатной формы МН в формат xls
     * @param $id код МН
     * @return string
     */
    public function actionManifestxls($id)
    {
        Event::callEvent(Event::MN_EXPORT_EXCEL, $id);

        $data=$this->printversionmanifest_getdata($id);
        return $this->render('manifestxls', ['data' => $data]);
    }


    /**
     * Получение данных журнала событий
     * @return string Данные в формате json
     */
    public function actionGetLogs(){

        $getParams = Yii::$app->getRequest()->get();
        $f_manifest_id=$getParams['f_manifest_id'];

        if (!$f_manifest_id)
            return;

        $result = array();
        $model = Manifest::findOne(['id'=>$f_manifest_id]);
        if ($model){
            $logs = $model->logs;
            foreach($logs as $log)
                $result[] = $log->toJson();
        }


        return json_encode($result);
    }

    /**
     * Получение данных  событий
     * @return string Данные в формате json
     */
    public function actionGetEvents(){

        $getParams = Yii::$app->getRequest()->get();
        $f_mn_id=$getParams['f_mn_id'];

        if (!$f_mn_id)
            return;

        $result = array();
        $model = Manifest::findOne(['id'=>$f_mn_id]);
        if ($model){
            foreach($model->mnHistoryEvents as $event)
                $result[] = $event->toJson();
        }


        return json_encode($result);
    }


    public function actionCallEvent(){

        $postParams = Yii::$app->getRequest()->post();
        $entity_id = $postParams['entity_id'];
        $event = $postParams['event'];

        $result = Event::callEvent($event,$entity_id);

        if ($result !== true)
            $result = implode('.',$result);
        return $result;
    }

    public function actionGetRelatedEntities($id) {

        $model = $this->findModel($id);
        if ($model)
            return $model->relatedEntities;
    }
}

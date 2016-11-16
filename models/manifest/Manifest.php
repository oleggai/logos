<?php
/**
 * В файле описан класс модели для манифеста
 *
 * @author Мельник И.А.
 * @category Манифест
 */

namespace app\models\manifest;

use app\classes\DocumentStorage;
use app\models\attached_doc\AttachedDoc;
use app\models\attached_doc\MnAttachedDoc;
use app\models\counterparty\CounterpartyLegalEntity;
use app\models\counterparty\CounterpartyPrivatPers;
use app\models\counterparty\CounterpartySign;
use app\models\counterparty\ListCounterpartySign;
use app\models\dictionaries\access\User;
use app\models\dictionaries\carrier\ListCarrier;
use app\models\ew\EwPlace;
use Yii;
use yii\db\Query;
use yii\helpers\Url;
use app\models\dictionaries\address\ListCity;
use app\models\common\CommonModel;
use app\models\counterparty\Counterparty;
use app\models\dictionaries\country\Country;
use app\models\common\DateFormatBehavior;
use app\models\dictionaries\employee\Employee;
use app\models\dictionaries\events\Event;
use app\models\dictionaries\events\MnHistoryEvents;
use app\models\ew\ExpressWaybill;
use app\models\ew\ListEntityType;
use app\models\ew\WbOrderType;
use app\models\dictionaries\exchangerate\ExchangeRate;
use app\models\common\Langs;
use app\models\common\Setup;
use app\models\common\ShortDateFormatBehavior;
use app\models\dictionaries\warehouse\ListWarehouse;

/**
 * Модель манифеста
 *
 * @property string $id Уникальный идентификатор манифеста
 * @property string $mn_num Номер МН
 * @property integer $state Состояние МН
 * @property string $date Дата МН
 * @property integer $mn_type Вид МН
 * @property integer $mn_kind Вид МН
 * @property string $dep_station Станция отправления
 * @property string $dep_station_abbr Станция отправления
 * @property string $dep_point Пункт отправления
 * @property string $des_station Станция назначения
 * @property string $des_station_abbr Станция назначения
 * @property string $carriers_name Название перевозчика
 * @property string $utd_num Номер ЕТД
 * @property string $utd_code Код ЕТД
 * @property string $utd_date Дата ЕТД
 * @property string $arrival_date Дата прибытия
 * @property string $total_pieces_amount Количество консолидированых мест
 * @property string $total_weight_with_pkg Общий вес с упаковкой
 * @property string $chargeable_weight Оплачиваемый вес, кг
 * @property string $dep_point_id Пункт отправления. Ссылка на нас.пункт
 * @property string $carriers_id Название перевозчика. Ссылка на контрагента
 *
 * автоматически расчитанные поля
 * @property string $total_weight_with_pkg_auto Общий вес с упаковкой (автоматически расчитанно)
 * @property string $chargeable_weight_auto Общий вес с упаковкой (автоматически расчитанно)
 * @property string total_ew_amount Кол-во накладных (автоматически расчитанно)
 * @property string total_amount_of_pieces Кол-во мест (автоматически расчитанно)
 * @property string total_mn_weight_kg Общий вес (автоматически расчитанно)
 * @property string total_pay_cost_euro Общая стоимость в евро (автоматически расчитанно)
 * ~автоматически расчитанные поля
 *
 * @property ExpressWaybill[] $ews Связанные накладные
 * @property LogManifest[] $logs Логи операций над манифестом
 * @property LogManifest logCreation Запись лога о создании манифеста
 * @property array serviceData Служебная информация
 * @property mixed _arrival_date Дата в нужном формате
 * @property mixed _date Дата в нужном формате
 * @property mixed _utd_date Дата в нужном формате
 * @property mixed ewsArray Привязанные накладные в виде массива
 * @property mixed ewsString Строка привязанных накладных (запятая как разделитель)
 * @property mixed logLastUpdate Запись лога о последнем редактировании
 * @property mixed stateList Список состояний
 * @property MnHistoryEvents[] $mnHistoryEvents
 * @property ListCity $depPointModel Модель пункта отправления
 * @property Counterparty $carriersModel Модель перевозчика
 * @property mixed dep_point_name Пункт отправления на основании нового и старого полей (если выбрано новое - используется новое, иначе старое)
 * @property mixed carriers_name_new Название перевозчика на основании нового и старого полей (если выбрано новое - используется новое, иначе старое)
 */
class Manifest extends CommonModel
{
    /**
     * Тип манифеста "Импорт"
     */
    const TYPE_IMPORT = 1;
    /**
     * Тип манифеста "Экспорт (Документы)"
     */
    const TYPE_EXPORT_DOC = 2;
    /**
     * Тип манифеста "Экспорт (Груз)"
     */
    const TYPE_EXPORT_CARGO = 3;

    private $ewsInput;
    private $saveEvent;

    const ENTITY_TYPE = 3;

    const ENTITY_NAME = 'MN';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%manifest}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['mn_num', 'state', '_date','mn_type', 'mn_kind'], 'required'],
            [['state', 'mn_type', 'total_pieces_amount', 'dep_point_id','carriers_id', 'mn_kind'], 'integer'],
            [['_date', '_utd_date', '_arrival_date', ], 'string'],
            [['_date', '_utd_date',], 'validateDate'],
            [['_arrival_date' ], 'validateDateShort'],
            [['total_weight_with_pkg_auto','chargeable_weight_auto'], 'number'],
            [['mn_num', 'dep_station', 'dep_station_abbr', 'des_station', 'des_station_abbr'], 'string', 'max' => 20],
            [['utd_num', 'utd_code'], 'string', 'max' => 50],
            [['mn_num'], 'unique','message' => Yii::t('manifest','This MN num has already been taken')],
        ]);
    }

    /**
     * * Поведения
     * @return array Массив поведений
     */
    function behaviors()
    {
        return [
            [
                'class' => DateFormatBehavior::className(),
                'attributes' => [
                '_date' => 'date',
                '_utd_date' => 'utd_date',
                ]
            ],
            [
                'class' => ShortDateFormatBehavior::className(),
                'attributes' => [
                    '_arrival_date' => 'arrival_date'
                ]
            ],
            [
                'class' => DocumentStorage::className(),
            ]
        ];
    }

    /**
     * Метод получения сязанных накладных
     * @return \yii\db\ActiveQuery
     */
    public function getEws()
    {
        return $this->hasMany(ExpressWaybill::className(), ['id' => 'ew_id'])->viaTable('{{%mn_ew}}', ['mn_id' => 'id']);
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового манифеста
     */
    public function generateDefaults($params, $ew_arr = null) {

        if ($this->hasErrors())
            return;

        $num_size = 8;
        $new_num = $this->getNextCounterValue('mn_id') % pow(10, $num_size + 1);
        $this->mn_num = str_pad($new_num , $num_size, '0', STR_PAD_LEFT);
        $this->mn_num = 'NPI'.Yii::$app->user->identity->employee->country->alpha2_code.$this->mn_num;
        $this->state = CommonModel::STATE_CREATED;
        $this->date = date(Setup::MYSQL_DATE_FORMAT);

        if ($ew_arr)
            $this->ewsInput = $ew_arr;

        if ($params['operation'] != null)
            $this->copyManifest($params);
    }

    public function copyManifest($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $manifest = Manifest::findOne(['id' => $params['id']]);
            if($manifest) {
                $this->attributes = $manifest->getAttributes(null, ['mn_num', 'id']);
                $this->total_weight_with_pkg_auto = $manifest->total_weight_with_pkg_auto;
                $this->chargeable_weight_auto = $manifest->chargeable_weight_auto;
                $this->utd_date = $manifest->utd_date;
                $this->ewsString = $manifest->ewsString;
                $this->isNewRecord = true;
            }
        }
    }

    /**
     * Метод получения накладных в виде строки разделенной запятыми
     */
    public function getEwsString()
    {
        $result = ['0'];

        foreach ($this->ews as $ew)
            $result[]=$ew->ew_num;

        if ($this->ewsInput)
            $result = $this->ewsInput;

        return implode (",", $result);
    }

    public function setEwsString($value){
        $this->ewsInput = explode(",", $value);
    }

    /**
     * Проверка формата даты времени
     * @param $attribute string  Имя атрибута даты
     * @internal param $params
     */
    public function validateDate($attribute){
        if (!DateFormatBehavior::validate($_POST['Manifest'][$attribute]))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }

    public function validateDateShort($attribute){
        if (!ShortDateFormatBehavior::validate($_POST['Manifest'][$attribute]))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }


    /**
     * Метод получения доступных видов манифестов
     * @return array Массив видов
     */
    public function getTypeList(){
        return
            [
                null=>'',
                self::TYPE_IMPORT=>Yii::t('manifest','Import'),
                self::TYPE_EXPORT_DOC=>Yii::t('manifest','Export (Documents)'),
                self::TYPE_EXPORT_CARGO=>Yii::t('manifest','Export (Cargo)'),
            ];
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'mn_num'=>$this->mn_num,
            'date'=>$this->_date,
            ''=>$this->mn_kind,
            'arrival_date'=>$this->_arrival_date,
            'departure_station'=>$this->de_pstation,
            'destiantion_station'=>$this->des_station,
            'utd_num' => $this->utd_num,
            'utd_date'=>$this->_utd_date,
            'carriers_name_new' => $this->carriers_name_new,
            // Количество ЕН
            'count_ews' => $this->total_ew_amount,
            // К-во консолидированых мест
            'total_pieces_amount' => $this->total_pieces_amount,
            // Количество ШК
            'total_amount_of_pieces' => $this->total_amount_of_pieces,
            // общий расчетный вес
            'total_mn_weight_kg' => $this->total_mn_weight_kg ? Yii::$app->formatter->asDecimal($this->total_mn_weight_kg) : '',
            // Общий вес с упаковкой
            'total_weight_with_pkg_auto' => $this->total_weight_with_pkg_auto ? Yii::$app->formatter->asDecimal($this->total_weight_with_pkg_auto) : '',
            // Общий фактический вес по всем ЕН (сумма)
            'total_actual_weight_kg_ews' => $this->total_actual_weight_kg_ews ? Yii::$app->formatter->asDecimal($this->total_actual_weight_kg_ews) : '',
            'state'=>$this->state
        ];

    }

    public function getTotal_actual_weight_kg_ews() {
        $sum = 0;
        foreach($this->ews as $ew) {
            $sum += $ew->total_actual_weight_kg;
        }
        return $sum;
    }

    /**
     * Метод архивирования
     * @param $operation int Тип операции. LogManifest::OPERATION_
     */
    public function archive($operation){
        $user_id = Yii::$app->user->id;
        $sql = "call archive_manifest($this->id,$operation, $user_id)";
        Yii::$app->db->createCommand($sql)->execute();
    }


    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert,$changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        // сохранение накладных
        $this->saveEws();

        Event::callEvent($this->saveEvent, $this->id, ['model'=>$this]);

        $this->archive($insert?LogManifest::OPERATION_CREATE:LogManifest::OPERATION_UPDATE);

        $this->operation = self::OPERATION_NONE;
    }

    /**
     * Получить логи манифеста
     */
    public function getLogs(){
        return $this
            ->hasMany(LogManifest::className(), ['id' => 'log_row_id'])
            ->viaTable('{{%arch_manifest}}', ['id' => 'id']);
    }

    /**
     * Получить лог создания
     */
    public function getLogCreation(){
        return $this
            ->hasOne(LogManifest::className(), ['id' => 'log_row_id'])
            ->where(['type'=>LogManifest::OPERATION_CREATE])
            ->viaTable('{{%arch_manifest}}', ['id' => 'id']);
    }

    /**
     * Получить последний лог редактирования
     */
    public function getLogLastUpdate(){
        return $this
            ->hasOne(LogManifest::className(), ['id' => 'log_row_id'])
            ->where(['type'=>LogManifest::OPERATION_UPDATE])
            ->orderBy('date desc')
            ->viaTable('{{%arch_manifest}}', ['id' => 'id']);
    }

    /**
     * Получение служебной информации
     * @return array Массив с полями служебной информации
     */
    public function getServiceData(){

        $createArray = [];
        if ($this->logCreation)
            $createArray = $this->logCreation->toJson('create_');
        $lastUpdateArray = [];
        if ($this->logLastUpdate)
            $lastUpdateArray = $this->logLastUpdate->toJson('lastupdate_');


        return array_merge($createArray,$lastUpdateArray);
    }

    /**
     * Метод получения накладных в виде массива
     */
    public function getEwsArray()
    {
        $result = array();

        foreach ($this->ews as $ew)
            $result[]=[
                'ew_id'=>$ew->id,
                'ew_num'=>$ew->ew_num,
            ];

        return $result;
    }


    /**
     * Метот получения и разбора данных от пользователя
     * @param array $data
     * @param null $formName
     * @return bool
     */
    public function load($data, $formName = null){

        if (parent::load($data,$formName)){

            // места и позиции формируются на стороне клиента в виде скрытых полей с именами EwPlace_ и EwPosition_
            $patternEw = '/^Ew_/';

            foreach ($data as $key => $value){
                if (preg_match($patternEw, $key)) {
                    $ew = ExpressWaybill::findOne(['ew_num'=>$value['ew_num']]);
                    if ($ew!=null)
                        $this->ewsInput[] = $value['ew_num'];
                }
            }

            return true;
        }

        return false;
    }


    /**
     * Метод перед сохранением накладной
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert){

        if (parent::beforeSave($insert)) {

            $this->saveEvent = $insert ? Event::MN_CREATE :Event::MN_UPDATE;

            if ($this->operation == self::OPERATION_DELETE) {
                $this->saveEvent = Event::MN_DELETE;
                //$this->state = self::STATE_DELETED;
            }
            else if ($this->operation == self::OPERATION_CANCEL) {
                $this->saveEvent = Event::MN_DELETE_CACNEL;
                //$this->state = self::STATE_CREATED;
            }

            return true;
        }

        return false;
    }

    public function getTotal_weight_with_pkg_auto(){


        $result = null;
        $result_dim = null;
        foreach ($this->ews as $ew) {
            $result += $ew->total_actual_weight_kg;
            $result_dim += $ew->total_dimensional_weight_kg;
        }

        // Должен отображаться больший из общего объемного и фактического.
        $result = max($result,$result_dim);

        if ($this->total_weight_with_pkg!=null &&
            $this->total_weight_with_pkg!=$result)
            return $this->total_weight_with_pkg;

        return $result;
    }

    public function setTotal_weight_with_pkg_auto($value){

        if ($this->total_weight_with_pkg_auto != $value)
            $this->total_weight_with_pkg = $value;
    }

    public function getChargeable_weight_auto(){

        $result = $this->total_mn_weight_kg;

        if ($this->chargeable_weight!=null &&
            $this->chargeable_weight!=$result)
            return $this->chargeable_weight;

        return $result;
    }

    public function setChargeable_weight_auto($value){

        if ($this->chargeable_weight_auto != $value)
            $this->chargeable_weight = $value;
    }


    public function getTotal_ew_amount(){
        return count($this->ews);
    }

    public function getTotal_amount_of_pieces(){
        $result = null;
        foreach ($this->ews as $ew)
            $result += $ew->ewPlacesCount;

        return $result;
    }

    public function getTotal_mn_weight_kg(){
        $result = null;
        foreach ($this->ews as $ew) {
            $result +=max($ew->total_dimensional_weight_kg,$ew->total_actual_weight_kg);
        }

        return $result;
    }

    public function getTotal_pay_cost_euro(){
        $result = null;
        foreach ($this->ews as $ew)
            $result += $ew->customs_declaration_cost * ExchangeRate::getExRate($ew->customs_declaration_currency, 2) ;

        return $result;
    }
    /**
     * Получение обычных фильтров
     * @return array filters
     */
    public function getFilters(){

        return  [
            ['id'=>'f_mn_num', 'label'=>Yii::t('manifest','Number').':', 'operation' => '=', 'field' => 'mn_num'],
            ['id'=>'f_mn_date_begin', 'type'=>self::FILTER_DATETIME, 'label'=>Yii::t('manifest','Date from').':', 'operation' => '>=', 'field' => 'date'],
            ['id'=>'f_mn_date_end',   'type'=>self::FILTER_DATETIME, 'label'=>Yii::t('manifest','Date to').':',  'operation' => '<=', 'field' => 'date' ],
        ];
    }
    /**
     * Получение расширенных фильтров
     * @return array filters
     */
    public function getAFilters($withItems=true){

        $urlEntities = Url::to(['list-entity-type/get-list']);
        $urlCarriers = Url::to(['manifest/get-list-carrier']);
        $urlCity = Url::to(['manifest/get-list-city']);
        $urlCountries = Url::to(['country/get-list']);
        $urlCities = Url::to(['city/get-list']);
        $urlWarehouses = Url::to(['warehouse/get-list']);
        $urlEmployees = Url::to(['employee/get-list']);
        $urlOrderTypes = Url::to(['wb-order-type/get-list']);
        $urlMnKind = Url::to(['manifest/get-list-mn-kind']);
        $listEntity = $withItems?ListEntityType::getVisibleList(true,'name_short'):[];
        $signTable = CounterpartySign::tableName();
        $cpTable = Counterparty::tableName();

        $list[null] = '';
        foreach($listEntity as $key => $val) {
            if($key == ExpressWaybill::ENTITY_TYPE) {
                $list[$key] = $val;
                continue;
            }
            if($key == Manifest::ENTITY_TYPE) {
                $list[$key] = $val;
                continue;
            }
        }
        $cityList = $withItems?ListCity::getList('name', true):[];

        return  [
            [
                'id' => 'title',
                'label' => Yii::t('manifest', 'Manifest') . ':',
            ],
            [
                'id' => 'af_ew_lang',
                'type' => self::FILTER_DROPDOWN,
                'value' => Yii::$app->language,
                'label' => Yii::t('app', 'Language') . ':',
                'items' => $withItems?Langs::$Names:[],
                'lang_selector' => true,
            ],
            // Сущность
            [
                'id' => 'af_ew_entity',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('manifest', 'Document') . ':',
                'items' => $withItems?$list:[],
                'lang_dependency' => true,
                'url' => $urlEntities,
            ],
            // Номер сущности
            [
                'id' => 'af_ew_entity_num',
                'label' => Yii::t('ew', 'Number') . ':',
            ],
            // Тип накладной/заказа
            [
                'id' => 'af_ew_order_type',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('manifest', 'Тип') . ':',
                'items' => $withItems?WbOrderType::getVisibleList(true):[],
                'lang_dependency' => true,
                'url' => $urlOrderTypes,
            ],
            [
                'id' => 'af_ew_order_carrier',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'label' => Yii::t('manifest', 'Перевозчик') . ':',
                'items' => $withItems?ListCarrier::getList('name',true):[],
            ],
            // Период дат сущности
            [
                'id' => 'af_ew_entity_date_begin',
                'type' => self::FILTER_DATETIME,
                'label' => Yii::t('manifest', 'Дата сущности с') . ':',
            ],
            [
                'id' => 'af_ew_entity_date_end',
                'type' => self::FILTER_DATETIME,
                'label' => Yii::t('manifest', 'Дата сущности по') . ':',
            ],

            [
                'id' => 'title',
                'label' => Yii::t('manifest', 'Параметры манифеста') . ':',
            ],

            ['id'=>'af_dep_station', 'label'=>Yii::t('manifest','Станция отправления').':', 'operation' => 'like', 'field' => 'dep_station'],
            ['id'=>'af_des_station', 'label'=>Yii::t('manifest','Станция назначения').':', 'operation' => 'like', 'field' => 'des_station'],
            [
                'id' => 'af_carriers_id',
                'operation' => '=',
                'field' => 'carriers_id',
                'type' => self::FILTER_SELECT2,
                'label' => Yii::t('manifest', 'Название перевозчика') . ':',
                'items' =>  $withItems?Counterparty::getListFast(true, null, "exists(select * from $signTable where counterparty_id = $cpTable.id and counterparty_sign_id = 6)"):[],
                'lang_dependency' => true,
                'url' => $urlCarriers,

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'Название перевозчика').': '.Yii::t('tab_title', 'Перевозчик').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['counterparty/counterparty/index-carrier']),
                'select_tab_uniqname' => 'filtermanifest_counterparty',
                'view_tab_title' => Yii::t('tab_title', 'Перевозчик').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['counterparty/counterparty/view']),
                'view_tab_uniqname' => 'counterparty_{0}',
            ],
            [
                'id' => 'af_city',
                'operation' => '=',
                'field' => 'dep_point_id',
                'items' => $withItems?$cityList:[],
                'type' => self::FILTER_SELECT2,
                'label' => Yii::t('manifest', 'Пункт отправления') . ':',
                'lang_dependency' => true,
                'url' => $urlCity,

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'Пункт отправления').': '.Yii::t('tab_title', 'Населенный пункт').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname' => 'filtermanifest_punctcity',
                'view_tab_title' => Yii::t('tab_title', 'Населенный пункт').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname' => 'city_{0}',
            ],
            [
                'id' => 'af_ew_state',
                'type' => self::FILTER_CHECKBOXESDROPDOWN,
                'items' => $withItems?$this->getStateList(false):[],
                'operation' => 'in',
                'field' => 'mn.state',
                'label' => Yii::t('manifest', 'Состояние МН') . ':',
            ],
            // Вид МН
            [
                'id' => 'af_mn_kind',
                'type' => self::FILTER_DROPDOWN,
                'items' => $withItems?ListMnKind::getList('name',true):[],
                'operation' => '=',
                'field' => 'mn_kind',
                'label' => Yii::t('manifest', 'Вид МН') . ':',
                'lang_dependency' => true,
                'url' => $urlMnKind,
            ],
            [
                'id' => 'title',
                'label' => Yii::t('manifest', 'Параметры создания') . ':',
            ],
            // Дата создания с по
            [
                'id' => 'af_ew_date_begin',
                'type' => self::FILTER_DATETIME,
                'field'=>"log_empl.date",
                'operation'=>">=",
                'label' => Yii::t('manifest', 'Дата создания с') . ':',
            ],
            [
                'id' => 'af_ew_date_end',
                'field'=>"log_empl.date",
                'operation'=>"<=",
                'type' => self::FILTER_DATETIME,
                'label' => Yii::t('manifest', 'Дата создания по') . ':',
            ],
            [
                'id' => 'af_ew_creation_country',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'Country') . ':',
                'items' =>  $withItems?Country::getListFast():[],
                'lang_dependency' => true,
                'url' => $urlCountries,
                'field'=>'log_empl.country_id',
                'operation' => '=',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'Манифест').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'filtermanifest_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],
            [
                'id' => 'af_ew_creation_city',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'City') . ':',
                'items' => $withItems?$cityList:[] ,
                'lang_dependency' => true,
                'url' => $urlCities,
                'operation' => '=',
                'field'=>'log_empl.city_id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'Параметры создания').': '.Yii::t('tab_title', 'Населенный пункт').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname' => 'filtermanifest_punctcity',
                'view_tab_title' => Yii::t('tab_title', 'Населенный пункт').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname' => 'city_{0}',
            ],
            [
                'id' => 'af_ew_creation_department',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'Department') . ':',
                'items' => $withItems?ListWarehouse::getList('name', true):[],
                'lang_dependency' => true,
                'url' => $urlWarehouses,
                'operation' => '=',
                'field'=>'log_empl.warehouse_id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'Параметры создания').': '.Yii::t('tab_title', 'Склад').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/warehouse/index']),
                'select_tab_uniqname' => 'filtermanifest_warehouse',
                'view_tab_title' => Yii::t('tab_title', 'Склад').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/warehouse/view']),
                'view_tab_uniqname' => 'warehouse_{0}',
            ],
            [
                'id' => 'af_ew_creation_user',
                'type' => self::FILTER_SELECT2,
                'value' => '',
                'label' => Yii::t('ew', 'User') . ':',
                'items' => $withItems?Employee::getListFast('surname_full', true):[],
                'lang_dependency' => true,
                'url' => $urlEmployees,
                'operation' => '=',
                'field'=>'log_empl.id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'Параметры создания').': '.Yii::t('tab_title', 'Сотрудник').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/employee/index']),
                'select_tab_uniqname' => 'filtermanifest_employee',
                'view_tab_title' => Yii::t('tab_title', 'Сотрудник').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/employee/view']),
                'view_tab_uniqname' => 'employee_{0}',
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMnHistoryEvents()
    {
        return $this->hasMany(MnHistoryEvents::className(), ['mn_id' => 'id'])->orderBy('date desc');
    }

    private function saveEws(){

        $ewsInBase = $this->hasMany(ExpressWaybill::className(), ['id' => 'ew_id'])
            ->viaTable('{{%mn_ew}}', ['mn_id' => 'id'])
            ->all();
        $ewsSaved = [];

        // обход по новым записям
        if ($this->ewsInput != null)
            foreach ($this->ewsInput as $ewInput) {

                // поиск среди уже присоединенных
                $search = false;
                foreach ($ewsInBase as $ewInBase){
                    if ($ewInBase->ew_num == $ewInput){
                        $search = true;
                        break;
                    }
                }

                // среди присоединенных не найден, добавляем
                if (!$search) {

                    $newEw = ExpressWaybill::findOne(['ew_num'=>$ewInput]);
                    Yii::$app->db->createCommand()
                        ->insert('{{%mn_ew}}', ['mn_id' => $this->id, 'ew_id' => $newEw->id])
                        ->execute();

                    Event::callEvent(Event::EW_LINK_MN, $newEw->id);
                }

                $ewsSaved[] = $ewInput;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        foreach ($ewsInBase as $ew){
            if (!in_array($ew->ew_num, $ewsSaved)){

                Yii::$app->db->createCommand()
                    ->delete('{{%mn_ew}}', ['mn_id' => $this->id,'ew_id'=>$ew->id])
                    ->execute();

                Event::callEvent(Event::EW_UNLINK_MN, $ew->id);
            }
        }

    }

    public function getGridOperations() {
        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app','Copy'),
        ];
    }

    public function getGridOperationsOptions() {
        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before'=>true, 'tab_name_sufix'=>'copy'],
        ];
    }

    public function getDepPointModel(){
        return $this->hasOne(ListCity::className(),['id'=>'dep_point_id']);
    }

    public function getCarriersModel(){
        return $this->hasOne(Counterparty::className(),['id'=>'carriers_id']);
    }

    public function getDep_point_name(){
        if ($this->depPointModel){
            return $this->depPointModel->name;
        }

        return $this->dep_point;
    }

    public function getCarriers_name_new(){
        return $this->carriersModel->counterpartyName;
    }

    public function getRelatedEntities() {

        $result = [];

        $ews = $this->ews;
        foreach ($ews as $ew) {
            $sd = $ew->serviceData;
            $result[] = [
                'id' => $ew->id,
                'state' => $ew->state,
                'document' => Yii::t('tab_title', 'EW_short_name'),
                'doc_type_num' => 2,
                'doc_num' => $ew->ew_num,
                'doc_type' => $ew->ewType->name,
                'date' => $ew->_date,
                'params' => $sd['create_country'] . ' ' . $sd['create_city'] . ' ' . $sd['create_departament'] . ' ' . $sd['create_surname']
            ];
        }

        return json_encode($result);
    }

    public static function checkUniqueManifestNum($mnNum) {
        return Manifest::findOne(['mn_num' => $mnNum]) ? Yii::t('error', "Attention! $mnNum already exists") : '';
    }

    /**
     * Получение всей таблицы МН в виде массива полей
     * @param null $lang требуемый язык
     * @param string $where
     * @return array массив манифестов в виде массива их полей
     */
    public static function selectAsArray($lang = null, $where = ''){

        if  (!$lang)
            $lang = Yii::$app->language;

        $mnTable = "mainmanifest";
        $ewTable = ExpressWaybill::tableName();
        $cpTable = Counterparty::tableName();
        $privatePersTable = CounterpartyPrivatPers::tableName();
        $legalEntityTable = CounterpartyLegalEntity::tableName();
        $placeTable = EwPlace::tableName();
        $mnLogTable = "{{%log_manifest}}";
        $mnArchTable = "{{%arch_manifest}}";
        $employee_table = Employee::tableName();
        $usrTable = User::tableName();
        $mnEwTable = "{{%mn_ew}}";

        $columns = [
            "$mnTable.id",
            "$mnTable.state",
            "$mnTable.mn_num",
            "DATE_FORMAT($mnTable.date,'%d.%m.%Y %H:%i:%s') as date",
            "$mnTable.dep_station as departure_station",
            "$mnTable.des_station as destination_station",
            "$mnTable.utd_num",
            "DATE_FORMAT(utd_date,'%d.%m.%Y %H:%i:%s') as utd_date",
            "coalesce (coalesce(carrier_pp.display_name_$lang,carrier_le.display_name_$lang), '') as carriers_name_new",
            "DATE_FORMAT(arrival_date,'%d.%m.%Y %H:%i:%s') as arrival_date",
            "(select count(*) from $ewTable where id in (select ew_id from $mnEwTable where mn_id = $mnTable.id) ) as count_ews",
            "$mnTable.total_pieces_amount",
            //"(select sum(ew_get_places_count(id)) FROM $ewTable WHERE id IN (SELECT ew_id FROM $mnEwTable WHERE mn_id = $mnTable.id)) as total_amount_of_pieces",
            "(SELECT sum((select count(id) from $ewTable LEFT JOIN $placeTable ON $placeTable.ew_id=$ewTable.id where $mnEwTable.ew_id=$ewTable.id)) AS cnt FROM ".self::tableName()." as manifest, $mnEwTable WHERE mn_id=id and $mnTable.id=manifest.id) as total_amount_of_pieces",
            "(select sum( GREATEST(total_dimensional_weight_kg,total_actual_weight_kg)) from $ewTable where id in (select ew_id from $mnEwTable where mn_id = $mnTable.id) ) as total_mn_weight_kg",
            "coalesce (total_weight_with_pkg, (select GREATEST( sum(total_dimensional_weight_kg), sum(total_actual_weight_kg)) from $ewTable where id in (select ew_id from $mnEwTable where mn_id = $mnTable.id))) as total_weight_with_pkg_auto",
            "(select sum( total_actual_weight_kg) from $ewTable where id in (select ew_id from $mnEwTable where mn_id = $mnTable.id) ) as total_actual_weight_kg_ews",

        ];


        $query = (new Query)
            ->select($columns)
            ->distinct(true)
            ->from(self::tableName().' '.$mnTable)
            ->leftJoin("$cpTable carrier", "carrier.id = $mnTable.carriers_id")
            ->leftJoin("$privatePersTable carrier_pp", "carrier_pp.counterparty = carrier.id")
            ->leftJoin("$legalEntityTable carrier_le", "carrier_le.counterparty = carrier.id")
            ->leftJoin("$mnLogTable log", "log.type = 1 and log.id in (select log_row_id from $mnArchTable WHERE id = $mnTable.id)")
            ->leftJoin("$employee_table log_empl", "log_empl.id = (select employee_id from $usrTable where user_id = log.user_id)")
            ->where($where)
            ->orderBy("$mnTable.date desc");

        return self::getDataWithLimits($query);
    }

    /**
     * Метод для получения прикрепленных к манифесту документов
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDocs()
    {
        return $this->hasMany(AttachedDoc::className(), ['id' => 'attdoc_id'])
            ->viaTable(MnAttachedDoc::tableName(), ['mn_id' => 'id']);
    }
}

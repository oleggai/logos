<?php

namespace app\models\dictionaries\warehouse;

use app\models\counterparty\Counterparty;
use app\models\counterparty\CounterpartyManualAdress;
use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\common\Langs;
use app\models\common\CommonModel;
use app\models\common\ShortDateFormatBehavior;
use app\models\common\DateShortStringBehavior;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\address\ListAdressKind;
use app\models\dictionaries\address\ListDayofweek;
use app\models\dictionaries\address\ListStreet;
use app\models\dictionaries\address\ListBuildingType;
use app\models\dictionaries\employee\Employee;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\country\Country;

/**
 * Модель для подразделений
 * @author Richok FG
 * @category warehouse
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $name_short_en
 * @property string $name_short_ru
 * @property string $name_short_uk
 * @property integer $is_css
 * @property string $warehouse_type
 * @property string $warehouse_subtype
 * @property string $warehouse_number
 * @property string $max_fact_weight
 * @property string $max_lenght
 * @property string $max_width
 * @property string $max_height
 * @property string $ramp_amount
 * @property string $latitude
 * @property string $longitude
 * @property integer $visible
 * @property integer $state
 * @property string $director
 * @property string $open_date
 * @property string $close_date
 * @property string $_open_date
 * @property string $_close_date
 * @property string $addition_info
 * @property string $city
 * @property integer $is_transit
 * @property integer $is_internal
 * @property integer $is_plocker
 * @property integer $is_terminal
 * @property string $fix_cust_auth_en
 * @property string $fix_cust_auth_ru
 * @property string $fix_cust_auth_uk
 * @property string $div_fix_cust_auth_en
 * @property string $div_fix_cust_auth_ru
 * @property string $div_fix_cust_auth_uk
 * @property string $phone_for_clients
 * @property string $phone_for_emploeeys
 * @property string $email
 * @property string $index
 * @property string $street
 * @property string $buildingtype_level1
 * @property string $number_level1
 * @property string $buildingtype_level2
 * @property string $number_level2
 * @property string $buildingtype_level3
 * @property string $number_level3
 *
 * @propery string addressShortEn
 * @propery string addressShortUk
 * @propery string addressShortRu
 *
 * @property Counterparty[] $counterparties
 * @property Employee[] $employees
 * @property ListWarehouseType $warehouseType
 * @property Employee $directorModel
 * @property ListCity $cityModel
 * @property ListWarehouseSubtype $warehouseSubtype
 * @property ListBuildingType $buildingtypeLevel1
 * @property ListBuildingType $buildingtypeLevel2
 * @property ListBuildingType $buildingtypeLevel3
 * @property ListStreet $streetModel
 * @property ListWarehouseCamera[] $cameras
 * @property ListWarehouseRoute[] $routes
 * $property ListWarehouseZone[] $zones
 * @property ListWarehouseScheduleReception[] $listWarehouseScheduleReceptions
 * @property ListWarehouseZone[] $listWarehouseZones
 */
class ListWarehouse extends CommonModel
{
    /**
     * @var ListWarehouseScheduleReception[]
     */
    public $listWarehouseScheduleReceptionsInput;
    public $listWarehouseScheduleReceptionsInputAppend;

    /**
     * @var ListWarehouseRoute[]
     */
    public $routesInput;
    public $routesInputAppend;
    /**
     * @var ListWarehouseZone[]
     */
    public $zonesInput;
    public $zonesInputAppend;
    /**
     * @var ListWarehouseCamera[]
     */
    public $camerasInput;
    public $camerasInputAppend;
    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%list_warehouse}}';
    }

    /**
     * Поведения
     */
    function behaviors()
    {
        return [
            [
                'class' => ShortDateFormatBehavior::className(),
                'attributes' => [
                    '_open_date' => 'open_date',
                    '_close_date' => 'close_date'
                ]
            ]
        ];
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name_en', 'name_ru', 'name_short_en', 'name_short_ru', 'warehouse_type', 'warehouse_subtype', 'city'], 'required'],
            [['warehouse_number', 'max_fact_weight', 'max_lenght', 'max_width', 'max_height', 'ramp_amount'], 'number'],
            [['is_css', 'warehouse_type', 'warehouse_subtype', 'visible', 'state', 'director', 'city', 'is_transit', 'is_internal', 'is_plocker', 'is_terminal', 'street', 'buildingtype_level1', 'buildingtype_level2', 'buildingtype_level3'], 'integer'],
            ['warehouse_number', 'integer', 'max' => 999, 'min' => 0],
            [['_open_date', '_close_date'], 'validateDateShort'],
            [['name_en', 'name_ru', 'name_uk', 'addition_info', 'fix_cust_auth_en', 'fix_cust_auth_ru', 'fix_cust_auth_uk', 'div_fix_cust_auth_en', 'div_fix_cust_auth_ru', 'div_fix_cust_auth_uk'], 'string', 'max' => 100],
            [['name_short_en', 'name_short_ru', 'name_short_uk'], 'string', 'max' => 80],
            [['latitude', 'longitude', 'phone_for_clients', 'phone_for_emploeeys', 'email', 'number_level1'], 'string', 'max' => 50],
            [['number_level2', 'number_level3'], 'string', 'max' => 50],
            [['index'], 'string', 'max' => 20],
            ['listWarehouseScheduleReceptions', 'validateListWarehouseScheduleReceptions'],
            ['routes', 'validateRoutes'],
            ['zones', 'validateZones'],
            ['cameras', 'validateCameras'],
            ['zones', 'validateZones'],
            ['number_level2', 'required', 'when' => function($model) { return $model->buildingtype_level2 != null; }],
            ['number_level3', 'required', 'when' => function($model) { return $model->buildingtype_level3 != null; }]
        ]);
    }

    /**
     * Надписи для полей
     * @return array массив названий полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('warehouse', 'ID'),
            'name' => Yii::t('warehouse', 'Name'),
            'name_en' => Yii::t('warehouse', 'Warehouse name (full) (Eng)'),
            'name_ru' => Yii::t('warehouse', 'Warehouse name (full) (Rus)'),
            'name_uk' => Yii::t('warehouse', 'Warehouse name (full) (Ukr)'),
            'name_short_en' => Yii::t('warehouse', 'Name Short En'),
            'name_short_ru' => Yii::t('warehouse', 'Name Short Ru'),
            'name_short_uk' => Yii::t('warehouse', 'Name Short Uk'),
            'is_css' => Yii::t('warehouse', 'CSS'),
            'warehouse_type' => Yii::t('warehouse', 'Warehouse type'),
            'warehouse_subtype' => Yii::t('warehouse', 'Warehouse subtype'),
            'warehouse_number' => Yii::t('warehouse', 'Warehouse number'),
            'max_fact_weight' => Yii::t('warehouse', 'Actual weight restriction, kg'),
            'max_lenght' => Yii::t('warehouse', 'Max lenght, m'),
            'max_width' => Yii::t('warehouse', 'Max width, m'),
            'max_height' => Yii::t('warehouse', 'Max height, m'),
            'ramp_amount' => Yii::t('warehouse', 'Number of ramp'),
            'latitude' => Yii::t('warehouse', 'Geographic coordinates, La'),
            'longitude' => Yii::t('warehouse', 'Geographic coordinates, Lo'),
            'visible' => Yii::t('warehouse', 'Visible'),
            'state' => Yii::t('warehouse', 'State'),
            'director' => Yii::t('warehouse', 'Head of warehouse'),
            '_open_date' => Yii::t('warehouse', 'Open Date'),
            '_close_date' => Yii::t('warehouse', 'Close Date'),
            'addition_info' => Yii::t('warehouse', 'Addition Info'),
            'city' => Yii::t('warehouse', 'City'),
            'is_transit' => Yii::t('warehouse', 'Is Transit'),
            'is_internal' => Yii::t('warehouse', 'Is Internal'),
            'is_plocker' => Yii::t('warehouse', 'Parcel locker'),
            'is_terminal' => Yii::t('warehouse', 'Is Terminal'),
            'fix_cust_auth_en' => Yii::t('warehouse', 'Fix Cust Auth En'),
            'fix_cust_auth_ru' => Yii::t('warehouse', 'Fix Cust Auth Ru'),
            'fix_cust_auth_uk' => Yii::t('warehouse', 'Fix Cust Auth Uk'),
            'div_fix_cust_auth_en' => Yii::t('warehouse', 'Div Fix Cust Auth En'),
            'div_fix_cust_auth_ru' => Yii::t('warehouse', 'Div Fix Cust Auth Ru'),
            'div_fix_cust_auth_uk' => Yii::t('warehouse', 'Div Fix Cust Auth Uk'),
            'phone_for_clients' => Yii::t('warehouse', 'Phone For Clients'),
            'phone_for_emploeeys' => Yii::t('warehouse', 'Phone For Emploeeys'),
            'email' => Yii::t('warehouse', 'Email'),
            'index' => Yii::t('warehouse', 'Index'),
            'street' => Yii::t('warehouse', 'Street'),

            'buildingtype_level1' => Yii::t('warehouse', '1-st lvl. type number'),
            'number_level1' => Yii::t('warehouse', 'Number_2_1'),
            'buildingtype_level2' => Yii::t('warehouse', '2-nd lvl. type number'),
            'number_level2' => Yii::t('warehouse', 'Number_2_2'),
            'buildingtype_level3' => Yii::t('warehouse', '3-rd lvl. type number'),
            'number_level3' => Yii::t('warehouse', 'Number_2_3'),
        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового склада
     * @param $params
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;

        if ($params['operation'] != null)
            $this->copyWarehouse($params);
    }

    public function copyWarehouse($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $warehouse = ListWarehouse::findOne(['id' => $params['id']]);
            if($warehouse) {
                $this->attributes = $warehouse->getAttributes();
                $this->open_date = $warehouse->open_date;
                $this->close_date = $warehouse->close_date;
            }
        }
    }

    /**
     * Получить название на текущем языке
     * @return mixed
     */
    public function getName($lang = '') {
        if(empty($lang))
            $lang = Yii::$app->language;
        return $this->getAttribute('name_' . Yii::$app->language);
    }

    /**
     * Получить краткое название на текущем языке
     * @return mixed
     */
    public function getNameShort() {
        return $this->getAttribute('name_short_' . Yii::$app->language);
    }

    /**
     * Получить название закрепленного таможенноого органа на текущем языке
     * @return mixed
     */
    public function getFixedCustomsAuth() {
        return $this->getAttribute('fix_cust_auth_' . Yii::$app->language);
    }

    /**
     * Получить подразделение закрепленного таможенноого органа на текущем языке
     * @return mixed
     */
    public function getDivisionFixedCustomsAuth() {
        return $this->getAttribute('div_fix_cust_auth_' . Yii::$app->language);
    }

    /**
     * ДЛЯ ВСЕХ ПОДРАЗДЕЛЕНИЙ ВИД АДРЕСА - ДВЕРИ (id = 1)
     * @return int
     */
    public function getAddressKind() {
        return 1;
    }

    public function getAddressKindModel() {
        return ListAdressKind::findOne(['id' => $this->addressKind]);
    }

    /**
     * Получить краткий адрес на английском
     * @return string
     */
    public function getAddressShortEn() {

        $addr = $this->streetModel->streetTypeModel->name_en . ' ' . $this->streetModel->name_en . ' ' .
            $this->buildingtypeLevel1->name_en . ' ' . $this->number_level1;

        if ($this->buildingtypeLevel2)
            $addr .= ' ' . $this->buildingtypeLevel2->name_en . ' ' . $this->number_level2;
        if ($this->buildingtypeLevel3)
            $addr .= ' ' . $this->buildingtypeLevel3->name_en . ' ' . $this->number_level3;

        return $addr;
    }

    /**
     * Получить краткий адрес на украинском
     * @return string
     */
    public function getAddressShortUk() {

        $addr = $this->streetModel->streetTypeModel->name_uk . ' ' . $this->streetModel->name_uk . ' ' .
            $this->buildingtypeLevel1->name_uk . ' ' . $this->number_level1;

        if ($this->buildingtypeLevel2)
            $addr .= ' ' . $this->buildingtypeLevel2->name_uk . ' ' . $this->number_level2;
        if ($this->buildingtypeLevel3)
            $addr .= ' ' . $this->buildingtypeLevel3->name_uk . ' ' . $this->number_level3;

        return $addr;
    }

    /**
     * Получить краткий адрес на русском
     * @return string
     */
    public function getAddressShortRu() {

        $addr = $this->streetModel->streetTypeModel->name_ru . ' ' . $this->streetModel->name_ru . ' ' .
            $this->buildingtypeLevel1->name_ru . ' ' . $this->number_level1;

        if ($this->buildingtypeLevel2)
            $addr .= ' ' . $this->buildingtypeLevel2->name_ru . ' ' . $this->number_level2;
        if ($this->buildingtypeLevel3)
            $addr .= ' ' . $this->buildingtypeLevel3->name_ru . ' ' . $this->number_level3;

        return $addr;
    }

    public function validateListWarehouseScheduleReceptions() {

        if ($this->listWarehouseScheduleReceptionsInput) {
            foreach ($this->listWarehouseScheduleReceptionsInput as $schedule) {
                if (!$schedule->validate()) {
                    $this->addErrors($schedule->errors);
                    break;
                }
            }
        }
    }

    public function getListWarehouseScheduleReceptions() {

        if (!$this->listWarehouseScheduleReceptionsInput)
            $this->listWarehouseScheduleReceptionsInput = $this->hasMany(ListWarehouseScheduleReception::className(), ['warehouse' => 'id'])->all();

        if (!$this->listWarehouseScheduleReceptionsInput) {

            $days = ListDayofweek::getList();
            $types = ListWarehouseScheduleType::getList();

            foreach (array_keys($days) as $day) {
                foreach (array_keys($types) as $type) {

                    $schedule = new ListWarehouseScheduleReception();
                    $schedule->warehouse_schedule_type = $type;
                    $schedule->dayofweek = $day;
                    $schedule->warehouse = $this->id;
                    $schedule->time_begin = '00:00';
                    $schedule->time_end = '00:00';
                    $this->listWarehouseScheduleReceptionsInput[] = $schedule;
                }
            }
        }
        return $this->listWarehouseScheduleReceptionsInput;
    }

    public function setListWarehouseScheduleReceptions($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->listWarehouseScheduleReceptionsInputAppend  = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        for ($i = 1; $i <= count($value); $i++) {

            if (!isset($value[$i]))
                continue;

            // новая запись
            $schedule = new ListWarehouseScheduleReception();

            // или существующая
            if ($value[$i]['id'] > 0)
                $schedule = ListWarehouseScheduleReception::findOne(['id' => $value[$i]['id']]);


            $schedule->load($value, $i);
            $this->listWarehouseScheduleReceptionsInput[] = $schedule;
        }
    }

    public function saveListWarehouseScheduleReceptions(){

        $scheduleInBase = ListWarehouseScheduleReception::findAll(['warehouse' => $this->id]);
        $scheduleSaved = [];

        // обход по новым записям
        if ($this->listWarehouseScheduleReceptionsInput)
            foreach ($this->listWarehouseScheduleReceptionsInput as $schedule) {

                $schedule->warehouse = $this->id;
                $schedule->save();
                $scheduleSaved[] = $schedule->id;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$this->listWarehouseScheduleReceptionsInputAppend)
        foreach ($scheduleInBase as $schedule){
            if (!in_array($schedule->id, $scheduleSaved)){
                $schedule->delete();
            }
        }
    }

    public function validateRoutes() {

        if ($this->routesInput) {

            $keys = [];
            foreach ($this->routesInput as $route) {

                if (!$route->validate()) {
                    $this->addErrors($route->errors);
                    break;
                }
                $keys[] = $route->city . '_' . $route->zone . '_' . $route->departure_time . '_'
                    . $route->monday . '_' . $route->tuesday . '_' . $route->wednesday . '_'
                    . $route->thursday . '_' . $route->friday . '_' . $route->saturday;

                $days = $route->monday + $route->tuesday + $route->wednesday + $route->thursday
                    + $route->friday + $route->saturday + $route->sunday;
                if ($days == 0) {
                    $this->addError('routes', Yii::t('warehouse', 'At least on day must be selected'));
                    break;
                }
            }

            if (count($keys) > count(array_unique($keys))) {
                //$this->addError('routes', Yii::t('error',
                //    (new ListTariffServiceType())->getAttributeLabel('service_type') . ' ' . 'должно быть уникальным значением'));
            }
        }
    }

    public function getRoutes() {

        if (!$this->routesInput)
            $this->routesInput = $this->hasMany(ListWarehouseRoute::className(), ['warehouse' => 'id'])->all();

        return $this->routesInput;
    }

    public function setRoutes($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->routesInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);


        for ($i = 1; $i <= count($value); $i++) {

            if (!isset($value[$i]))
                continue;

            // новая запись
            $route = new ListWarehouseRoute();

            // или существующая
            if ($value[$i]['id'] > 0)
                $route = ListWarehouseRoute::findOne(['id' => $value[$i]['id']]);


            $route->load($value, $i);
            $this->routesInput[] = $route;
        }
    }

    public function saveRoutes() {

        $routeInBase = ListWarehouseRoute::findAll(['warehouse' => $this->id]);
        $routeSaved = [];

        // обход по новым записям
        if ($this->routesInput)
            foreach ($this->routesInput as $route) {

                $route->warehouse = $this->id;
                $route->save();
                $routeSaved[] = $route->id;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$this->routesInputAppend)
        foreach ($routeInBase as $route){
            if (!in_array($route->id, $routeSaved)){
                $route->delete();
            }
        }
    }

    public function validateZones() {

        if ($this->zonesInput) {
            foreach ($this->zonesInput as $zone) {
                if (!$zone->validate()) {
                    $this->addErrors($zone->errors);
                    break;
                }
            }
        }
    }

    public function getZones() {

        if (!$this->zonesInput)
            $this->zonesInput = $this->hasMany(ListWarehouseZone::className(), ['warehouse' => 'id'])->all();

        return $this->zonesInput;
    }

    public function setZones($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->zonesInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        for ($i = 1; $i <= count($value); $i++) {

            if (!isset($value[$i]))
                continue;


            // новая запись
            $zone = new ListWarehouseZone();

            // или существующая
            if ($value[$i]['id'] > 0)
                $zone = ListWarehouseZone::findOne(['id' => $value[$i]['id']]);


            $zone->load($value, $i);
            $this->zonesInput[] = $zone;
        }
    }

    public function saveZones() {

        $zoneInBase = ListWarehouseZone::findAll(['warehouse' => $this->id]);
        $zoneSaved = [];

        // обход по новым записям
        if ($this->zonesInput)
            foreach ($this->zonesInput as $zone) {

                $zone->warehouse = $this->id;
                $zone->save();
                $zoneSaved[] = $zone->id;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$this->zonesInputAppend)
        foreach ($zoneInBase as $zone){
            if (!in_array($zone->id, $zoneSaved)){
                $zone->delete();
            }
        }
    }

    public function validateDateShort($attribute){
        if (!DateShortStringBehavior::validate($_POST['ListWarehouse'][$attribute]))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterparties()
    {
        return $this->hasMany(Counterparty::className(), ['warehouse_fixation' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployees()
    {
        return $this->hasMany(Employee::className(), ['warehouse_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseType()
    {
        return $this->hasOne(ListWarehouseType::className(), ['id' => 'warehouse_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDirectorModel()
    {
        return $this->hasOne(Employee::className(), ['id' => 'director']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCityModel()
    {
        return $this->hasOne(ListCity::className(), ['id' => 'city']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseSubtype()
    {
        return $this->hasOne(ListWarehouseSubtype::className(), ['id' => 'warehouse_subtype']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuildingtypeLevel1()
    {
        return $this->hasOne(ListBuildingType::className(), ['id' => 'buildingtype_level1']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuildingtypeLevel2()
    {
        return $this->hasOne(ListBuildingType::className(), ['id' => 'buildingtype_level2']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuildingtypeLevel3()
    {
        return $this->hasOne(ListBuildingType::className(), ['id' => 'buildingtype_level3']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStreetModel()
    {
        return $this->hasOne(ListStreet::className(), ['id' => 'street']);
    }

    /**
     * Метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {


    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            $this->saveListWarehouseScheduleReceptions();
            $this->saveRoutes();
            $this->saveZones();
            $this->saveCameras();

            //обновляем все адреса КА, к которым привязан склад
            $city_m = ListCity::findOne($this->city);
            $region2_m = ListRegion::findOne($city_m->region);
            $region1_m = ListRegion::findOne($region2_m->parent_id);
            $country_m = Country::findOne($region1_m->country);
            CounterpartyManualAdress::updateAll(['country_id'=>$country_m->id, 'region_lvl1_id'=>$region1_m->id,
                'region_lvl2_id'=>$region2_m->id, 'city_id'=>$this->city,
                'adress_en'=>$this->name_en, 'adress_uk'=>$this->name_uk, 'adress_ru'=>$this->name_ru, 'index'=>$this->index], ['warehouse_id'=>$this->id]);
        }

        $this->operation = self::OPERATION_NONE;

    }

    public function toJson() {

        return [
            'id'                => $this->id,
            'state'             => $this->state,
            'visible_text'      => $this->visibilityText,
            'name_en'           => $this->name_en,
            'name_ru'           => $this->name_ru,
            'name_uk'           => $this->name_uk,
            'warehouse_type'    => $this->warehouseType->name,
            'warehouse_subtype' => $this->warehouseSubtype->name,
            'street_type'       => $this->streetModel->streetTypeModel->name,
            'street'            => $this->streetModel->name,
            'city_type'         => $this->streetModel->cityModel->cityType->name,
            'city'              => $this->streetModel->cityModel->name,
            'index'             => $this->index,
            'region_type2'      => $this->streetModel->cityModel->regionModel->regionType->name,
            'region2'           => $this->streetModel->cityModel->regionModel->name,
            'region_type1'      => $this->streetModel->cityModel->regionModel->parent->regionType->name,
            'region1'           => $this->streetModel->cityModel->regionModel->parent->name,
            'country'           => $this->streetModel->cityModel->regionModel->countryModel->nameShort
        ];
    }

    public function toJsonForAddress() {
        $city_m = ListCity::findOne($this->city);
        $region2_m = ListRegion::findOne($city_m->region);
        $region1_m = ListRegion::findOne($region2_m->parent_id);
        $country_m = Country::findOne($region1_m->country);

        return [
            'name_en'       => $this->name_en,
            'name_ru'       => $this->name_ru,
            'name_uk'       => $this->name_uk,
            'city'          => $this->city,
            'region2'       => $region2_m->id,
            'region1'       => $region1_m->id,
            'country'       => $country_m->id,
            'index'         => $this->index
        ];
    }

    /**
     * Получить массив фильтров
     * @return array|null
     */
    public function getFilters() {

        $urlRegion1 = Url::to(['dictionaries/list-region/get-list', 'level' => 1]);
        $urlRegion2 = Url::to(['dictionaries/list-region/get-list', 'level' => 2]);
        $urlCountries = Url::to(['dictionaries/country/get-list']);
        $urlCities = Url::to(['dictionaries/list-city/get-list']);

        $wt = $this->tableName();

        return  [
            ['id' => 'f_warehouse_state', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getStateList(true),
                'operation' => '=', 'field' => $wt . '.state', 'label' => Yii::t('app', 'State') . ':'],

            ['id' => 'f_warehouse_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language,
                'label' => Yii::t('app', 'Language') . ':', 'items' => Langs::$Names, 'lang_selector' => true],

            ['id' => 'f_warehouse_id', 'field' => $wt . '.id', 'operation' => '=', 'label' => $this->getAttributeLabel('id').':'],

            ['id' => 'f_warehouse_index', 'field' => $wt . '.index', 'operation' => '=', 'label' => Yii::t('address', 'Index') . ':'],

            [
                'id' => 'f_warehouse_country', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.country',
                'items' => Country::getListFast('name_short', false, Yii::$app->language),
                'lang_dependency' => true, 'url' => $urlCountries,
                'label' => Yii::t('address', 'Country') . ':',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'warehouse_full_name').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'filterwarehouse_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],
            [
                'id' => 'f_warehouse_region1', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.parent_id',
                'items' => ListRegion::getList(), 'lang_dependency' => true, 'url' => $urlRegion1,
                'label' => Yii::t('address', '1-st level region') . ':',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'warehouse_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/list-region/index']),
                'select_tab_uniqname' => 'filterwarehouse_region1',
                'view_tab_title' => Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/list-region/view']),
                'view_tab_uniqname' => 'region_{0}',
            ],
            [
                'id' => 'f_warehouse_region2', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'region',
                'items' => ListRegion::getList('name', true, null, 2), 'lang_dependency' => true, 'url' => $urlRegion2,
                'label' => Yii::t('address', '2-nd lvl region') . ':',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'warehouse_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/list-region/index2']),
                'select_tab_uniqname' => 'filterwarehouse_region2',
                'view_tab_title' => Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/list-region/view2']),
                'view_tab_uniqname' => 'region2_{0}',
            ],
            [
                'id' => 'f_warehouse_city', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'st.city',
                'items' => ListCity::getList(), 'lang_dependency' => true, 'url' => $urlCities,
                'label' => Yii::t('address', 'City') . ':',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'warehouse_full_name').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname' => 'filterwarehouse_city',
                'view_tab_title' => Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname' => 'city_{0}',
            ],

            ['id' => 'f_warehouse_type', 'type' => self::FILTER_DROPDOWN, 'value' => '',
                'items' => ListWarehouseType::getList(true), 'operation' => '=', 'field' => 'warehouse_type'],

            ['id' => 'f_warehouse_subtype', 'type' => self::FILTER_DROPDOWN, 'value' => '',
                'items' => ListWarehouseSubtype::getList(true), 'operation' => '=', 'field' => 'warehouse_subtype'],

            ['id' => 'f_warehouse_name', 'field' => $wt . '.name', 'operation' => 'starts', 'lang_field' => true,
                'label' => $this->getAttributeLabel('name') . ':'],

            ['id' => 'f_warehouse_visible', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getVisibilityList(true),
                'operation' => '=', 'field' => $wt . '.visible', 'label' => Yii::t('app', 'Visible') . ':'],

            ['id' => 'f_warehouse_css', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getYesNoList(true),
                'operation' => '=', 'field' => 'is_css'],

            ['id' => 'f_warehouse_transit', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getYesNoList(true),
                'operation' => '=', 'field' => 'is_transit'],

            ['id' => 'f_warehouse_internal', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getYesNoList(true),
                'operation' => '=', 'field' => 'is_internal'],

            ['id' => 'f_warehouse_plocker', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getYesNoList(true),
                'operation' => '=', 'field' => 'is_plocker'],

            ['id' => 'f_warehouse_terminal', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getYesNoList(true),
                'operation' => '=', 'field' => 'is_terminal'],

            ['id' => 'f_warehouse_max_weight', 'field' => $wt . '.max_fact_weight', 'operation' => '>=',
            'label' => $this->getAttributeLabel('max_fact_weight') . ':'],
        ];
    }

    public function getAFilters() {

        $dimensionArr = [
            null => null,
            'max_lenght' => Yii::t('warehouse', 'Длина'),
            'max_width' => Yii::t('warehouse', 'Ширина'),
            'max_height' => Yii::t('warehouse', 'Высота'),
        ];

        return [
            ['id' => 'title', 'label' => Yii::t('warehouse', 'График работы') . ':',],

            ['id' => 'af_schedule_type', 'field' => 'sch.warehouse_schedule_type', 'operation' => '=', 'type' => self::FILTER_DROPDOWN,
                'items' => ListWarehouseScheduleType::getList(true)],

            ['id' => 'af_day_of_week', 'field' => 'sch.dayofweek', 'operation' => '=', 'type' => self::FILTER_DROPDOWN,
                'items' => ListDayofweek::getList(true)],

            ['id' => 'af_time_begin', 'field' => 'sch.time_begin', 'operation' => '<=', 'type' => self::FILTER_MASKEDEDIT,
                'mask' => '99:99'],

            ['id' => 'af_time_end', 'field' => 'sch.time_end', 'operation' => '>=', 'type' => self::FILTER_MASKEDEDIT,
                'mask' => '99:99'],

            ['id' => 'hr'],

            ['id' => 'title', 'label' => Yii::t('warehouse', 'Ограничения по габаримам') . ':',],

            ['id' => 'af_dimension_type', 'operation' => '=', 'type' => self::FILTER_DROPDOWN, 'items' => $dimensionArr,
                'label' => Yii::t('warehouse', 'Вид габарита').':'],

            ['id' => 'af_dimension_value', 'operation' => '<=', 'label' => Yii::t('warehouse', 'Ограничения по грабаритам, м').':']
        ];
    }

    public function validateCameras() {

        if ($this->camerasInput) {
            foreach ($this->camerasInput as $camera) {
                if (!$camera->validate()) {
                    $this->addErrors($camera->errors);
                    break;
                }
            }
        }
    }

    public function getCameras() {

        if (!$this->camerasInput)
            $this->camerasInput = $this->hasMany(ListWarehouseCamera::className(), ['warehouse' => 'id'])->all();

        return $this->camerasInput;
    }

    public function setCameras($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->camerasInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        for ($i = 1; $i <= count($value); $i++) {

            if (!isset($value[$i]))
                continue;

            // новая запись
            $camera = new ListWarehouseCamera();

            // или существующая
            if ($value[$i]['id'] > 0)
                $camera = ListWarehouseCamera::findOne(['id' => $value[$i]['id']]);


            $camera->load($value, $i);
            $this->camerasInput[] = $camera;
        }
    }

    public function saveCameras() {

        $cameraInBase = ListWarehouseCamera::findAll(['warehouse' => $this->id]);
        $cameraSaved = [];

        // обход по новым записям
        if ($this->camerasInput)
            foreach ($this->camerasInput as $camera) {

                $camera->warehouse = $this->id;
                $camera->save();
                $cameraSaved[] = $camera->id;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$this->camerasInputAppend)
        foreach ($cameraInBase as $camera){
            if (!in_array($camera->id, $cameraSaved)){
                $camera->delete();
            }
        }
    }

    public function getGridOperations() {
        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app', 'Copy'),
        ];
    }

    public function getGridOperationsOptions() {
        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']), 'separator_before' => true, 'tab_name_sufix' => 'copy'],
        ];
    }
    
    /** Получение списка отделений, в которых есть сотрудники
     * @param string $field
     * @param type $empty
     * @param type $lang
     * @param type $andWhere условие для сотрудников
     * @return array
     */
    public static function getListByEmplyee($field = 'name', $empty = false, $lang = null, $andWhere = '1=1') {

        if (!$lang)
            $lang = Yii::$app->language;

        $eployees = Employee::find()
                ->select('warehouse_id')
                ->where('visible = ' . self::VISIBLE . ' AND state != ' . CommonModel::STATE_DELETED)
                ->andWhere('warehouse_id = '.self::tableName().'.id');


        $models = self::find()
                ->where('visible = ' . self::VISIBLE . ' AND state != ' . CommonModel::STATE_DELETED)
                ->andWhere(['exists', $eployees])
                ->andWhere($andWhere)
                ->all();

        if ($field == 'name') {
            $field = $field . '_' . $lang;
        }
        $result = ArrayHelper::map($models, 'id', $field);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

}



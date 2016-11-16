<?php

/**
 * В файле описан класс модели населенного пункта
 *
 * @author Мельник И.А.
 * @category Адресная система
 */

namespace app\models\dictionaries\address;

use app\models\common\CommonModel;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\employee\Employee;
use app\models\common\Langs;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\dictionaries\tariff\ListTariffZone;

/**
 * Модель населенного пункта
 *
 * @property string $id Код
 * @property string $name_en Наименование англ.
 * @property string $name_ru Наименование рус.
 * @property string $name_uk Наименование укр.
 * @property string $city_type Ссылка на справочник типов нас.пунктов
 * @property string $region Ссылка на справочник регионов
 * @property integer $state Состояние
 * @property string $delivery_from Доставляется из
 * @property string $director_of_city Директор населенного пункта
 * @property string $tariff_zone Тарифная зона населённого пункта
 * @property string $addition_info Дополнительная информация
 * @property integer $visible Доступность выбора
 * @property string $begin_per_indexes Начало периода индексов
 * @property string $end_per_indexes Конец периода индексов
 * @property integer $npi_branch Филиал НПИ
 * @property string $latitude Координата latitude
 * @property string $longitude Координата longitude
 * 
 * @property ListCityType $cityType Тип города
 * @property ListRegion $regionModel Регион
 * @property ListCity $deliveryFrom Модель доставки из
 * @property ListCity[] $listCities Масив городов
 * @property Employee $directorOfCity Директор населенного пункта
 * @property ListTariffZone $tariffZone Тарифная зона населённого пункта
 * @property ListCityContact $listCityContact Контактая информация нас. пункта
 * @property ListCity[] $routes Маршруты нас. пункта
 * @property ListCityRoute[] $listCityRoutes1 Маршруты используемые в нас.пункте
 * @property ListCityRoute[] $listCityRoutes2 Маршруты в которых используется данный нас.пункт
 * @property ListCityScheduleReception[] $listCityScheduleReceptions График приема
 * @property ListStreet[] $listStreets Массив улиц нас. пункта
 * @property mixed npi_branchText Признак ветки НПИ
 * @property mixed name Имя нас. пункта в зависимости от языка системы
 * @property mixed availableCities Массив доступных для выбора нас.пунктов
 * @property mixed availableRegions2 Массив доступных для выбора регионов 2го уровня
 * @property mixed availableRegions1 Массив доступных для выбора регионов 1го уровня
 * @property mixed availableStreets Массив доступных улиц
 * @property string addressEn Полный адрес на анл. языке
 * @property string addressUk Полный адрес на укр. языке
 * @property string addressRu Полный адрес на рус. языке
 * @property string addressShortEn Сокращенный адрес на анл. языке
 * @property string addressShortUk Сокращенный адрес на укр. языке
 * @property string addressShortRu Сокращенный адрес на рус. языке
 */
class ListCity extends CommonModel {

    /**
     * @var ListCityContact Временная переменная для хранения не сохраненной в базе контактой информации
     */
    public $cityContactInput;

    /**
     * @var ListRegion Временная переменная для хранения не сохраненного в базе региона
     */
    public $regionInput;

    /**
     * @var ListCityScheduleReception[] Временная переменная для хранения не сохраненного в базе графика приема
     */
    public $listCityScheduleReceptionsInput;
    public $listCityScheduleReceptionsInputAppend;

    /**
     * @var ListCity[] Временная переменная для хранения не сохраненных в базе маршрутов
     */
    public $routesInput;
    public $routesInputAppend;

    /**
     * Получение имени таблицы в БД
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%list_city}}';
    }

    /**
     * Правила полей модели
     * @inheritdoc
     */
    public function rules() {
        return array_merge(parent::rules(), [
            [['name_en', 'name_ru', 'name_uk', 'city_type', 'region'], 'required'],
            [['city_type', 'region', 'state', 'delivery_from', 'director_of_city', 'tariff_zone', 'visible', 'npi_branch'], 'integer'],
            [['name_en', 'name_ru', 'name_uk', 'latitude', 'longitude'], 'string', 'max' => 50],
            [['addition_info'], 'string', 'max' => 100],
            [['begin_per_indexes', 'end_per_indexes'], 'string', 'max' => 20],
            ['listCityContact', 'safe'],
            ['listCityScheduleReceptions', 'validateListCityScheduleReceptions'],
            ['routes', 'validateRoutes'],
        ]);
    }

    /**
     * Надписи полей модели
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('address', 'ID'),
            'name' => Yii::t('address', 'Settlement name'),
            'name_en' => Yii::t('address', 'Name En'),
            'name_ru' => Yii::t('address', 'Name Ru'),
            'name_uk' => Yii::t('address', 'Name Uk'),
            'city_type' => Yii::t('address', 'City Type'),
            'region' => Yii::t('address', 'Region'),
            'state' => Yii::t('address', 'State'),
            'delivery_from' => Yii::t('address', 'Delivery From'),
            'director_of_city' => Yii::t('address', 'Director Of City'),
            'addition_info' => Yii::t('address', 'Addition Info'),
            'latitude' => Yii::t('address', 'Latitude'),
            'longitude' => Yii::t('address', 'Longitude'),
            'visible' => Yii::t('address', 'Visible'),
            'begin_per_indexes' => Yii::t('address', 'Begin Per Indexes'),
            'end_per_indexes' => Yii::t('address', 'End Per Indexes'),
            'npi_branch' => Yii::t('address', 'Npi Branch'),
            'operation' => Yii::t('app', 'Operation'),
            'tariff_zone' => Yii::t('address', 'Tariff zone'),

        ];
    }

    /**
     * Получение модели типа населенного поля
     * @return \yii\db\ActiveQuery
     */
    public function getCityType() {
        return $this->hasOne(ListCityType::className(), ['id' => 'city_type']);
    }

    /**
     * Получение модели региона
     * @return \yii\db\ActiveQuery
     */
    public function getRegionModel() {
        if (!$this->regionInput)
            $this->regionInput = $this->hasOne(ListRegion::className(), ['id' => 'region'])->one();

        if (!$this->regionInput)
            $this->regionInput = new ListRegion();

        return $this->regionInput;
    }

    /**
     * Установка значения региона
     * @param $value
     */
    public function setRegionModel($value) {
        $this->regionInput->load($value, '');
    }

    /**
     * Получение модели для поля "Доставляется из"
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryFrom() {
        return $this->hasOne(ListCity::className(), ['id' => 'delivery_from']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountryModel() {
        return $this->hasOne(Country::className(), ['id' => 'country']);
    }

    /**
     * Получение моделеий нас. пунктов для которых доставляется из текущего нас. пункта
     * @return \yii\db\ActiveQuery
     */
    public function getListCities() {
        return $this->hasMany(ListCity::className(), ['delivery_from' => 'id']);
    }

    /**
     * Получение модели директора нас. пункта
     * @return \yii\db\ActiveQuery
     */
    public function getDirectorOfCity() {
        return $this->hasOne(Employee::className(), ['id' => 'director_of_city']);
    }

    /**
     * Получение модели тарифконй зоны
     * @return \yii\db\ActiveQuery
     */
    public function getTariffZone() {
        return $this->hasOne(ListTariffZone::className(), ['id' => 'tariff_zone']);
    }

    /**
     * Получение модели контактой информации нас. пункта
     * @return \yii\db\ActiveQuery
     */
    public function getListCityContact() {
        if (!$this->cityContactInput)
            $this->cityContactInput = $this->hasOne(ListCityContact::className(), ['city' => 'id'])->one();

        if (!$this->cityContactInput)
            $this->cityContactInput = new ListCityContact();

        return $this->cityContactInput;
    }

    /**
     * Установка значекния контактой информации нас. пункта
     * @param $value
     */
    public function setListCityContact($value) {
        $this->cityContactInput = $this->listCityContact;
        $this->cityContactInput->load($value, '');
    }

    /**
     * Проверка введенных данных контактой информации
     */
    public function validateListCityScheduleReceptions() {

        if ($this->listCityScheduleReceptionsInput)
            foreach ($this->listCityScheduleReceptionsInput as $schedule) {
                if (!$schedule->validate()) {
                    $this->addErrors($schedule->errors);
                    break;
                }
            }
    }

    /**
     * Получение графиков приема
     * @return ListCityScheduleReception[]|array|\yii\db\ActiveRecord[]
     */
    public function getListCityScheduleReceptions() {

        if (!$this->listCityScheduleReceptionsInput)
            $this->listCityScheduleReceptionsInput = $this->hasMany(ListCityScheduleReception::className(), ['city' => 'id'])->all();

        if (!$this->listCityScheduleReceptionsInput) {

            $days = ListDayofweek::getList();
            $types = ListScheduleType::getList();

            foreach (array_keys($days) as $day) {
                foreach (array_keys($types) as $type) {

                    $schedule = new ListCityScheduleReception();
                    $schedule->schedule_type = $type;
                    $schedule->dayofweek = $day;
                    $schedule->city = $this->id;
                    $schedule->time_begin = '00:00';
                    $schedule->time_end = '00:00';
                    $this->listCityScheduleReceptionsInput[] = $schedule;
                }
            }
        }

        return $this->listCityScheduleReceptionsInput;
    }

    /**
     * Установка значений графиков приема
     * @param $value
     */
    public function setListCityScheduleReceptions($value) {


        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->listCityScheduleReceptionsInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        for ($i = 1; $i <= count($value); $i++) {

            if (!isset($value[$i]))
                continue;

            // новая запись
            $schedule = new ListCityScheduleReception();

            // или существующая
            if ($value[$i]['id'] > 0)
                $schedule = ListCityScheduleReception::findOne(['id' => $value[$i]['id']]);


            $schedule->load($value, $i);
            $this->listCityScheduleReceptionsInput[] = $schedule;
        }
    }

    /**
     * Сохранение значений графиков приема
     * @param $value
     */
    public function saveListCityScheduleReceptions() {

        $scheduleInBase = ListCityScheduleReception::findAll(['city' => $this->id]);
        $scheduleSaved = [];


        // обход по новым записям
        if ($this->listCityScheduleReceptionsInput)
            foreach ($this->listCityScheduleReceptionsInput as $schedule) {

                $schedule->city = $this->id;
                $schedule->save();
                $scheduleSaved[] = $schedule->id;
            }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$this->listCityScheduleReceptionsInputAppend)
        foreach ($scheduleInBase as $schedule) {
            if (!in_array($schedule->id, $scheduleSaved)) {
                $schedule->delete();
            }
        }
    }

    /**
     * Получение маршрутов нас. пункта
     * @return \yii\db\ActiveQuery
     */
    public function getListCityRoutes1() {
        return $this->hasMany(ListCityRoute::className(), ['city1' => 'id']);
    }

    /**
     * Получение маршрутов где используется текущий нас.пункт
     * @return \yii\db\ActiveQuery
     */
    public function getListCityRoutes2() {
        return $this->hasMany(ListCityRoute::className(), ['city2' => 'id']);
    }

    /**
     * Получение признака ветки НПИ в текстовом виде
     * @return mixed
     */
    public function getNpi_branchText() {
        return $this->getYesNoList()[$this->npi_branch];
    }

    /**
     * Получение масива улиц
     * @return \yii\db\ActiveQuery
     */
    public function getListStreets() {
        return $this->hasMany(ListStreet::className(), ['city' => 'id']);
    }

    /**
     * Получение ассоциативного массива городов
     * @param string $field name
     * @param bool $empty false
     * @param string $lang null
     * @param string $andWhere 1=1
     * @return array id=field
     */
    public static function getList($field = 'name', $empty = false, $lang = null, $andWhere = '1=1') {

        if (!$lang)
            $lang = Yii::$app->language;

        if ($field == 'name') {
            $field = $field . '_' . $lang;
        }

        $result = (new Query())
            ->select(['id', $field])
            ->from(self::tableName())
            ->where('visible = ' . self::VISIBLE . ' AND state != ' . CommonModel::STATE_DELETED)
            ->andWhere($andWhere)
            ->all();

        $result = ArrayHelper::map($result, 'id', $field);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    /**
     * Получение полей модели в виде массива
     * @param string $prefix
     * @return array
     */
    public function toJson($prefix = '', $withSelectEntityWidget = true) {
        $result = [
            $prefix . 'id' => $this->id,
            $prefix . 'city' => $this->id,
            $prefix . 'city_type' => $this->cityType->name,
            $prefix . 'name' => $this->name,
            $prefix . 'name_ru' => $this->name_ru,
            $prefix . 'name_en' => $this->name_en,
            $prefix . 'name_uk' => $this->name_uk,
            $prefix . 'region' => $this->regionModel->name,
            $prefix . 'region_id' => $this->regionModel->id,
            $prefix . 'regionType' => $this->regionModel->regionType->name,
            $prefix . 'region1' => $this->regionModel->parent->name,
            $prefix . 'region1_id' => $this->regionModel->parent->id,
            $prefix . 'region1Type' => $this->regionModel->parent->regionType->name,
            $prefix . 'country' => $this->regionModel->countryModel->nameShort,
            $prefix . 'country_id' => $this->regionModel->countryModel->id,
            $prefix . 'state' => $this->state,
            $prefix . 'visibilityText' => $this->visibilityText,
            $prefix . 'director_of_city' => $this->directorOfCity->name,
            $prefix . 'tariff_zone' => $this->tariff_zone->name_en,
            $prefix . 'npi_branchText' => $this->npi_branchText,
            $prefix . 'tariffZone' => $this->tariffZone->{'name_' . Yii::$app->language},
            $prefix . 'begin_per_indexes' => $this->begin_per_indexes,
            $prefix . 'end_per_indexes' => $this->end_per_indexes,
        ];
        
        if ($withSelectEntityWidget) {
            $result[$prefix . 'uniq_id'] = $this->getUniqueId();
            $result[$prefix . 'name_select_entity'] = $this->getSelectEntityWidget();
        }

        return $result;
    }
    
    public function getSelectEntityWidget()
    {
        $uniqId = $this->getUniqueId();
        $selectEntityWidgetName = 'select_entity_' . $uniqId;
        
        return 
            '<div class="select-entity-widget-container">' .
                '<input type="hidden" id="' . $uniqId . '_route_id" value="' . $this->id . '" class="change-route-id-trigger">' .
                '<input type="hidden" value="' . $uniqId . '" class="entity-uniq-id">' .
                '<span id="' . $uniqId . '_route_name" class="route-name">' . $this->name . '</span>' .
                '<div class="choose_btn">' .
                    \app\widgets\SelectEntityWidget::widget([
                        'name' => $selectEntityWidgetName,
                        'model' => $this,
                        'parent_selector' => '#direction_tab',
                        'linked_field'=>$uniqId . '_route_id', // связанное поле в которое будет записано значение при выборе
                        'with_creation'=>true,

                        'select_tab_title'=>Yii::t('tab_title', 'City routes').': '.Yii::t('tab_title', 'search_command'), // надпись таба при выборе
                        'select_url'=>Url::to(['dictionaries/list-city/index-select']), // урл таба при выборе
                        'select_tab_uniqname'=>'findcityroutes_' . $uniqId, // уникальное имя таба при выборе

                        'view_tab_title'=>Yii::t('tab_title', 'city_name').' {0} '.Yii::t('tab_title', 'view_command'), // надпись таба при просмотре
                        'view_url'=>Url::to(['dictionaries/list-city/view']), //  урл таба при просмотре выбранной сущности
                        'view_tab_uniqname'=>'cityroute_' . $uniqId, // уникальное имя таба при просмотре. вместо {0} будет подставлен id
                    ]) .
                '</div>' .
            '</div>';
    }

    /**
     * Получение фильров модели
     * @return array
     */
    public function getFilters() {

        $urlRegion1 = Url::to(['dictionaries/list-region/get-list', 'level' => 1]);
        $urlRegion2 = Url::to(['dictionaries/list-region/get-list', 'level' => 2]);
        $urlCountries = Url::to(['dictionaries/country/get-list']);

        return [

            ['id' => 'f_city_state', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getStateList(true),
                'operation' => '=', 'field' => $this->tableName() . '.state', 'label' => Yii::t('app', 'Visible') . ':'],
            ['id' => 'f_city_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language, 'label' => Yii::t('app', 'Language') . ':',
                'items' => Langs::$Names, 'lang_selector' => true],
            ['id' => 'f_city_id', 'field' => ListCity::tableName() . '.id', 'operation' => '=', 'label' => $this->getAttributeLabel('id') . ':'],
            ['id' => 'f_city_index', 'label' => Yii::t('address', 'Index')],
            [
                'id' => 'f_city_country', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.country',
                'items' => Country::getListFast('name_short', false, Yii::$app->language),
                'lang_dependency' => true, 'url' => $urlCountries,
                'label' => Yii::t('address', 'Country name (short)') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'city_full_name').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/country/index']),
                'select_tab_uniqname'=>'filtercity_country',
                'view_tab_title'=>Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/country/view']),
                'view_tab_uniqname'=>'country_{0}',
            ],
            [
                'id' => 'f_city_region1', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.parent_id',
                'items' => ListRegion::getList(), 'lang_dependency' => true, 'url' => $urlRegion1,
                'label' => Yii::t('address', '1-st level region') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'city_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index']),
                'select_tab_uniqname'=>'filtercity_region1',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view']),
                'view_tab_uniqname'=>'region_{0}',
            ],
            [
                'id' => 'f_city_region2', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'region',
                'items' => ListRegion::getList('name', true, null, 2), 'lang_dependency' => true, 'url' => $urlRegion2,
                'label' => Yii::t('address', '2-nd lvl region') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'city_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index2']),
                'select_tab_uniqname'=>'filtercity_region2',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view2']),
                'view_tab_uniqname'=>'region2_{0}',
            ],
            ['id' => 'f_city_name', 'operation' => 'starts', 'field' => $this->tableName() . '.name', 'lang_field' => true, 'label' => Yii::t('address', 'City name') . ':'],
            ['id' => 'f_city_type', 'type' => self::FILTER_DROPDOWN, 'value' => '',
                'items' => ListCityType::getList('name', true), 'operation' => '=', 'field' => 'city_type'],
            ['id' => 'f_city_visible', 'type' => self::FILTER_DROPDOWN, 'value' => '', 'items' => $this->getVisibilityList(true),
                'operation' => '=', 'field' => $this->tableName() . '.visible', 'label' => Yii::t('app', 'Visible') . ':'],
            ['id' => 'f_city_npi_branch', 'type' => self::FILTER_DROPDOWN, 'value' => '',
                'items' => $this->getYesNoList(true), 'operation' => '=', 'field' => 'npi_branch'],
            ['id' => 'f_tariff_zone', 'type' => self::FILTER_SELECT2, 'value' => '',
                'items' => ListTariffZone::getList(null, true), 'operation' => '=', 'field' => 'tariff_zone'],
        ];
    }

    /**
     * Метод формирования значений по умолчанию для новой модели
     * @param $params
     * @return null
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyCity($params);
    }

    public function copyCity($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $city = ListCity::findOne(['id' => $params['id']]);
            if($city) {
                $this->attributes = $city->getAttributes();
                $this->cityContactInput = $city->listCityContact;
                $this->region = $city->region;
                $this->listCityScheduleReceptionsInput = $city->listCityScheduleReceptions;
            }
        }
    }
    
    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            $this->cityContactInput->city = $this->id;
            $this->cityContactInput->save();

            $this->saveListCityScheduleReceptions();
            $this->saveRoutes();
        }

        $this->operation = self::OPERATION_NONE;
    }

    /**
     * Получение маршрутов
     * @return ListCity[]
     */
    public function getRoutes()
    {
        if (!$this->routesInput) {
            if ($this->listCityRoutes1)
                foreach ($this->listCityRoutes1 as $cityRoute)
                    $this->routesInput[] = $cityRoute->city2Model;
        }

        return $this->routesInput;
    }

    /**
     * Проверка введенных маршрутов
     */
    public function validateRoutes()
    {
        $uniq_cityes = [];

        if ($this->routesInput) {
            foreach ($this->routesInput as $city) {

                if (in_array($city->id, $uniq_cityes)) {
                    $this->addError('routes', Yii::t('app', "Dubplicate city route. City '{city_name}'", [
                        'city_name' => $city->name,
                    ]));
                    break;
                }
                
                if ($city->id == $this->id) {
                    $this->addError('routes', Yii::t('app', 'You can not add self city as route'));
                    break;
                }

                $uniq_cityes[] = $city->id;
            }
        }
    }

    /**
     * Установка введенных маршрутов
     * @param array $routes
     */
    public function setRoutes($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        $this->routesInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        for ($i = 1; $i <= count($value); $i++) {
            if (!isset($value[$i]))
                continue;
            $city = self::findOne(['id' => $value[$i]['city']]);
            if ($city)
                $this->routesInput[] = $city;
        }
    }

    /**
     * Сохранение маршрутов
     */
    public function saveRoutes()
    {
        $routeInBase = $this->listCityRoutes2;
        $routeSaved = [];

        if ($this->routesInput) {
            foreach ($this->routesInput as $routeInput) {

                if (!ListCityRoute::findOne([$this->id, $routeInput->id])) {
                    $route = new ListCityRoute();
                    $route->city1 = $this->id;
                    $route->city2 = $routeInput->id;
                    $route->save();
                    $routeSaved[] = $routeInput->id;
                }
            }
        }

        // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
        if (!$this->routesInputAppend) {
            foreach ($routeInBase as $route) {
                if (!in_array($route->id, $routeSaved))
                    $route->delete();
            }
        }
    }

    /**
     * Получение доступных маршрутов
     * @return array
     */
    public function getAvailableRoutes() {

        $where = '1=1';
        if ($this->id)
            $where = 'id <> ' . $this->id;

        return self::getList('name', false, null, $where);
    }

    /**
     * Получение доступных регионов 2го уровня
     * @return array
     */
    public function getAvailableRegions2() {
        if ($this->regionModel->parent_id)
            return ListRegion::getList('name', false, null, 2, 'parent_id = ' . $this->regionModel->parent_id);

        return [];
    }

    /**
     * Получение доступных нас. пунктов
     * @return array
     */
    public function getAvailableCities() {
        if ($this->regionModel->parent_id)
            return ListCity::getList('name', true, null, $andWhere = 'region=' . $this->region);
        return [];
    }

    /**
     * Получение доступных регионов 1го уровня
     * @return array
     */
    public function getAvailableRegions1() {

        if ($this->regionModel->parent->country)
            return ListRegion::getList('name', false, null, 1, 'country = ' . $this->regionModel->parent->country);

        return [];
    }

    /**
     * Получение доступных улиц
     * @return array
     */
    public function getAvailableStreets() {
        return ListStreet::getList('name', false, null, 'city = ' . $this->id);
    }

    /**
     * Получить полный адрес на английском
     * @return string
     */
    public function getAddressEn() {

        return $this->regionModel->parent->countryModel->nameShortEn . ' '
                . $this->regionModel->parent->name_en . ' '
                . $this->regionModel->parent->regionType->name_en . ' '
                . $this->regionModel->name_en . ' '
                . $this->regionModel->regionType->name_en . ' '
                . $this->cityType->name_en . ' '
                . $this->name_en;
    }

    /**
     * Получить полный адрес на украинском
     * @return string
     */
    public function getAddressUk() {

        return $this->regionModel->parent->countryModel->nameShortUk . ' '
                . $this->regionModel->parent->name_uk . ' '
                . $this->regionModel->parent->regionType->name_uk . ' '
                . $this->regionModel->name_uk . ' '
                . $this->regionModel->regionType->name_uk . ' '
                . $this->cityType->name_uk . ' '
                . $this->name_uk;
    }

    /**
     * Получить полный адрес на русском
     * @return string
     */
    public function getAddressRu() {

        return $this->regionModel->parent->countryModel->nameShortRu . ' '
                . $this->regionModel->parent->name_ru . ' '
                . $this->regionModel->parent->regionType->name_ru . ' '
                . $this->regionModel->name_ru . ' '
                . $this->regionModel->regionType->name_ru . ' '
                . $this->cityType->name_ru . ' '
                . $this->name_ru;
    }

    /**
     * Получение списка id городов по регионам 1 или 2 уровня
     * @param int $region_id id региона
     * @return array
     */
    public function getByRegion($region_id) {
        $cities = $cities_list  = $regions = $regions1 = $regions2 = [];
        $regions = ListRegion::getList('id', false, null, 2, 'id=' . (int) $region_id);
        if (empty($regions)) {
            $regions2 = ListRegion::getList('id', false, null, 2, 'parent_id=' . (int) $region_id);
            $regions1 = ListRegion::getList('id', false, null, 1, 'id=' . (int) $region_id);
            $regions = array_merge($regions1, $regions2);
        }
        if (!empty($regions)) {
            foreach ($regions as $id) {
                $cities_list[] = ListCity::getList('id', false, null, 'region=' . (int) $id);
            }
        }
        foreach($cities_list as $clist){
            $cities = array_merge($cities, $clist);
        }
        return $cities;
    }
    
    /**
     * Получение списка id городов по странам
     * @param int $country_id id страны
     * @return array
     */
    public function getByCountry($country_id) {
        $cities = $cities_list = $regions = $regions1 = $regions2 = [];
        $regions2 = ListRegion::getList('id', false, null, 2, 'country=' . (int) $country_id);
        $regions1 = ListRegion::getList('id', false, null, 1, 'country=' . (int) $country_id);
        $regions = array_merge($regions1, $regions2);
        if (!empty($regions)) {
            foreach ($regions as $id) {
                $cities_list[] = ListCity::getList('id', false, null, 'region=' . (int) $id);
            }
        }
        foreach($cities_list as $clist){
            $cities = array_merge($cities, $clist);
        }
        return $cities;
    }

    /**
     * Получение имени в зависимости от языка системы
     * @param null $lang
     * @param bool $typeCity
     * @return mixed
     */
    public function getName($lang=null,$typeCity=false){
        if (!$lang){
            $lang = Yii::$app->language;
        }

        $type = '';
        if($typeCity) {
            $type = $this->cityType->{"name_short_$lang"};
        }

        return $type.$this->{"name_$lang"};
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

}

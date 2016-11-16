<?php

namespace app\models\dictionaries\employee;

use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\address\ListRegion;
use app\models\common\Langs;
use app\models\dictionaries\warehouse\ListWarehouse;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\Translator;
use app\models\common\CommonModel;
use app\models\dictionaries\country\Country;
use yii\helpers\Url;

/**
 * Модель сотрудника
 * @author Richok FG
 * @category employee
 *
 * @property string $id
 * @property string $job_position_id
 * @property string $email
 * @property string $work_phone_number
 * @property string $personal_phone_number
 * @property string $state
 * @property integer $visible
 * @property string $employee_status_id
 * @property string $city
 * @property string $departament
 * @property string $country_id
 * @property Country $country
 * @property integer $city_id
 * @property ListCity $cityModel
 * @property integer $warehouse_id
 * @property ListWarehouse $warehouseModel
 * @property integer $org_kind
 *
 * @property JobPosition $jobPosition
 * @property EmployeeStatus $employeeStatus
 * @property ListOrgKind $listOrgKind
 * @property mixed surnameFullUk
 * @property mixed surnameFullEn
 * @property mixed surnameFullRu
 * @property mixed availableCities
 * @property mixed availableWarehouses
 * @property mixed employeeStatusColor
 */


class Employee extends CommonModel
{

    // поля из таблицы переводов
    public $surname;
    public $surnames;
    public $name;
    public $names;
    public $secondName;
    public $secondNames;
    public $surnameFull;
    public $surnamesFull;
    public $surnameShort;
    public $surnamesShort;


    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%employee}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return array_merge(parent::rules(),
            [
                [['surnameEn', 'surnameUk', 'surnameRu', 'nameEn', 'nameUk', 'nameRu', 'org_kind'], 'required'],
                [['surnameFullEn', 'surnameFullUk', 'surnameFullRu', 'surnameShortEn', 'surnameShortUk', 'surnameShortRu'], 'required'],
                [['surnameEn', 'surnameUk', 'surnameRu', 'nameEn', 'nameUk', 'nameRu', 'secondNameRu', 'secondNameUk', 'secondNameRu'], 'string', 'max' => 50],
                [['surnameFullEn', 'surnameFullUk', 'surnameFullRu'], 'string', 'max' => 200],
                [['surnameShortEn', 'surnameShortUk', 'surnameShortRu'], 'string', 'max' => 100],
                [['surnameEn', 'nameEn', 'secondNameEn', 'surnameFullEn', 'surnameShortEn'],
                    'match', 'pattern' => '/^[\w\s\W0-9]+[^А-яҐЄЇІґєїіЪЫЁЭъыёэ!@#\$%^*?=]+$/u'],
                [['surnameUk', 'nameUk', 'secondNameUk', 'surnameFullUk', 'surnameShortUk'],
                    'match', 'pattern' => '/^[А-яҐЄЇІіґєї\s\W\[\]0-9]+[^A-zЪЫЁЭъыёэ!@#\$%\^\*\?=]+$/u'],
                [['surnameRu', 'nameRu', 'secondNameRu', 'surnameFullRu', 'surnameShortRu'],
                    'match', 'pattern' => '/^[А-яЁё\s\W\[\]0-9]+[^A-zҐЄЇІґєїі!@#\$%\^\*\?=]+$/u'],
                [['job_position_id', 'state', 'employee_status_id', 'visible'], 'required'],
                [['job_position_id', 'visible', 'state', 'employee_status_id','country_id','city_id', 'warehouse_id', 'org_kind'], 'integer'],
                [['email'], 'string', 'max' => 100],
                [['work_phone_number', 'personal_phone_number'], 'string', 'max' => 50],
                [['work_phone_number', 'personal_phone_number'], 'match', 'pattern' => '/^[0-9()\s+-]+$/'],
        ]);
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('employee', 'Employee code'),
            'surname' => Yii::t('employee', 'Surname'),
            'surnameEn' => Yii::t('employee', 'Surname (Eng)'),
            'surnameUk' => Yii::t('employee', 'Surname (Ukr)'),
            'surnameRu' => Yii::t('employee', 'Surname (Rus)'),
            'name' => Yii::t('employee', 'Name'),
            'nameEn' => Yii::t('employee', 'Name (Eng)'),
            'nameUk' => Yii::t('employee', 'Name (Ukr)'),
            'nameRu' => Yii::t('employee', 'Name (Rus)'),
            'secondName' => Yii::t('employee', 'Second Name'),
            'secondNameEn' => Yii::t('employee', 'Second Name (Eng)'),
            'secondNameUk' => Yii::t('employee', 'Second Name (Ukr)'),
            'secondNameRu' => Yii::t('employee', 'Second Name (Rus)'),
            'surnameFull' => Yii::t('employee', 'Surname Full'),
            'surnameFullEn' => Yii::t('employee', 'Surname Full (Eng)'),
            'surnameFullUk' => Yii::t('employee', 'Surname Full (Ukr)'),
            'surnameFullRu' => Yii::t('employee', 'Surname Full (Rus)'),
            'surnameShort' => Yii::t('employee', 'Surname Short'),
            'surnameShortEn' => Yii::t('employee', 'Surname Short (Eng)'),
            'surnameShortUk' => Yii::t('employee', 'Surname Short (Ukr)'),
            'surnameShortRu' => Yii::t('employee', 'Surname Short (Rus)'),
            'email' => Yii::t('employee', 'Email'),
            'org_kind' => Yii::t('employee', 'Вид организации'),
            'org_kind_name' => Yii::t('employee', 'Вид организации'),
            'work_phone_number' => Yii::t('employee', 'Work Phone Number'),
            'personal_phone_number' => Yii::t('employee', 'Personal Phone Number'),
            'state' => Yii::t('employee', 'State'),
            'visible' => Yii::t('employee', 'Availability of choice'),
            'job_position_id' => Yii::t('employee', 'Job Position'),
            'jobPositionText' => Yii::t('employee', 'Job Position'),
            'employee_status_id' => Yii::t('employee', 'Employee Status'),
            'employeeStatusText' => Yii::t('employee', 'Employee Status'),
            'country_id' => Yii::t('employee', 'Country'),
            'departament'=>Yii::t('employee', 'Departament'),
            'warehouse_id'=>Yii::t('employee', 'Departament'),
            'city'=>Yii::t('employee', 'City'),
            'city_id'=>Yii::t('employee', 'City'),
            'country'=>Yii::t('employee', 'Country'),
            'operation' => Yii::t('app', 'Operation')
        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового сотрудника
     * @param $params
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyEmployee($params);
        $this->org_kind = 2;
    }

    /**
     * Копирование сотрудников
     * @param $params
     */
    public function copyEmployee($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $employee = Employee::findOne(['id' => $params['id']]);
            if($employee) {
                $this->attributes = $employee->getAttributes();
                $this->surnameRu = $employee->surnameRu;
                $this->nameRu = $employee->nameRu;
                $this->secondNameRu = $employee->secondNameRu;
                $this->surnameFullRu = $employee->surnameFullRu;
                $this->surnameShortRu = $employee->surnameShortRu;

                $this->surnameEn = $employee->surnameEn;
                $this->nameEn = $employee->nameEn;
                $this->secondNameEn = $employee->secondNameEn;
                $this->surnameFullEn = $employee->surnameFullEn;
                $this->surnameShortEn = $employee->surnameShortEn;

                $this->surnameUk = $employee->surnameUk;
                $this->nameUk = $employee->nameUk;
                $this->secondNameUk = $employee->secondNameUk;
                $this->surnameFullUk = $employee->surnameFullUk;
                $this->surnameShortUk = $employee->surnameShortUk;
            }
        }
    }

    /**
     * получить фамилию на английском
     */
    public function getSurnameEn() {
        return $this->surnames['en'];
    }

    /**
     * установить фамилию на английском
     */
    public function setSurnameEn($value) {
        $this->surnames['en'] = $value;
    }

    /**
     * получить фамилию на украинском
     */
    public function getSurnameUk() {
        return $this->surnames['uk'];
    }

    /**
     * установить фамилию на украинском
     */
    public function setSurnameUk($value) {
        $this->surnames['uk'] = $value;
    }

    /**
     * получить фамилию на русском
     */
    public function getSurnameRu() {
        return $this->surnames['ru'];
    }

    /**
     * установить фамилию на русском
     */
    public function setSurnameRu($value) {
        $this->surnames['ru'] = $value;
    }

    /**
     * получить имя на английском
     */
    public function getNameEn() {
        return $this->names['en'];
    }

    /**
     * установить имя на английском
     */
    public function setNameEn($value) {
        $this->names['en'] = $value;
    }

    /**
     * получить имя на украиском
     */
    public function getNameUk() {
        return $this->names['uk'];
    }

    /**
     * установить имя на украинском
     */
    public function setNameUk($value) {
        $this->names['uk'] = $value;
    }

    /**
     * получить имя на русском
     */
    public function getNameRu() {
        return $this->names['ru'];
    }

    /**
     * установить имя на русском
     */
    public function setNameRu($value) {
        $this->names['ru'] = $value;
    }

    /**
     * получить отчество на английском
     */
    public function getSecondNameEn() {
        return $this->secondNames['en'];
    }

    /**
     * установить отчество на английском
     */
    public function setSecondNameEn($value) {
        $this->secondNames['en'] = $value;
    }

    /**
     * получить отчество на украиском
     */
    public function getSecondNameUk() {
        return $this->secondNames['uk'];
    }

    /**
     * установить отчество на украинском
     */
    public function setSecondNameUk($value) {
        $this->secondNames['uk'] = $value;
    }

    /**
     * получить отчество на русском
     */
    public function getSecondNameRu() {
        return $this->secondNames['ru'];
    }

    /**
     * установить отчество на русском
     */
    public function setSecondNameRu($value) {
        $this->secondNames['ru'] = $value;
    }

    /**
     * получить ФИО полное на английском
     */
    public function getSurnameFullEn() {
        return $this->surnamesFull['en'];
    }

    /**
     * установить ФИО полное на английском
     */
    public function setSurnameFullEn($value) {
        $this->surnamesFull['en'] = $value;
    }

    /**
     * получить ФИО полное на украинском
     */
    public function getSurnameFullUk() {
        return $this->surnamesFull['uk'];
    }

    /**
     * установить ФИО полное на украинском
     */
    public function setSurnameFullUk($value) {
        $this->surnamesFull['uk'] = $value;
    }

    /**
     * получить ФИО полное на русском
     */
    public function getSurnameFullRu() {
        return $this->surnamesFull['ru'];
    }

    /**
     * установить ФИО полное на русском
     */
    public function setSurnameFullRu($value) {
        $this->surnamesFull['ru'] = $value;
    }

    /**
     * получить ФИО сокращенное на английском
     */
    public function getSurnameShortEn() {
        return $this->surnamesShort['en'];
    }

    /**
     * установить ФИО сокращенное на английском
     */
    public function setSurnameShortEn($value) {
        $this->surnamesShort['en'] = $value;
    }

    /**
     * получить ФИО сокращенное на украинском
     */
    public function getSurnameShortUk() {
        return $this->surnamesShort['uk'];
    }

    /**
     * установить ФИО сокращенное на украинском
     */
    public function setSurnameShortUk($value) {
        $this->surnamesShort['uk'] = $value;
    }

    /**
     * получить ФИО сокращенное на русском
     */
    public function getSurnameShortRu() {
        return $this->surnamesShort['ru'];
    }

    /**
     * установить ФИО сокращенное на русском
     */
    public function setSurnameShortRu($value) {
        $this->surnamesShort['ru'] = $value;
    }

    /**
     * @получить название статуса сотрудника
     */
    public function getEmployeeStatusText() {
        if ($this->employeeStatus != null)
            return $this->employeeStatus->name;
    }

    /**
     * получить название должности
     */
    public function getJobPositionText() {
        if ($this->jobPosition != null)
            return $this->jobPosition->name;
    }

    /**
     * получить должность
     */
    public function getJobPosition()
    {
        return $this->hasOne(JobPosition::className(), ['id' => 'job_position_id']);
    }

    /**
     * получить статус сотрудника
     */
    public function getEmployeeStatus()
    {
        return $this->hasOne(EmployeeStatus::className(), ['id' => 'employee_status_id']);
    }

    /**
     * получить Вид организации
     */
    public function getListOrgKind()
    {
        return $this->hasOne(ListOrgKind::className(), ['id' => 'org_kind']);
    }
    /**
     * Получение сотрудника по уникальному идентификатору
     * @param $id
     * @return null|Employee
     */
    public static function getById($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Получение списка сотрудников в виде ассоциативного массива, где ключ - id, значение - значение поля переданного параметром ('surnameFull' по-умолчанию)
     * @param string $field поле для значения
     * @param boolean
     * @return array список сотрудников
     */
    public static function getList($field = 'surnameFull', $empty = false, $andWhere = '1=1') {
        $arr = Employee::find()
                ->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])
                ->andWhere($andWhere)
                ->all();
        $res = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $res = [null => ''] + $res;

        return $res;
    }

    public static function getListFast($field = 'surname_short', $empty = false, $andWhere = '1=1') {

        $empTable= Employee::tableName();
        $empTranslateTable= '{{%employee_translate}}';
        $lang = Yii::$app->language;


        $arr = Employee::find()
            ->select("id, $field")
            ->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])
            ->leftJoin("$empTranslateTable tr", "tr.employee_id = $empTable.id and lang='$lang'" )
            ->andWhere($andWhere)
            ->asArray(true)
            ->all();

        $res = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $res = [null => ''] + $res;

        return $res;
    }

    /**
     * Получить логи сотрудника
     */
    public function getLogs(){
        return $this
            ->hasMany(LogEmployee::className(), ['id' => 'log_row_id'])
            ->viaTable('{{%arch_employee}}', ['id' => 'id']);
    }

    /**
     * Получить лог создания
     */
    public function getLogCreation(){
        return $this
            ->hasOne(LogEmployee::className(), ['id' => 'log_row_id'])
            ->where(['type' => LogEmployee::OPERATION_CREATE])
            ->viaTable('{{%arch_employee}}', ['id' => 'id']);
    }

    /**
     * Получить последний лог редактирования
     */
    public function getLogLastUpdate(){
        return $this
            ->hasOne(LogEmployee::className(), ['id' => 'log_row_id'])
            ->where(['type' => LogEmployee::OPERATION_UPDATE])
            ->orderBy('date DESC')
            ->viaTable('{{%arch_employee}}', ['id' => 'id']);
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

        return array_merge($createArray, $lastUpdateArray);
    }

    public function getEmployeeStatusColor()
    {
        $r = [
            2 => '#FFCCCC',    //«Уволен»/ «Dismissed» (Id = 2) – красный (отличный от удаленного);
            3 => '#40E0D0',    //«В декретном отпуске»/ «Maternity» (Id = 3) – синий;
            4 => '#33FF98'     //«Испытательный срок»/ «Probation» (Id = 4) – зеленый; #00FF7F
        ];
        return $r[$this->employee_status_id];
    }

    /**
     * Метод архивирования
     * @param $operation int Тип операции. LogEmployee::OPERATION_
     */
    public function archive($operation) {
        $user_id = Yii::$app->user->id;
        $sql = "call archive_employee($this->id, $operation, $user_id)";
        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {
        // получение полей из таблицы перевода
        $this->surnames = Translator::getAll('{{%employee_translate}}', 'surname', 'employee_id', $this->id);
        $this->surname = $this->surnames[Yii::$app->language];
        $this->names = Translator::getAll('{{%employee_translate}}', 'name', 'employee_id', $this->id);
        $this->name = $this->names[Yii::$app->language];
        $this->secondNames = Translator::getAll('{{%employee_translate}}', 'second_name', 'employee_id', $this->id);
        $this->secondName = $this->secondNames[Yii::$app->language];
        $this->surnamesFull = Translator::getAll('{{%employee_translate}}', 'surname_full', 'employee_id', $this->id);
        $this->surnameFull = $this->surnamesFull[Yii::$app->language];
        $this->surnamesShort = Translator::getAll('{{%employee_translate}}', 'surname_short', 'employee_id', $this->id);
        $this->surnameShort = $this->surnamesShort[Yii::$app->language];
    }

    /**
     * метод вызывается перед удалением сущности
     */
    public function beforeDelete() {
        //Translator::delTranslation('{{%job_position_translate}', 'job_position_id', $this->id);
        return parent::beforeDelete();
    }

    /**
     * метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        // добавление в лог операций над сущностью
        $this->archive($insert ? LogEmployee::OPERATION_CREATE : LogEmployee::OPERATION_UPDATE);

        if ($this->operation == self::OPERATION_NONE  || $this->operation == self::OPERATION_UPDATE) {
            // созранение всех переводов
            Translator::setAll('{{%employee_translate}}', 'employee_id', $this->id,
                [
                    'surname' => $this->surnames,
                    'name' => $this->names,
                    'second_name' => $this->secondNames,
                    'surname_full' => $this->surnamesFull,
                    'surname_short' => $this->surnamesShort
                ]);
        }

        $this->operation = self::OPERATION_NONE;
    }

    public function getCountry(){
        return $this->hasOne(Country::className(),['id'=>'country_id']);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение

     */
    public function toJson(){

        return [
            'id'                => $this->id,
            'surnameFullUk'      => $this->surnameFullUk,
            'surnameFullRu'      => $this->surnameFullRu,
            'surnameFullEn'      => $this->surnameFullEn,
            'email'             => $this->email,
            'org_kind'          => $this->org_kind,
            'org_kind_name'     => $this->listOrgKind->getShortName(),
            'work_phone_number' => $this->work_phone_number,
            'status'            => $this->employeeStatus->name,
            'job_position'      => $this->jobPosition->name,
            'state'             => $this->state,
            'c_nameShortRu'     => $this->country->nameShortRu,
            'c_nameShortEn'     => $this->country->nameShortEn,
            'city'              => $this->city,
            'department'        => $this->departament,
            'visible'           => $this->visible,
            'row_color'         => $this->employeeStatusColor,
        ];

    }

    public function getFilters(){

        $urlCountries = Url::to(['dictionaries/country/get-list']);
        $urlCities = Url::to(['dictionaries/list-city/get-list']);
        $urlWarehouses = Url::to(['dictionaries/warehouse/get-list']);
        $urlJobPosition = Url::to(['dictionaries/job-position/get-list']);
        $urlListOrgKind = Url::to(['dictionaries/list-org-kind/get-list']);
        $urlEmployeeStatus = Url::to(['dictionaries/employee-status/get-list']);

        return  [

            ['id'=>'f_employee_id', 'field' => 'id','operation' => '='],

            ['id'=>'f_employee_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,'label'=>Yii::t('app','Language').':',
                'items'=>Langs::$Names, 'operation' => '=', 'field' => 'lang', 'lang_selector'=>true],

            [
                'id'=>'f_employee_country', 'type'=>self::FILTER_SELECT2,'operation' => '=', 'field' => 'country_id',
                'items'=>Country::getListFast('name_short', false, Yii::$app->language),
                'lang_dependency'=>true,'url'=>$urlCountries,

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'employee_full_name').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/country/index']),
                'select_tab_uniqname'=>'filteremployee_country',
                'view_tab_title'=>Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/country/view']),
                'view_tab_uniqname'=>'country_{0}',
            ],

            [
                'id'=>'f_employee_city', 'type'=>self::FILTER_SELECT2,'operation' => '=', 'field' => 'city_id',
                'items'=>ListCity::getList('name', false),
                'lang_dependency'=>true,'url'=>$urlCities,

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'employee_full_name').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname'=>'filteremployee_city',
                'view_tab_title'=>Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname'=>'city_{0}',
            ],

            ['id'=>'f_employee_warehouse', 'type'=>self::FILTER_SELECT2,'operation' => '=', 'field' => 'warehouse_id',
                'items'=>ListWarehouse::getList('name', false),
                'lang_dependency'=>true,'url'=>$urlWarehouses,

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'employee_full_name').': '.Yii::t('tab_title', 'warehouse_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/warehouse/index']),
                'select_tab_uniqname'=>'filteremployee_warehouse',
                'view_tab_title'=>Yii::t('tab_title', 'warehouse_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/warehouse/view']),
                'view_tab_uniqname'=>'warehouse_{0}',
            ],

            [
                'id'=>'f_employee_org_kind', 'type'=>self::FILTER_DROPDOWN,
                'items'=>ListOrgKind::getList('name_short_'.Yii::$app->language, true),
                'operation' => '=',
                'field' => 'org_kind',
                'lang_dependency'=>true,
                'url'=>$urlListOrgKind,
            ],

            ['id'=>'f_employee_surname', 'field' => 'surname','operation' => 'starts'],
            ['id'=>'f_employee_name', 'field' => 'name','operation' => 'starts'],
            ['id'=>'f_employee_secondname', 'field' => 'second_name','operation' => 'like'],

            [
                'id'=>'f_employee_position', 'type'=>self::FILTER_DROPDOWN,
                'items'=>JobPosition::getList('name'.Yii::$app->language, true), 'operation' => '=', 'field' => 'job_position_id',
                'lang_dependency'=>true,'url'=>$urlJobPosition,

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'employee_full_name').': '.Yii::t('tab_title', 'job-position_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/job-position/index']),
                'select_tab_uniqname'=>'filteremployee_jobposition',
                'view_tab_title'=>Yii::t('tab_title', 'job-position_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/job-position/view']),
                'view_tab_uniqname'=>'jobposition_{0}',
            ],

            [
                'id'=>'f_employee_status', 'type'=>self::FILTER_DROPDOWN,
                'items'=>EmployeeStatus::getList('name'.Yii::$app->language, true), 'operation' => '=', 'field' => 'employee_status_id',
                'lang_dependency'=>true,'url'=>$urlEmployeeStatus,

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'employee_full_name').': '.Yii::t('tab_title', 'employee_status').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/employee-status/index']),
                'select_tab_uniqname'=>'filteremployee_employeestatus',
                'view_tab_title'=>Yii::t('tab_title', 'employee_status').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/employee-status/view']),
                'view_tab_uniqname'=>'employeestatus_{0}',
            ],

            ['id'=>'f_employee_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'operation' => '=', 'field' => 'state'],

            ['id'=>'f_employee_visible', 'type'=>self::FILTER_DROPDOWN, 'value'=>'',
                'items'=>$this->getVisibilityList(true), 'operation' => '=', 'field' => 'visible'],

        ];
    }


    public function getCityModel(){
        return $this->hasOne(ListCity::className(), ['id'=>'city_id']);
    }

    public function  getAvailableCities(){
        $country = $this->country_id ?: -1;
        return ListCity::getList('name', true, Yii::$app->language, "region in ( select id from ".ListRegion::tableName()." where country = {$country})");
    }

    public function getWarehouseModel(){
        return $this->hasOne(ListWarehouse::className(), ['id'=>'warehouse_id']);
    }

    public function  getAvailableWarehouses(){
        return ListWarehouse::getList('name', true, Yii::$app->language, $this->city_id?"city = {$this->city_id}":'');
    }

    public function getCity(){
        if ($this->cityModel)
            return $this->cityModel->name;

        return '';
    }

    public function getDepartament(){
        if ($this->warehouseModel)
            return $this->warehouseModel->name;

        return '';
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

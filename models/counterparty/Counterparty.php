<?php

namespace app\models\counterparty;

use app\classes\DocumentStorage;
use app\models\attached_doc\AttachedDoc;
use app\models\attached_doc\CpAttachedDoc;
use app\models\attached_doc\CpContractAttachedDoc;
use Yii;
use yii\db\Query;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\common\Langs;
use app\models\common\CommonModel;
use app\models\dictionaries\employee\Employee;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\warehouse\ListWarehouse;

/**
 * Модель контрагента
 * @author Tochonyi DM
 * @category Counterparty
 *
 * @property string $id
 * @property integer $state
 * @property integer $visible
 * @property string $counterparty_id
 * @property string $person_type
 * @property integer $internet_shop
 * @property integer $corporate_client
 * @property integer $inner
 * @property string $npu_counterparty_code
 * @property string $npu_crm_code
 * @property integer $manager
 * @property integer $use_pa
 * @property integer $use_api
 * @property integer $vipclient
 * @property integer $warehouse_fixation
 * @property integer $counterparty_status
 * @property integer $country_fixation
 * @property integer $city_fixation
 * @property integer $resident_of_ukraine
 *
 * @property ListPersonType $personType
 * @property Employee $managerEmployee
 * @property Country $countryFixation;
 * @property ListCity $cityFixation;
 * @property ListWarehouse $warehouseFixation;
 *
 * @property CounterpartyContactPers[] $counterpartyContactPers
 * @property CounterpartyLegalEntity $counterpartyLegalEntity
 * @property CounterpartyManualAdress[] $counterpartyManualAdresses
 * @property CounterpartyPersDocs $counterpartyPersDocs
 * @property CounterpartyPrivatPers $counterpartyPrivatPers
 * @property CounterpartyContactPersPhones[] $counterpartyContactPersPhones
 * @property CounterpartyContactPers[] $counterpartyContactPersRel relation
 * @property CounterpartyContactPersEmail[] $counterpartyContactPersEmail
 * @property CounterpartyContactPersOthercontact[] $counterpartyContactPersOthercontact
 * @property CounterpartyContract[] $counterpartyContract
 * @property CounterpartySign[] $counterpartySign;
 *
 * @property array serviceData Служебная информация
 *
 * поля получаемые с других таблиц
 * @property string $counterpartyName_en ПІБ/Назва контрагента
 * @property string $counterpartyName_uk ПІБ/Назва контрагента
 * @property string $counterpartyName_ru ПІБ/Назва контрагента
 * @property string $counterpartyName ПІБ/Назва контрагента в зависимости от языка
 * @property string $code ІДН/Код ЄДРПОУ
 * @property CounterpartyManualAdress $counterpartyPrimaryAdress основной адрес
 * @property CounterpartyContactPers $counterpartyPrimaryPers основное контактное лицо
 * @property CounterpartyContactPersPhones $counterpartyPrimaryPhone основной телефон
 * @property CounterpartyContactPersEmail $counterpartyPrimaryEmail основной Email
 * @property Counterparty $parentCounterparty родительская компания
 *
 * @property ListCity $availableCities;
 * @property ListWarehouse $availableWarehouses;
 *
 * @property mixed $counterpartySignArray дополнительные признаки контрагента в виде массива
 *
 * @property Counterparty[] $counterpartySubsidiary
 * @property bool isPrivatePerson
 * @property bool isLegalPerson
 *
 */
class Counterparty extends CommonModel
{

    /**
     * @var CounterpartyLegalEntity Переменная для хранения введеных пользователем данных о юр лице
     */
    private $counterpartyLegalEntityInput;

    /**
     * @var CounterpartyPrivatPers Переменная для хранения введеных пользователем данных о физ лице
     */
    private $counterpartyPrivatPersInput;

    /**
     * @var CounterpartyPersDocs Переменная для хранения введеных пользователем данных о документе физ лица
     */
    private $counterpartyPersDocsInput;

    /**
     * @var CounterpartyManualAdress[] Переменная для хранения массива введеных пользователем адресов
     */
    private $counterpartyManualAdressesInput;

    /**
     * @var CounterpartyContactPers[] Переменная для хранения массива введеных пользователем контактов
     */
    private $counterpartyContactPersInput;

    /**
     * @var CounterpartyContactPersPhones[] Переменная для хранения массива введеных пользователем контактов
     */
    private $counterpartyContactPersPhonesInput;

    /**
     * @var CounterpartyContactPersEmail[] Переменная для хранения массива введеных пользователем контактов
     */
    private $counterpartyContactPersEmailInput;

    /**
     * @var mixed Переменная для хранения массива дополнительных признаков контрагента
     */
    private $counterpartySignArrayInput;

    /**
     * @var int 1 - нужно или 0 - нет делать проверки на дубли перед сохранением
     */
    public $forceSave = 0;

    const ENTITY_NAME = 'CP';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(),
            [
                [['state', 'visible', 'person_type', 'internet_shop', 'corporate_client', 'inner', 'manager', 'use_pa',
                    'use_api', 'vipclient', 'warehouse_fixation', 'counterparty_status', 'country_fixation',
                    'city_fixation', 'resident_of_ukraine'], 'integer'],
                [['counterparty_id', 'person_type'], 'required'],
                [['counterparty_id'], 'number'],
                [['counterparty_id'], 'unique'],
                [['npu_counterparty_code', 'npu_crm_code'], 'string'],
                [['person_type'], 'validateCounterparty'],

                [['counterpartyLegalEntity', 'counterpartyPrivatPers', 'counterpartyPersDocs',
                    'counterpartyManualAdresses', 'counterpartyContactPers', 'counterpartyContactPersPhones',
                    'counterpartyContactPersEmail', 'counterpartySignArray', 'forceSave'], 'safe'],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),
            [
                'id' => Yii::t('counterparty', 'ID'),
                'ID' => Yii::t('counterparty', 'ID'),
                'state' => Yii::t('counterparty', 'State'),
                'visible' => Yii::t('counterparty', 'Visible'),
                'counterparty_id' => Yii::t('counterparty', 'Counterparty ID'),
                'person_type' => Yii::t('counterparty', 'Person Type'),
                'internet_shop' => Yii::t('counterparty', 'Internet Shop'),
                'corporate_client' => Yii::t('counterparty', 'Corporate Client'),
                'inner' => Yii::t('counterparty', 'Inner counterparty'),
                'counterpartyPrimaryAdress' => Yii::t('counterparty', 'Primary Adress'),
                'counterpartyPrimaryPers' => Yii::t('counterparty', 'Primary Person'),
                'counterpartyPrimaryPhone' => Yii::t('counterparty', 'Primary Phone'),
                'counterpartyPrimaryEmail' => Yii::t('counterparty', 'Primary Email'),
                'npu_counterparty_code' => Yii::t('counterparty', 'NPU counterparty code'),
                'npu_crm_code' => Yii::t('counterparty', 'NPU CRM code'),
                'manager' => Yii::t('counterparty', 'Manager'),
                'use_pa' => Yii::t('counterparty', 'Use PA'),
                'use_api' => Yii::t('counterparty', 'Use API'),
                'vipclient' => Yii::t('counterparty', 'VIP client'),
                'warehouse_fixation' => Yii::t('counterparty', 'Warehouse fixation'),
                'counterparty_status' => Yii::t('counterparty', 'Counterparty status'),
                'country_fixation' => Yii::t('counterparty', 'Country fixation'),
                'city_fixation' => Yii::t('counterparty', 'Settlement fixation'),
                'counterpartySignArray' => Yii::t('counterparty', 'Additional feature of the counterparty'),
                'counterparty_sign_names' => Yii::t('counterparty', 'Additional feature of the counterparty'),
                'parentCounterpartyName' => Yii::t('counterparty', 'Parent company'),
                'counterparty_name' => Yii::t('counterparty', 'Counterparty'),
                'counterpartyName_en' => Yii::t('counterparty', 'Counterparty Name En'),
                'counterpartyName_uk' => Yii::t('counterparty', 'Counterparty Name Uk'),
                'counterpartyName_ru' => Yii::t('counterparty', 'Counterparty Name Ru'),
                'resident_of_ukraine'=>Yii::t('counterparty', 'Resident of Ukraine'),
                'itn_code'=>Yii::t('counterparty', 'ITN'),
                'primary_name'=>Yii::t('counterparty', 'Primary Name'),
                'Operation' => Yii::t('app', 'Operation'),
/*
'phone_number'=>Yii::t('counterparty', 'Phone Number'),
'operator_code'=>Yii::t('counterparty', 'Operator Code'),
'phone_num_type_name'=>Yii::t('counterparty', 'Phone Num Type Name'),
'counterparty_cont_pers_name_en'=>Yii::t('counterparty', 'Сounterparty Сont Pers Name En'),
'counterparty_cont_pers_name_uk'=>Yii::t('counterparty', 'Сounterparty Сont Pers Name Uk'),
'counterparty_cont_pers_name_ru'=>Yii::t('counterparty', 'Сounterparty Сont Pers Name Ru'),
*/

                'Id'=>Yii::t('counterparty', 'ID'),
                'code'=>Yii::t('counterparty', 'Code'),

                'counterparty_name_uk'=>Yii::t('counterparty', 'Counterparty Name Uk'),
                'counterparty_name_ru'=>Yii::t('counterparty', 'Counterparty Name Ru'),
                'counterparty_name_en'=>Yii::t('counterparty', 'Counterparty Name En'),
                'counterparty_primary_pers_en'=>Yii::t('counterparty', 'Primary contact person En'),
                'counterparty_primary_pers_uk'=>Yii::t('counterparty', 'Primary contact person Uk'),
                'counterparty_primary_pers_ru'=>Yii::t('counterparty', 'Primary contact person Ru'),
                'counterparty_primary_adress_en'=>Yii::t('counterparty', 'Primary address En'),
                'counterparty_primary_adress_uk'=>Yii::t('counterparty', 'Primary address Uk'),
                'counterparty_primary_adress_ru'=>Yii::t('counterparty', 'Primary address Ru'),
                'counterparty_primary_phone'=>Yii::t('counterparty', 'Primary phone'),
                'counterparty_primary_email'=>Yii::t('counterparty', 'Primary E-mail'),


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
                'class' => DocumentStorage::className(),
            ]
        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового контрагента
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
        $this->counterparty_id = $this->getNextCounterValue('counterparty_id');
        $this->counterparty_status = 1;
        if ($params['operation'] != null)
            $this->copyCounterparty($params);
    }

    public function copyCounterparty($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $counterparty = Counterparty::findOne(['id' => $params['id']]);
            if($counterparty) {
                $this->attributes = $counterparty->getAttributes(null, ['counterparty_id']);
                $this->counterpartyLegalEntityInput = $counterparty->counterpartyLegalEntity;
                $this->counterpartyPrivatPersInput = $counterparty->counterpartyPrivatPers;
                $this->counterpartyPersDocsInput = $counterparty->counterpartyPersDocs;
                $this->counterpartyManualAdressesInput = $counterparty->counterpartyManualAdresses;
                $this->counterpartyContactPersInput = $counterparty->counterpartyContactPers;
                $this->counterpartyContactPersEmailInput = $counterparty->counterpartyContactPersEmail;
                $this->counterpartySignArrayInput = $counterparty->counterpartySignArray;
            }
        }
    }

    /**
     * Проверка заполнености полей связанных сущностей
     */
    public function validateCounterparty(){
        if ($this->person_type == 1) {
            if ($this->counterpartyPrivatPersInput) {
                $this->counterpartyPrivatPersInput->counterparty = 0; //чтоб сработал валидатор
                if (!$this->counterpartyPrivatPersInput->validate()) {
                    foreach ($this->counterpartyPrivatPersInput->errors as $k => $v) {
                        $this->addError("counterpartyPrivatPers[$k]", $v[0]);
                    }
                }
            }
            if ($this->counterpartyPersDocsInput && $this->counterpartyPersDocsInput->doc_type && !$this->counterpartyPersDocsInput->validate()) {
                foreach ($this->counterpartyPersDocsInput->errors as $k=>$v) {
                    $this->addError("counterpartyPersDocs[$k]", $v[0]);
                }
            }
        }
        else {
            if ($this->counterpartyLegalEntityInput){
                $this->counterpartyLegalEntityInput->counterparty = 0; //чтоб сработал валидатор
                $this->counterpartyLegalEntityInput->residentOfUkraine = $this->resident_of_ukraine;
                if (!$this->counterpartyLegalEntityInput->validate()) {
                    foreach ($this->counterpartyLegalEntityInput->errors as $k => $v) {
                        $this->addError("counterpartyLegalEntity[$k]", $v[0]);
                    }
                }
            }
        }

    }

    public function getPersonType()
    {
        return $this->hasOne(ListPersonType::className(), ['id' => 'person_type']);
    }

    public function getManagerEmployee()
    {
        return $this->hasOne(Employee::className(), ['id' => 'manager']);
    }

    public function getCountryFixation()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_fixation']);
    }

    public function getCityFixation()
    {
        return $this->hasOne(ListCity::className(), ['id' => 'city_fixation']);
    }

    public function getWarehouseFixation()
    {
        return $this->hasOne(ListWarehouse::className(), ['id' => 'warehouse_fixation']);
    }

    public function  getAvailableCities(){
        $country = $this->country_fixation ?: -1;
        return ListCity::getList('name', true, Yii::$app->language, "region in ( select id from ".ListRegion::tableName()." where country = {$country})");
    }
    public function  getAvailableWarehouses(){
        return ListWarehouse::getList('name', true, Yii::$app->language, $this->city_fixation?"city = {$this->city_fixation}":'');
    }

    /**
     * метод получения модели данных о юр лице
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyLegalEntity()
    {
        if ($this->counterpartyLegalEntityInput == null) {
            $this->counterpartyLegalEntityInput = $this->hasOne(CounterpartyLegalEntity::className(),
                ['counterparty' => 'id'])->one();
            if ($this->counterpartyLegalEntityInput == null)
                $this->counterpartyLegalEntityInput = new CounterpartyLegalEntity();
        }
        return $this->counterpartyLegalEntityInput;
    }

    /**
     * метод сохранения введенных пользователем данных о юр лице
     */
    public function setCounterpartyLegalEntity($value)
    {
        $this->counterpartyLegalEntityInput = $this->hasOne(CounterpartyLegalEntity::className(),
            ['counterparty' => 'id'])->one();
        if ($this->counterpartyLegalEntityInput == null)
            $this->counterpartyLegalEntityInput = new CounterpartyLegalEntity();
        $this->counterpartyLegalEntityInput->load($value, '');
    }

    /**
     * метод получения модели данных о физ лице
     * @return CounterpartyPrivatPers
     */
    public function getCounterpartyPrivatPers()
    {
        if ($this->counterpartyPrivatPersInput == null) {
            $this->counterpartyPrivatPersInput = $this->hasOne(CounterpartyPrivatPers::className(),
                ['counterparty' => 'id'])->one();
            if ($this->counterpartyPrivatPersInput == null)
                $this->counterpartyPrivatPersInput = new CounterpartyPrivatPers();
        }
        return $this->counterpartyPrivatPersInput;
    }

    /**
     * метод сохранения введенных пользователем данных о физ лице
     */
    public function setCounterpartyPrivatPers($value)
    {
        $this->counterpartyPrivatPersInput = $this->hasOne(CounterpartyPrivatPers::className(),
            ['counterparty' => 'id'])->one();
        if ($this->counterpartyPrivatPersInput == null)
            $this->counterpartyPrivatPersInput = new CounterpartyPrivatPers();
        $this->counterpartyPrivatPersInput->load($value, '');
        $this->counterpartyPrivatPersInput->counterparty = $this->id;
    }

    /**
     * метод получения модели данных о документе физ лица
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyPersDocs()
    {
        if ($this->counterpartyPersDocsInput == null) {
            $this->counterpartyPersDocsInput = $this->hasOne(CounterpartyPersDocs::className(),
                ['counterparty' => 'id'])->one();
            if ($this->counterpartyPersDocsInput == null)
                $this->counterpartyPersDocsInput = new CounterpartyPersDocs();
        }
        return $this->counterpartyPersDocsInput;
    }

    /**
     * метод сохранения введенных пользователем данных о физ лице
     */
    public function setCounterpartyPersDocs($value)
    {
        $this->counterpartyPersDocsInput = $this->hasOne(CounterpartyPersDocs::className(),
            ['counterparty' => 'id'])->one();
        if ($this->counterpartyPersDocsInput == null)
            $this->counterpartyPersDocsInput = new CounterpartyPersDocs();
        $this->counterpartyPersDocsInput->load($value, '');
        $this->counterpartyPersDocsInput->counterparty = $this->id;
    }

    /**
     * метод получения списка адресов контрагента
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyManualAdresses()
    {
        if ($this->counterpartyManualAdressesInput)
            return $this->counterpartyManualAdressesInput;
        $this->counterpartyManualAdressesInput = $this->hasMany(CounterpartyManualAdress::className(),
            ['counterparty' => 'id'])->all();
        return $this->counterpartyManualAdressesInput;
    }

    /**
     * Связь со списком контактных лиц контрагента
     */
    public function getCounterpartyContactPersRel() {
        return $this->hasMany(CounterpartyContactPers::className(), ['counterparty' => 'id']);
    }

    /**
     * метод получения списка контактных лиц контрагента
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPers()
    {
        if ($this->counterpartyContactPersInput)
            return $this->counterpartyContactPersInput;
        $this->counterpartyContactPersInput = $this->hasMany(CounterpartyContactPers::className(),
            ['counterparty' => 'id'])->all();
        return $this->counterpartyContactPersInput;
    }

    /**
     * метод получения списка телефонов контрагента
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPersPhones(){
        if ($this->counterpartyContactPersPhonesInput)
            return $this->counterpartyContactPersPhonesInput;
        $this->counterpartyContactPersPhonesInput = CounterpartyContactPersPhones::find()
            ->leftJoin('{{%counterparty_contact_pers}} pers', 'counterparty_contact_pers=pers.id')
            ->where(['pers.counterparty' => $this->id])->all();
        return $this->counterpartyContactPersPhonesInput;
    }

    /**
     * метод получения списка электронных адресов контрагента
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPersEmail()
    {
        if ($this->counterpartyContactPersEmailInput)
            return $this->counterpartyContactPersEmailInput;
        $this->counterpartyContactPersEmailInput = CounterpartyContactPersEmail::find()
            ->leftJoin('{{%counterparty_contact_pers}} pers', 'counterparty_contact_pers=pers.id')
            ->where(['pers.counterparty' => $this->id])->all();
        return $this->counterpartyContactPersEmailInput;
    }

    /**
     * метод получения списка других контактов контрагента
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPersOthercontact()
    {
        return CounterpartyContactPersOthercontact::find()
            ->leftJoin('{{%counterparty_contact_pers}} pers', 'counterparty_contact_pers=pers.id')
            ->where(['pers.counterparty' => $this->id])->all();
    }

    /**
     * метод получения списка контрактов контрагента
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContract()
    {
        return $this->hasMany(CounterpartyContract::className(), ['counterparty_id' => 'id']);
    }

    /**
     * Метод получения списка дополнительных признаков
     */
    public function getCounterpartySign()
    {
        return $this->hasMany(CounterpartySign::className(), ['counterparty_id' => 'id']);
    }

    /**
     * Метод получения дополнительных признаков в виде массива
     */
    function getCounterpartySignArray(){
        $signs = $this->counterpartySign;
        return ArrayHelper::map($signs, 'counterparty_sign_id', 'counterparty_sign_id');
    }

    /**
     * установка дополнительных признаков
     */
    function setCounterpartySignArray($values){

        $this->counterpartySignArrayInput = $values;
    }

    /**
     * сохранение дополнительных признаков
     */
    function saveСounterpartySign(){

        // удаление старых дополнительных признаков
        foreach ($this->counterpartySign as $sign)
            $sign->delete();

        // привязка новых дополнительных признаков
        if (is_array($this->counterpartySignArrayInput))
            foreach ($this->counterpartySignArrayInput as $value){
                $newSign = new CounterpartySign();
                $newSign->counterparty_sign_id = $value;
                $newSign->counterparty_id = $this->id;
                $newSign->save();
            }
    }

    public function getCounterpartyName_en()
    {
        if ($this->counterpartyPrivatPers->counterparty != null)
            return $this->counterpartyPrivatPers->display_name_en;
        return $this->counterpartyLegalEntity->display_name_en;
    }

    public function getCounterpartyName_uk()
    {
        if ($this->counterpartyPrivatPers->counterparty != null)
            return $this->counterpartyPrivatPers->display_name_uk;
        return $this->counterpartyLegalEntity->display_name_uk;
    }

    public function getCounterpartyName_ru()
    {
        if ($this->counterpartyPrivatPers->counterparty != null)
            return $this->counterpartyPrivatPers->display_name_ru;
        return $this->counterpartyLegalEntity->display_name_ru;
    }

    public function getCounterpartyName($lang = null)
    {
        if (!$lang)
            $lang = Yii::$app->language;

        return $this->{"counterpartyName_$lang"};
    }

    public function getCode()
    {
        if ($this->counterpartyPrivatPers->counterparty != null)
            return $this->counterpartyPrivatPers->tax_number;
        return $this->counterpartyLegalEntity->edrpou_code;
    }

    /**
     * получение основного адреса
     */
    public function getCounterpartyPrimaryAdress()
    {
        $primaryAdress = array_filter($this->counterpartyManualAdresses,
            function($v){return $v->primary_address == 1 && $v->state == self::STATE_CREATED;});
        if (sizeof($primaryAdress) > 0)
            return array_values($primaryAdress)[0];
        return new CounterpartyManualAdress();
    }
    
    /**
     * получение основного контактного лица
     */
    public function getCounterpartyPrimaryPers()
    {
        $primaryPers = array_filter($this->counterpartyContactPers,
            function($v){return $v->primary_person == 1 && $v->state == self::STATE_CREATED;});
        if (sizeof($primaryPers) > 0)
            return array_values($primaryPers)[0];
        return new CounterpartyContactPers();
    }

    /**
     * получение основного телефона
     */
    public function getCounterpartyPrimaryPhone()
    {
        $primaryPhone = array_filter($this->counterpartyContactPersPhones,
            function($v){return $v->primary == 1 && $v->state == self::STATE_CREATED;});
        if (sizeof($primaryPhone) > 0)
            return array_values($primaryPhone)[0];
        return new CounterpartyContactPersPhones();
    }

    /**
     * получение основного адреса электронной почты
     */
    public function getCounterpartyPrimaryEmail()
    {
        $primaryEmail = array_filter($this->counterpartyContactPersEmail,
            function($v){return $v->primary == 1 && $v->state == self::STATE_CREATED;});
        if (sizeof($primaryEmail) > 0)
            return array_values($primaryEmail)[0];
        return new CounterpartyContactPersEmail();
    }

    /**
     * Получение дополнительных признаков в виде строки разделенной запятыми
     */
    public function getCounterpartySignNames()
    {
        $result = array();
        foreach ($this->counterpartySign as $sign)
            $result[] = $sign->counterpartySign->name;
        return implode(", ", $result);
    }

    /**
     * Получение родительской компании
     * @return \app\models\counterparty\Counterparty;
     */
    public function getParentCounterparty()
    {
        if ($this->counterpartyLegalEntity->counterparty != null) {
            return Counterparty::findOne($this->counterpartyLegalEntity->parent_company);
        }
    }

    /**
     * Получение названия родительской компании
     */
    public function getParentCounterpartyName()
    {
        $cmodel = $this->getParentCounterparty();
        if ($cmodel != null) {
            return $cmodel->counterparty_id.' '.$cmodel->{"counterpartyName_".Yii::$app->language};
        }
    }

    /**
     * Получение списка дочерних компаний
     */
    public function getCounterpartySubsidiary()
    {
        $models = Counterparty::find()
            ->innerJoin('{{%counterparty_legal_entity}} legal', 'id=legal.counterparty')
            ->where(['coalesce(legal.parent_company, -1)' => $this->id])->all();
        return $models;
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'state'=>$this->state,
            'counterparty_id'=>$this->counterparty_id,
            'person_type'=>$this->personType->name,
            // Такое название _cp_pt нужно для выгрузки етого атрибута в ексель
            'person_type_cp_pt'=>$this->personType->name,
            'counterparty_name_en'=>$this->counterpartyName_en,
            'counterparty_name_uk'=>$this->counterpartyName_uk,
            'counterparty_name_ru'=>$this->counterpartyName_ru,
            'counterparty_name'=>$this->counterpartyName,
            'code'=>$this->code,
            'counterparty_primary_pers_id'=>$this->counterpartyPrimaryPers->id,
            'counterparty_primary_pers_en'=>$this->counterpartyPrimaryPers->display_name_en,
            'counterparty_primary_pers_uk'=>$this->counterpartyPrimaryPers->display_name_uk,
            'counterparty_primary_pers_ru'=>$this->counterpartyPrimaryPers->display_name_ru,
            'counterparty_primary_pers'=>$this->counterpartyPrimaryPers->display_name,
            'counterparty_primary_adress_id'=>$this->counterpartyPrimaryAdress->id,
            'counterparty_primary_adress_en'=>$this->counterpartyPrimaryAdress->adress_full_en,
            'counterparty_primary_adress_uk'=>$this->counterpartyPrimaryAdress->adress_full_uk,
            'counterparty_primary_adress_ru'=>$this->counterpartyPrimaryAdress->adress_full_ru,
            'counterparty_primary_adress'=>$this->counterpartyPrimaryAdress->adress_full,
            'counterparty_primary_phone_id'=>$this->counterpartyPrimaryPhone->id,
            'counterparty_primary_phone'=>$this->counterpartyPrimaryPhone->displayPhone,
            'counterparty_primary_email_id'=>$this->counterpartyPrimaryEmail->id,
            'counterparty_primary_email'=>$this->counterpartyPrimaryEmail->email,
            'internet_shop'=>$this->internet_shop,
            'corporate_client'=>$this->corporate_client,
            'manager'=>$this->managerEmployee->surnameFull,
            'counterparty_sign_names'=>$this->counterpartySignNames,
            'clearing'=>$this->clearing,
            'thirdparty'=>$this->thirdparty,
            'row_color'=>$this->getColor()
        ];
    }

    /**
     * Получение служебной информации
     * @return array Массив с полями служебной информации
     */
    public function getServiceData(){

        $createArray = [];
        /*
        if ($this->logCreation)
            $createArray = $this->logCreation->toJson('create_');
        $lastUpdateArray = [];
        if ($this->logLastUpdate)
            $lastUpdateArray = $this->logLastUpdate->toJson('lastupdate_');

        return array_merge($createArray, $lastUpdateArray);
        */
        return $createArray;
    }

    /**
     * Выполняется перед сохранением сущности
     * @param bool $insert создание/редактирование
     * @return bool сатисфекшн
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            if (!$this->forceSave && ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE)) {

                //проверка на дубликаты и вывод предупреждений
                $warnings = Array();
                $ids = Array();
                $cp_id = $this->id == null ? 0 : $this->id;

                if ($this->person_type == 1) {

                    if ($this->counterpartyPrivatPersInput->tax_number) {
                        $exists = CounterpartyPrivatPers::find()->
                        where('counterparty<>' . $cp_id . ' and tax_number=' . $this->counterpartyPrivatPersInput->tax_number)->all();
                        foreach ($exists as $exist) {
                            if (!in_array($exist->counterparty, $ids)) {
                                $cp = Counterparty::findOne($exist->counterparty);
                                $warnings[] = $cp->counterparty_id;
                                $ids[] = $exist->counterparty;
                            }
                        }
                    }

                    if ($this->counterpartyPersDocsInput->doc_type == 1) { //Паспорт гражданина Украины
                        $exists = CounterpartyPersDocs::find()->
                        where('counterparty<>' . $cp_id . ' and doc_type=1 and doc_serial_num="'
                            . $this->counterpartyPersDocsInput->doc_serial_num . '" and doc_num="' . $this->counterpartyPersDocsInput->doc_num . '"')->all();
                        foreach ($exists as $exist) {
                            if (!in_array($exist->counterparty, $ids)) {
                                $cp = Counterparty::findOne($exist->counterparty);
                                $warnings[] = $cp->counterparty_id;
                                $ids[] = $exist->counterparty;
                            }
                        }

                    }

                } else {

                    $where = '1=2';
                    if ($this->counterpartyLegalEntityInput->itn_code)
                        $where = $where.' or itn_code=' . $this->counterpartyLegalEntityInput->itn_code;
                    if ($this->counterpartyLegalEntityInput->edrpou_code)
                        $where = $where.' or edrpou_code=' . $this->counterpartyLegalEntityInput->edrpou_code;
                    if ($this->counterpartyLegalEntityInput->tax_number)
                        $where = $where.' or tax_number="' . $this->counterpartyLegalEntityInput->tax_number . '"';

                    $exists = CounterpartyLegalEntity::find()->
                    where('counterparty<>' . $cp_id . ' and ('.$where. ')')->all();
                    foreach ($exists as $exist) {
                        $cp = Counterparty::findOne($exist->counterparty);
                        $warnings[] = $cp->counterparty_id;
                        $ids[] = $exist->counterparty;
                    }

                }

                if (sizeof($warnings) > 0) {
                    Yii::$app->session->setFlash('cp_warning', $warnings);
                    Yii::$app->session->setFlash('cp_ids', $ids);
                    $this->forceSave = 0;
                    return false;
                }

            }
            return true;
        }
        return false;
    }


    /**
     * Метод после сохранения
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            if ($this->person_type == 1) {
                $this->counterpartyPrivatPersInput->counterparty = $this->id;
                $this->counterpartyPrivatPersInput->save();
                if ($this->counterpartyPersDocsInput->doc_type) {
                    $this->counterpartyPersDocsInput->counterparty = $this->id;
                    $this->counterpartyPersDocsInput->save();
                }

                $this->counterpartyLegalEntityInput->delete();
                foreach ($this->counterpartySign as $sign)
                    $sign->delete();

            } else {
                $this->counterpartyLegalEntityInput->counterparty = $this->id;
                $this->counterpartyLegalEntityInput->save();
                $this->saveСounterpartySign();

                $this->counterpartyPrivatPersInput->delete();
                $this->counterpartyPersDocsInput->delete();
            }

        }

        //при удалении удаляем все связанные сущности, при восстановлении восстанавливаем
        if ($this->operation == self::OPERATION_DELETE) {
            foreach($this->counterpartyContactPers as $contact) {
                $contact->state = CommonModel::STATE_DELETED;
                $contact->operation = CommonModel::OPERATION_DELETE;
                $contact->save();
            }
            foreach($this->counterpartyManualAdresses as $address) {
                $address->state = CommonModel::STATE_DELETED;
                $address->operation = CommonModel::OPERATION_DELETE;
                $address->save();
            }
            foreach($this->counterpartyContract as $contract) {
                $contract->state = CommonModel::STATE_DELETED;
                $contract->operation = CommonModel::OPERATION_DELETE;
                $contract->save();
            }
        }
        if ($this->operation == self::OPERATION_CANCEL) {
            foreach($this->counterpartyContactPers as $contact) {
                $contact->state = CommonModel::STATE_CREATED;
                $contact->operation = CommonModel::OPERATION_CANCEL;
                $contact->save();
            }
            foreach($this->counterpartyManualAdresses as $address) {
                $address->state = CommonModel::STATE_CREATED;
                $address->operation = CommonModel::OPERATION_CANCEL;
                $address->save();
            }
            foreach($this->counterpartyContract as $contract) {
                $contract->state = CommonModel::STATE_CREATED;
                $contract->operation = CommonModel::OPERATION_CANCEL;
                $contract->save();
            }
        }

        $this->operation = self::OPERATION_NONE;
    }

    public function getFilters() {

        return [
            ['id' => 'f_counterparty_state', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getStateList(true),
                'operation' => '=', 'field' => $this->tableName() . '.state', 'label' => Yii::t('app', 'State') . ':'],

            ['id' => 'f_counterparty_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language,
                'label' => Yii::t('app', 'Language') . ':', 'items' => Langs::$Names, 'lang_selector' => true],

            ['id' => 'f_counterparty_date_from', 'type' => self::FILTER_DATETIME,
                'label' => Yii::t('ew', 'Date from') . ':', 'operation' => '>=', 'field' => '',],

            ['id' => 'f_couterparty_date_to', 'type' => self::FILTER_DATETIME,
                'label' => Yii::t('ew', 'Date to') . ':',  'operation' => '<=', 'field' => '' ],

            ['id' => 'f_counterparty_id', 'field' => $this->tableName() . '.counterparty_id', 'operation' => '=',
                'label' => $this->getAttributeLabel('counterparty_id') . ':'],

            /*['id' => 'f_counterparty_npu_crm_code', 'field' => 'npu_crm_code', 'operation' => 'like',
                'label' => $this->getAttributeLabel('npu_crm_code')],*/

        ];
    }

    public function getAFilters($withItems=true) {

        $urlEmp = Url::to(['dictionaries/employee/get-list']);
        $urlSign = Url::to(['counterparty/counterparty/get-list-sign']);
        $urlDocType = Url::to(['counterparty/counterparty/get-list-doctype']);
        $urlCountries = Url::to(['dictionaries/country/get-list']);
        $urlCities = Url::to(['dictionaries/list-city/get-list']);
        $urlWarehouses = Url::to(['dictionaries/warehouse/get-list']);
        $urlEmployees = Url::to(['dictionaries/employee/get-list']);

        return [

            ['id' => 'af_counterparty_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language,
                'label' => Yii::t('app', 'Language') . ':', 'items' => Langs::$Names, 'lang_selector' => true],

            ['id' => 'hr',],

            [ 'id' => 'title', 'label' => Yii::t('counterparty', 'Counterparty properties') . ':',],

            [ 'id' => 'af_person_type', 'field' => 'person_type', 'operation' => '=', 'type' => self::FILTER_DROPDOWN,
                'items' => $withItems ? ListPersonType::getList(true):[]],

            [ 'id' => 'af_resident_u', 'field' => 'resident_of_ukraine', 'operation' => '=', 'type' => self::FILTER_DROPDOWN,
                'items' => self::getYesNoList(true), 'label' => $this->counterpartyPrivatPers->getAttributeLabel('resident_of_ukraine').':'],



            [ 'id' => 'af_itn', 'label' => Yii::t('counterparty', 'ITN/EDRPOU').':', 'field' => ['le.itn_code','le.edrpou_code'], 'operation' => '='],

            /*[ 'id' => 'af_counterparty_type', 'label' => Yii::t('counterparty', 'Counterparty type').':',
                'field' => 'le.edrpou_code', 'operation' => 'like'],*/

            [ 'id' => 'af_taxnumber', 'label' => Yii::t('counterparty', 'Tax Number').':', 'field' => ['pp.tax_number','le.tax_number'], 'operation' => '='],

            ['id' => 'br',],

            [ 'id' => 'af_counterparty_name', 'label' => Yii::t('counterparty', 'Counterparty Name').':' , 'lang_field' => true,
                'field'=>['pp.display_name','le.display_name'], 'operation'=>'starts' ],

            [ 'id' => 'af_status', 'label' => Yii::t('counterparty', 'Counterparty’s Status').':', 'field' => 'counterparty_status',
                'operation' => '=', 'type' => self::FILTER_DROPDOWN, 'items' => [null => '', '1' => 'New'] ],

            ['id' => 'br',],

            [ 'id' => 'af_additional', 'label' => Yii::t('counterparty', 'Additional feature of the counterparty').':',
                'field' => 's.counterparty_sign_id', 'operation' => '=', 'lang_dependency' => true,
                'type' => self::FILTER_SELECT2, 'items' => $withItems?ListCounterpartySign::getList():[], 'url' => $urlSign],

            [ 'id' => 'af_manager_name', 'label' => Yii::t('counterparty', 'Manager').':', 'field' => 'manager', 'operation' => '=',
                'lang_dependency'=>true, 'type' => self::FILTER_SELECT2, 'items' => $withItems?Employee::getListFast('surname_full',true):[], 'url' => $urlEmp,

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'counterparty_full_name').': '.Yii::t('tab_title', 'employee_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/employee/index']),
                'select_tab_uniqname' => 'filtercounterparty_manager',
                'view_tab_title' => Yii::t('tab_title', 'employee_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/employee/view']),
                'view_tab_uniqname' => 'employee_{0}',
            ],

            ['id' => 'br',],

            [ 'id' => 'af_doc_type', 'label'=>Yii::t('counterparty', 'Document Type').':', 'field' => 'pd.doc_type', 'operation' => '=',
                'lang_dependency' => true, 'type' => self::FILTER_DROPDOWN, 'items' => $withItems?ListPersDocType::getList(true):[], 'url' => $urlDocType],

            [ 'id' => 'af_doc_serial', 'label' => Yii::t('counterparty', 'Document Serial Number').':',
                'field' => 'pd.doc_serial_num', 'operation' => '=',
                'type' => self::FILTER_MASKEDEDIT,
                'mask'=>'ff',
                'mask_definitions'=>['f' => [ 'validator' => '[АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя]','cardinality' => '1', 'casing'=>'upper',]],
            ],

            [ 'id' => 'af_doc_number', 'label'=>Yii::t('counterparty', 'Document Number').':',
                'field' => 'pd.doc_num', 'operation' => '=',
                'type' => self::FILTER_MASKEDEDIT,
                'mask'=>'999999',
            ],


            ['id' => 'hr',],
            [ 'id' => 'title', 'label' => Yii::t('counterparty', 'Contact person properties') . ':',],

            [ 'id' => 'af_phone_type', 'label' => Yii::t('counterparty', 'Phone Number Type').':', 'field' => 'cpp.phone_num_type',
                'operation' => '=', 'type' => self::FILTER_DROPDOWN, 'items' => $withItems?ListPhoneNumType::getActualList('0', $empty = true):[]],

            [ 'id' => 'af_phone_number', 'label' => Yii::t('counterparty', 'Phone Number').':',
                'field' => "concat(coalesce(cpp.operator_code,''),coalesce(cpp.phone_number,''))", 'operation' => 'like'],

            ['id' => 'hr',],
            [ 'id' => 'title', 'label' => Yii::t('counterparty', 'Contract properties') . ':',],

            [ 'id' => 'af_contract_type', 'label' => Yii::t('counterparty', 'Contract Type').':', 'field' => 'c.contract_type',
            'operation' => '=', 'type' => self::FILTER_DROPDOWN, 'items' => $withItems?ListContractType::getactualList('0', $empty = true):[]],

            [ 'id' => 'af_contract_code', 'label' => Yii::t('counterparty', 'Contract Code').':',
                'field' => 'c.id', 'operation' => 'starts'],

            [ 'id' => 'af_contract_number', 'label' => Yii::t('counterparty', 'Contract Number').':',
                'field' => 'c.contract_number', 'operation' => 'starts'],

            ['id' => 'hr',],
            ['id' => 'title', 'label' => Yii::t('counterparty', 'Creation Properties') . ':',],

            [ 'id' => 'af_cp_creation_country', 'type' => self::FILTER_SELECT2, 'value' => '', 'label' => Yii::t('counterparty', 'Country') . ':',
                'items' => $withItems?Country::getList('nameOfficial'):[], 'lang_dependency' => true,'url' => $urlCountries, 'operation' => '=',
                'field'=>'log_empl.country_id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'counterparty_full_name').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'filtercounterparty_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],

            [ 'id' => 'af_cp_creation_city', 'type' => self::FILTER_SELECT2,'value' => '', 'label' => Yii::t('counterparty', 'City') . ':',
                'items' => $withItems?ListCity::getList('name', true):[], 'lang_dependency' => true,'url' => $urlCities,'operation' => '=',
                'field'=>'log_empl.city_id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'counterparty_full_name').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname' => 'filtercounterparty_city',
                'view_tab_title' => Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname' => 'city_{0}',
            ],

            ['id' => 'br',],

            [ 'id' => 'af_cp_creation_department', 'type' => self::FILTER_SELECT2,'value' => '','label' => Yii::t('counterparty', 'Department') . ':',
                'items' => $withItems?ListWarehouse::getListByEmplyee('name', true):[],
                'lang_dependency' => true,'url' => $urlWarehouses,'operation' => '=',
                'field'=>'log_empl.warehouse_id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'counterparty_full_name').': '.Yii::t('tab_title', 'warehouse_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/warehouse/index']),
                'select_tab_uniqname' => 'filtercounterparty_warehouse',
                'view_tab_title' => Yii::t('tab_title', 'warehouse_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/warehouse/view']),
                'view_tab_uniqname' => 'warehouse_{0}',
            ],

            [ 'id' => 'af_cp_creation_user', 'type' => self::FILTER_SELECT2, 'value' => '', 'label' => Yii::t('counterparty', 'User') . ':',
                'items' => $withItems?Employee::getListFast('surname_full', true, 'city_id IS NOT NULL AND warehouse_id IS NOT NULL AND country_id IS NOT NULL'):[],
                'lang_dependency' => true, 'url' => $urlEmployees,'operation' => '=',
                'field'=>'log_empl.id',

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'counterparty_full_name').': '.Yii::t('tab_title', 'employee_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/employee/index']),
                'select_tab_uniqname' => 'filtercounterparty_employee',
                'view_tab_title' => Yii::t('tab_title', 'employee_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/employee/view']),
                'view_tab_uniqname' => 'employee_{0}',
            ],

        ];
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

    /**
     * Возвращает список контрагентов
     * @param string $field
     * @param boolean $empty
     * @param string $lang
     * @param integer $type
     * @return array Список контрагентов
     */
    public static function getList($field = 'counterpartyName', $empty = false, $lang = null, $type = 0) {

        if (!$lang)
            $lang = Yii::$app->language;

        $modelsQuery = self::find()
                ->where('visible = ' . self::VISIBLE . ' AND state != ' . CommonModel::STATE_DELETED);

        if ($type > 0 && $type < 1000)
            $modelsQuery->andWhere('person_type = '.$type);

            // только Третьи лица
        if ($type == 1001) {
            $counterpartyTbl = self::tableName();
            $legalEntityTbl = CounterpartyLegalEntity::tableName();
            $modelsQuery->leftJoin("$legalEntityTbl le", "le.counterparty = $counterpartyTbl.id");
            $modelsQuery->andWhere('le.maybe_thirdparty = 1'); // может выступать третьей стороной
        }

        $models = $modelsQuery->all();
        
        if ($field == 'name'||$field == 'counterpartyName') {
            $field = $field . '_' . $lang;
        }

        if ($type == 1000) {
            $models = array_filter($models, function($v) {
                return $v->counterpartySignContains(6);
            });
        }

        $result = ArrayHelper::map($models, 'id', $field);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }
    
    public static function getListByAddress($field = 'counterpartyName', $empty = false, $lang = null, $andWhere = '1=1') {

        if (!$lang)
            $lang = Yii::$app->language;

        $models = self::find()
                ->where(self::tableName() . '.visible = ' . self::VISIBLE . ' AND ' . self::tableName() . '.state != ' . CommonModel::STATE_DELETED)
                ->leftJoin('{{%counterparty_manual_adress}} cma', 'cma.counterparty = ' . self::tableName() . '.id') // для адреса
                ->andWhere($andWhere)
//                ->createCommand()->rawSql; die($models);
                ->all();

        if ($field == 'name' || $field == 'counterpartyName') {
            $field = $field . '_' . $lang;
        }

        $result = ArrayHelper::map($models, 'id', $field);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    public static function getListFast($empty = false, $lang = null, $andWhere='') {

        $counterpartyTbl = Counterparty::tableName();
        $privatePersTbl = CounterpartyPrivatPers::tableName();
        $legalEntityTbl = CounterpartyLegalEntity::tableName();


        if (!$lang)
            $lang = Yii::$app->language;

        $result = (new Query())
            ->select(['id', " coalesce (le.display_name_$lang, pp.display_name_$lang) name"])
            ->from($counterpartyTbl)
            ->leftJoin("$privatePersTbl pp", "pp.counterparty = $counterpartyTbl.id")
            ->leftJoin("$legalEntityTbl le", "le.counterparty = $counterpartyTbl.id")
            ->where('visible = ' . self::VISIBLE . ' AND state != ' . CommonModel::STATE_DELETED)
            ->andWhere($andWhere)
            ->all();

        $result = ArrayHelper::map($result, 'id', 'name');

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }


    private function counterpartySignContains($sign_id){

        $result = false;

        if ($this->counterpartySign)
            foreach ($this->counterpartySign as $sign)
                if ($sign->counterparty_sign_id == $sign_id){
                    $result = true;
                    break;
                }

        return $result;
    }

    public function getIsPrivatePerson(){
        return $this->person_type == ListPersonType::PERSON_TYPE_PRIVATE;
    }

    public function getIsLegalPerson(){
        return $this->person_type == ListPersonType::PERSON_TYPE_LEGAL;
    }
    
    public function getTariffZoneId(CounterpartyManualAdress $address) {
        $city_zone = ListCity::findOne($address->city_id)->tariff_zone;
        $region1_zone = ListRegion::findOne($address->region_lvl1_id)->tariff_zone;
        $region2_zone = ListRegion::findOne($address->region_lvl2_id)->tariff_zone;
        $country_zone = Country::findOne($address->country_id)->tariff_zone;
        if(!empty($city_zone)){
            return($city_zone);
        }
        elseif (!empty($region1_zone)){
            return($region1_zone);
        }
        elseif (!empty($region2_zone)){
            return($region2_zone);
        }
        elseif (!empty($country_zone)){
            return($country_zone);
        }
        // Shipper tariff zone not found
        return null;
    }

    public static function checkUniqueCounterpartyNum($counterpartyNum) {
        return Counterparty::findOne(['counterparty_id' => $counterpartyNum]) ? Yii::t('error', "Attention! $counterpartyNum already exists") : '';
    }

    /**
     * Оплата по безналу
     * @return int;
     */
    public function getClearing()
    {
        if ($this->counterpartyLegalEntity->counterparty != null) {
            return ($this->counterpartyLegalEntity->clearing);
        }
        return 0;
    }

    /**
     * Может оплачивать третьим лицом
     * @return int;
     */
    public function getThirdparty()
    {
        if ($this->counterpartyLegalEntity->counterparty != null) {
            return ($this->counterpartyLegalEntity->thirdparty);
        }
        return 0;
    }

    /**
     * Метод для получения прикрепленных к манифесту документов
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDocs()
    {
        return $this->hasMany(AttachedDoc::className(), ['id' => 'attdoc_id'])
            ->viaTable(CpAttachedDoc::tableName(), ['cnt_id' => 'id']);
    }

    /**
     * Метод для получения прикрепленных документов всех контрактов контрагента
     * @param $counterpartyId
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDocsContracts($counterpartyId)
    {
        return (new Query())->select([AttachedDoc::tableName().'.id as attdoc_id', CounterpartyContract::tableName().'.id as contract_id'])->from([AttachedDoc::tableName()])
            ->innerJoin(CpContractAttachedDoc::tableName(), AttachedDoc::tableName().'.id = '.CpContractAttachedDoc::tableName().'.attdoc_id')
            ->innerJoin(CounterpartyContract::tableName(), CpContractAttachedDoc::tableName().'.cntcontr_id = '.CounterpartyContract::tableName().'.id')
            ->where([CounterpartyContract::tableName().'.counterparty_id' => $counterpartyId]);

    }

    /**
     * Метод получения цвета записи
     * @return string
     */
    public function getColor()
    {
        if ($this->counterpartyLegalEntity->obligor) //Должник
            return '#BF9CA2';
        if ($this->vipclient) //VIP
            return '#E9CD86';
        if (ArrayHelper::keyExists('6',$this->getCounterpartySignArray())) //Перевозчик
            return '#88FBC8';
        if (ArrayHelper::keyExists('1',$this->getCounterpartySignArray())) //Агент
            return '#04BE6B';
        if ($this->counterpartyLegalEntity->maybe_thirdparty) //может выступать третьим лицом
            return '#DC6FFB';
        return '';
    }

}

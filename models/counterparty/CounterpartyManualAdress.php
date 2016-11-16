<?php

namespace app\models\counterparty;

use Yii;
use app\models\common\CommonModel;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\address\ListAdressKind;
use app\models\dictionaries\address\ListAdressType;
use app\models\dictionaries\address\ListBuildingType;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\address\ListStreet;

/**
 * This is the model class for table "{{%counterparty_manual_adress}}".
 *
 * @property string $id
 * @property string $counterparty
 * @property string $adress_kind
 * @property string $adress_type
 * @property string $country_en
 * @property string $region_en
 * @property string $city_en
 * @property string $index
 * @property string $adress_en
 * @property string $country_ru
 * @property string $region_ru
 * @property string $city_ru
 * @property string $adress_ru
 * @property string $country_uk
 * @property string $region_uk
 * @property string $city_uk
 * @property string $adress_uk
 * @property string $addition_info
 * @property integer $primary_address
 * @property integer $state
 *
 * @property string $adress_full_en
 * @property string $adress_full_uk
 * @property string $adress_full_ru
 * @property string $adress_full
 * *
 * @property Counterparty $counterpartyModel
 * @property ListAdressKind $adressKind
 * @property ListAdressType $adressType
 *
 * @property int $country_id Ссылка на страну
 * @property int $region_lvl1_id Ссылка на регион 1го уровня
 * @property int $region_lvl2_id Ссылка на регион 2го уровня
 * @property int $city_id Ссылка на нас.пункт
 * @property int $street_id Ссылка на улицу
 * @property int $buildingtype_level1 Тип строения 1го уровня
 * @property int $buildingtype_level2 Тип строения 2го уровня
 * @property int $buildingtype_level3 Тип строения 3го уровня
 * @property int $number_level1 Номер 1го уровня
 * @property int $number_level2 Номер 2го уровня
 * @property int $number_level3 Номер 3го уровня
 * @property mixed use_manual_address_level_0
 * @property mixed use_manual_address_level_10
 * @property mixed use_manual_address_level_20
 * @property mixed use_manual_address_level_30
 * @property mixed country_name_en
 * @property Country countryModel
 * @property ListRegion region1Model
 * @property ListRegion region2Model
 * @property ListCity cityModel
 * @property ListStreet streetModel
 * @property ListBuildingType buildingtypeLevel1
 * @property ListBuildingType buildingtypeLevel2
 * @property ListBuildingType buildingtypeLevel3
 * @property string $latitude Координата latitude
 * @property string $longitude Координата longitude
 * @property string $addressName
 * @property string cityName
 * @property mixed regionName
 *
 * @property int $warehouse_id Склад - Ссылка на справочник складов
 * @property mixed cityNameWithType
 */
class CounterpartyManualAdress extends CommonModel
{
    const EMPTY_REGION = "-";
    public $use_manual_address_level_0_input;
    public $use_manual_address_level_10_input;
    public $use_manual_address_level_20_input;
    public $use_manual_address_level_30_input;

    const ADDRESS_TYPE_LEGAL = 2;
    const ADDRESS_TYPE_REGISTRATION = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_manual_adress}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['counterparty', 'adress_type', 'primary_address', 'adress_kind'], 'required'],
            [['counterparty', 'adress_kind', 'adress_type', 'primary_address', 'state'], 'integer'],
            [['country_en', 'region_en', 'city_en', 'index', 'adress_en', 'country_ru', 'region_ru', 'city_ru', 'adress_ru', 'country_uk', 'region_uk', 'city_uk', 'adress_uk', 'addition_info'], 'string', 'max' => 100],
            [['country_id_en','region_lvl1_id_en','region_lvl2_id_en','city_id_en','street_id_en',
                'buildingtype_level1_en','buildingtype_level2_en','buildingtype_level3_en', 'warehouse_id'],'integer'],
            [['use_manual_address_level_0','use_manual_address_level_10','use_manual_address_level_20','use_manual_address_level_30',], 'required'],
            [['latitude', 'longitude', 'number_level1_en','number_level2_en','number_level3_en'], 'string', 'max' => 50],

            [['country_en'], 'required', 'when' => function($model) {
                return $model->use_manual_address_level_0;
            }],
            //[['region_en','region_ru','region_uk'], 'required', 'when' => function($model) {
            //    return $model->use_manual_address_level_10;
            //}],
            [['city_en'], 'required', 'when' => function($model) {
                return $model->use_manual_address_level_20;
            }],
            [['adress_en'], 'required', 'when' => function($model) {
                return $model->use_manual_address_level_30;
            }],
            [['country_id'], 'required', 'when' => function($model) {
                return !$model->use_manual_address_level_0;
            }],
            [['region_lvl1_id_en','region_lvl2_id_en'], 'required', 'when' => function($model) {
                return !$model->use_manual_address_level_10;
            }],
            [['city_id_en'], 'required', 'when' => function($model) {
                return !$model->use_manual_address_level_20;
            }],
            [['street_id_en','buildingtype_level1_en','number_level1_en'], 'required', 'when' => function($model) {
                return !$model->use_manual_address_level_30;
            }],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('address', 'ID'),
            'counterparty' => Yii::t('address', 'Counterparty'),
            'adress_kind' => Yii::t('counterparty', 'Address Kind'),
            'adress_type' => Yii::t('address', 'Тип адреса'),
            'adress_type_name' => Yii::t('address', 'Тип адреса'),
            'country_en' => Yii::t('address', 'Country En'),
            'region_en' => Yii::t('address', 'Region En'),
            'city_en' => Yii::t('address', 'City En'),
            'index' => Yii::t('address', 'Index'),
            'adress_en' => Yii::t('address', 'Adress En'),
            'country_ru' => Yii::t('address', 'Country Ru'),
            'region_ru' => Yii::t('address', 'Region Ru'),
            'city_ru' => Yii::t('address', 'City Ru'),
            'adress_ru' => Yii::t('address', 'Adress Ru'),
            'country_uk' => Yii::t('address', 'Country Uk'),
            'region_uk' => Yii::t('address', 'Region Uk'),
            'city_uk' => Yii::t('address', 'City Uk'),
            'adress_uk' => Yii::t('address', 'Adress Uk'),
            'addition_info' => Yii::t('address', 'Доп инфо'),
            'primary_address' => Yii::t('address', 'Основной адрес'),
            'state' => Yii::t('address', 'State'),

            'adress_kind_name' => Yii::t('address', 'Adress Kind'),
            'primary_address_name' => Yii::t('address', 'Основной адрес'),
            'adress_full_en' => Yii::t('address', 'Адрес полный анг'),
            'adress_full_ru' => Yii::t('address', 'Адрес полный рус'),
            'adress_full_uk' => Yii::t('address', 'Адрес полный укр'),
            'use_manual_address_level_0' => Yii::t('address', 'Manual country'),
            'use_manual_address_level_10' => Yii::t('address', 'Manual region'),
            'use_manual_address_level_20' => Yii::t('address', 'Manual city'),
            'use_manual_address_level_30' => Yii::t('address', 'Manual address'),

            'country_id_en' => Yii::t('counterparty', 'Country'),
            'country_id_uk' => Yii::t('counterparty', 'Country'),
            'country_id_ru' => Yii::t('counterparty', 'Country'),
            'region_lvl1_id_en' => Yii::t('address', '1-st level region'),
            'region_lvl1_id_uk' => Yii::t('address', '1-st level region'),
            'region_lvl1_id_ru' => Yii::t('address', '1-st level region'),
            'region1_type_en' => Yii::t('address', '1-st level region'),

            'region_lvl2_id_en' => Yii::t('address', '2-nd level region'),
            'region_lvl2_id_uk' => Yii::t('address', '2-nd level region'),
            'region_lvl2_id_ru' => Yii::t('address', '2-nd level region'),
            'region2_type_en' => Yii::t('address', '2-nd level region'),

            'city_id_en' =>Yii::t('address', 'City'),
            'city_id_ru' =>Yii::t('address', 'City'),
            'city_id_uk' =>Yii::t('address', 'City'),
            'street_id_en' =>Yii::t('address', 'Street'),
            'street_id_ru' =>Yii::t('address', 'Street'),
            'street_id_uk' =>Yii::t('address', 'Street'),
            'buildingtype_level1_en' =>Yii::t('address', 'Buildingtype level 1'),
            'buildingtype_level1_ru' =>Yii::t('address', 'Buildingtype level 1'),
            'buildingtype_level1_uk' =>Yii::t('address', 'Buildingtype level 1'),
            'buildingtype_level2_en' =>Yii::t('address', 'Buildingtype level 2'),
            'buildingtype_level2_ru' =>Yii::t('address', 'Buildingtype level 2'),
            'buildingtype_level2_uk' =>Yii::t('address', 'Buildingtype level 2'),
            'buildingtype_level3_en' =>Yii::t('address', 'Buildingtype level 3'),
            'buildingtype_level3_ru' =>Yii::t('address', 'Buildingtype level 3'),
            'buildingtype_level3_uk' =>Yii::t('address', 'Buildingtype level 3'),
            'number_level1_en' =>Yii::t('address', 'Number level 1'),
            'number_level1_ru' =>Yii::t('address', 'Number level 1'),
            'number_level1_uk' =>Yii::t('address', 'Number level 1'),
            'number_level2_en' =>Yii::t('address', 'Number level 2'),
            'number_level2_ru' =>Yii::t('address', 'Number level 2'),
            'number_level2_uk' =>Yii::t('address', 'Number level 2'),
            'number_level3_en' =>Yii::t('address', 'Number level 3'),
            'number_level3_ru' =>Yii::t('address', 'Number level 3'),
            'number_level3_uk' =>Yii::t('address', 'Number level 3'),
            'latitude' => Yii::t('address', 'Latitude'),
            'longitude' => Yii::t('address', 'Longitude'),
            'operation' => Yii::t('app', 'Operation'),


        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового контрагента
     */
    public function generateDefaults() {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
    }

    /**
     * Метод получения доступных операция
     */
    public function getOperations() {
        if ($this->counterpartyModel->state == CommonModel::STATE_DELETED)
            return [];
        return parent::getOperations();
    }

    /**
     * Метод вызывается перед сохранением сущности
     * @param bool $insert параметр
     * @return bool результат выполнения
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            if ($insert){
                if (self::find()->where('counterparty='.$this->counterparty.' and state='.self::STATE_CREATED)->count() == 0) {
                    $this->primary_address = 1;
                }
            } elseif (self::find()->where('id<>'.$this->id.' and counterparty='.$this->counterparty.' and state='.self::STATE_CREATED)->count() == 0){
                $this->primary_address = 1;
            }
            return true;
        }

        return false;
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        if ( $this->primary_address == 1){
            self::updateAll(['primary_address'=>0], 'id<>'.$this->id.' and counterparty='.$this->counterparty);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyModel()
    {
        return $this->hasOne(Counterparty::className(), ['id' => 'counterparty']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdressKind()
    {
        return $this->hasOne(ListAdressKind::className(), ['id' => 'adress_kind']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdressType()
    {
        return $this->hasOne(ListAdressType::className(), ['id' => 'adress_type']);
    }


    public function getAdress_full_en(){
        return $this->getAdress_full('en');
    }

    public function getAdress_full_uk(){
        return $this->getAdress_full('uk');
    }

    public function getAdress_full_ru(){
        return $this->getAdress_full('ru');
    }

    public function getCountryName($lang=null)
    {
        if (!$lang)
            $lang = Yii::$app->language;

        // страна
        if (!$this->use_manual_address_level_0)
            return $this->countryModel->namesOfficial[$lang];
        else
            return $this->{"country_$lang"};
    }

    public function getRegionName($lang=null)
    {
        if (!$lang)
            $lang = Yii::$app->language;

        // регионы
        if (!$this->use_manual_address_level_10) {
            $region1 = $this->region1Model->{"name_$lang"};
            if ($region1 && $region1 != self::EMPTY_REGION)
                $result = $this->region1Model->regionType->{"name_short_$lang"}.' ' .$region1;
            else
                $result = $region1?:'';

            $region2 = $this->region2Model ? $this->region2Model->{"name_$lang"}:'';
            if ($region2 && $region2 != self::EMPTY_REGION)
                $result .=  ', ' . $this->region2Model->regionType->{"name_short_$lang"}.' '. $region2;
            else
                $result .= $region2?', ' . $region2:'';
        }
        else
            $result= $this->{"region_$lang"};

        return $result;
    }

    public function getCityName($lang=null, $typeCity = false)
    {
        if (!$lang)
            $lang = Yii::$app->language;
        $type = '';
        if($typeCity) {
            $type = $this->cityModel ? $this->cityModel->cityType->{"name_short_$lang"} : '';
        }
        // город
        if (!$this->use_manual_address_level_20)
            return $type.$this->cityModel->{"name_$lang"};
        else
            return $type.$this->{"city_$lang"};
    }

    public function getCityNameWithType($lang=null){

        if (!$lang)
            $lang = Yii::$app->language;

        $type = $this->cityModel ? $this->cityModel->cityType->{"name_short_$lang"} : '';

        if (!$this->use_manual_address_level_20)
            return $type.$this->cityModel->{"name_$lang"};
        else
            return $type.$this->{"city_$lang"};
    }

    public function getAddressName($lang=null)
    {
        if (!$lang)
            $lang = Yii::$app->language;

        // улица дом квартира и тд
        if (!$this->use_manual_address_level_30)
            return $this->getAddressShort($lang);
        else
            return $this->{"adress_$lang"};
    }

    public function getAdress_full($lang=null)
    {
        if(!$lang)
            $lang = Yii::$app->language;

        $result = '';

        // страна
        if (!$this->use_manual_address_level_0)
            $result.= $this->countryModel->namesOfficial[$lang];
        else
            $result.= $this->{"country_$lang"};

        $result .= ($this->{"index"} == '' ? '' : ', '.$this->{"index"});

        // регионы
        if (!$this->use_manual_address_level_10) {
            $region1 = $this->region1Model ? $this->region1Model->{"name_$lang"}:'';
            if ($region1 && $region1 != self::EMPTY_REGION)
                $result .= ', ' . $this->region1Model->regionType->{"name_short_$lang"}.' '. $region1;
            else
                $result .= $region1?', ' . $region1:'';

            $region2 = $this->region2Model ? $this->region2Model->{"name_$lang"}:'';
            if ($region2 && $region2 != self::EMPTY_REGION)
                $result .= ', ' . $this->region2Model->regionType->{"name_short_$lang"}.' '. $region2;
            else
                $result .= $region2?', ' . $region2:'';
        }
        else
            $result.= ($this->{"region_$lang"} == '' ? '' : ', '.$this->{"region_$lang"});

        // город
        if (!$this->use_manual_address_level_20)
            $result.= $this->cityModel?', '. $this->cityModel->{"name_$lang"}:'';
        else
            $result.=($this->{"city_$lang"} == '' ? '' : ', '.$this->{"city_$lang"});

        // улица дом квартира и тд
        if (!$this->use_manual_address_level_30) {
            $short = $this->getAddressShort($lang);
            $result .= $short? ", $short":'';
        }
        else
            $result .= ($this->{"adress_$lang"} == '' ? '' : ', '.$this->{"adress_$lang"});


        $result.= ($this->addition_info == '' ? '' : ', '.$this->addition_info);

        return $result;
    }

    /**
     * Получить краткий адрес
     * @param string $lang
     * @return string
     */
    public function getAddressShort($lang=null) {
        $addr= '';
        if (!$lang)
            $lang = Yii::$app->language;
        if (!$this->streetModel)
            return '';
        $addr .= $this->streetModel->streetTypeModel->{"name_short_$lang"} . ' ' . $this->streetModel->{"name_$lang"} . ', ' .
            $this->buildingtypeLevel1->{"name_short_$lang"} . ' ' . $this->number_level1;

        if ($this->buildingtypeLevel2)
            $addr .= ', ' . $this->buildingtypeLevel2->{"name_short_$lang"} . ' ' . $this->number_level2;
        if ($this->buildingtypeLevel3)
            $addr .= ', ' . $this->buildingtypeLevel3->{"name_short_$lang"} . ' ' . $this->number_level3;
        return $addr;
    }

    public function getAddressInput($lang = null, $showCity = false) {
        if(!$lang)
            $lang = Yii::$app->language;
        $result = '';
        // город
        if($showCity) {
            if (!$this->use_manual_address_level_20) {
                $result .= $this->cityModel ? $this->cityModel->cityType->{"name_short_$lang"} . ' ' . $this->cityModel->{"name_$lang"}.', ' : '';
            } else {
                $result .= $this->{"city_$lang"} == '' ? ', ' : $this->{"city_$lang"}.', ';
            }
        }
        // улица дом квартира и тд
        if (!$this->use_manual_address_level_30) {
            $short = $this->getAddressShort($lang);
            $result .= $short ? $short : '';
        }
        else
            $result .= ($this->{"adress_$lang"} == '' ? '' : $this->{"adress_$lang"});

        return $result;
    }

    public function getChecked($val) {
        if ($val == '1')
            return 'checked';

        return '';
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'state' => $this->state,
            'adress_kind' => $this->adress_kind,
            'adress_kind_name' => $this->adressKind->name,
            'adress_type' => $this->adress_type,
            'adress_type_name' => $this->adressType->name,
            'primary_address' => $this->primary_address,
            'primary_address_name' => $this->getChecked($this->primary_address),
            'country_en' => $this->country_en,
            'region_en' => $this->region_en,
            'city_en' => $this->city_en,
            'index' => $this->index,
            'adress_en' => $this->adress_en,
            'adress_full_en' => $this->adress_full_en,
            'country_ru' => $this->country_ru,
            'region_ru' => $this->region_ru,
            'city_ru' => $this->city_ru,
            'adress_ru' => $this->adress_ru,
            'adress_full_ru' => $this->adress_full_ru,
            'country_uk' => $this->country_uk,
            'region_uk' => $this->region_uk,
            'city_uk' => $this->city_uk,
            'adress_uk' => $this->adress_uk,
            'adress_full_uk' => $this->adress_full_uk,
            'addition_info' => $this->addition_info,
            'country_name' => $this->countryName,
            'region_name' => $this->regionName,
            'city_name' => $this->cityNameWithType,
            'address_name' => $this->addressName,
        ];
    }

    public function getUse_manual_address_level_0(){

        if ($this->use_manual_address_level_0_input !== null)
            return $this->use_manual_address_level_0_input;

        if ($this->isNewRecord)
            return false;

        return !$this->country_id;
    }

    public function getUse_manual_address_level_10(){

        if ($this->use_manual_address_level_10_input !== null)
            return $this->use_manual_address_level_10_input;

        if ($this->isNewRecord)
            return false;

        return $this->use_manual_address_level_0 || !$this->region_lvl1_id;
    }


    public function getUse_manual_address_level_20(){

        if ($this->use_manual_address_level_20_input !== null)
            return $this->use_manual_address_level_20_input;

        if ($this->isNewRecord)
            return false;

        return $this->use_manual_address_level_10 || !$this->city_id;
    }

    public function getUse_manual_address_level_30(){

        if ($this->use_manual_address_level_30_input !== null)
            return $this->use_manual_address_level_30_input;

        if ($this->isNewRecord)
            return false;


        return $this->use_manual_address_level_20 || !$this->street_id;
    }

    public function setUse_manual_address_level_0($value){
        $this->use_manual_address_level_0_input = $value;
        if ($value)
            $this->country_id = null;
    }

    public function setUse_manual_address_level_10($value){
        $this->use_manual_address_level_10_input = $value;
        if ($value) {
            $this->region_lvl1_id = null;
            $this->region_lvl2_id = null;
        }
    }

    public function setUse_manual_address_level_20($value){
        $this->use_manual_address_level_20_input = $value;
        if ($value)
           $this->city_id=null;
    }

    public function setUse_manual_address_level_30($value){
        $this->use_manual_address_level_30_input = $value;
        if ($value){
            $this->street_id = null;
            $this->buildingtype_level1 = null;
            $this->buildingtype_level2 = null;
            $this->buildingtype_level3 = null;
            $this->number_level1 = null;
            $this->number_level2 = null;
            $this->number_level3 = null;
        }

    }
    

    public static function getList($field = 'name', $empty = false, $lang = null, $andWhere = '1=1') {

        if (!$lang)
            $lang = Yii::$app->language;

        $models = self::find()
            ->where('state != ' . CommonModel::STATE_DELETED)
            ->andWhere($andWhere)
            ->all();

        if ($field == 'name') {
            $field = $field . '_' . $lang;
        }
        $result = \yii\helpers\ArrayHelper::map($models, 'id', $field);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    public function getCountry_id_ru(){
        return $this->country_id;
    }

    public function getCountry_id_uk(){
        return $this->country_id;
    }

    public function getCountry_id_en(){
        return $this->country_id;
    }

    public function setCountry_id_en($value){
        $this->country_id = $value;
    }


    public function getRegion_lvl1_id_ru(){
        return $this->region_lvl1_id;
    }

    public function getRegion_lvl1_id_uk(){
        return $this->region_lvl1_id;
    }

    public function getRegion_lvl1_id_en(){
        return $this->region_lvl1_id;
    }

    public function setRegion_lvl1_id_en($value){
        $this->region_lvl1_id = $value;
    }

    public function getRegion_lvl2_id_ru(){
        return $this->region_lvl2_id;
    }

    public function getRegion_lvl2_id_uk(){
        return $this->region_lvl2_id;
    }

    public function getRegion_lvl2_id_en(){
        return $this->region_lvl2_id;
    }

    public function setRegion_lvl2_id_en($value){
        $this->region_lvl2_id = $value;
    }

    public function getCity_id_ru(){
        return $this->city_id;
    }

    public function getCity_id_uk(){
        return $this->city_id;
    }

    public function getCity_id_en(){
        return $this->city_id;
    }

    public function setCity_id_en($value){
        $this->city_id = $value;
    }

    public function getStreet_id_ru(){
        return $this->street_id;
    }

    public function getStreet_id_uk(){
        return $this->street_id;
    }

    public function getStreet_id_en(){
        return $this->street_id;
    }

    public function setStreet_id_en($value){
        $this->street_id = $value;
    }

    public function getBuildingtype_level1_ru(){
        return $this->buildingtype_level1;
    }

    public function getNumber_level1_ru(){
        return $this->number_level1;
    }

    public function getBuildingtype_level1_uk(){
        return $this->buildingtype_level1;
    }

    public function getNumber_level1_uk(){
        return $this->number_level1;
    }

    public function getBuildingtype_level1_en(){
        return $this->buildingtype_level1;
    }

    public function setBuildingtype_level1_en($value){
        $this->buildingtype_level1 = $value;
    }

    public function getNumber_level1_en(){
        return $this->number_level1;
    }

    public function setNumber_level1_en($value){
        $this->number_level1 = $value;
    }

    public function getBuildingtype_level2_ru(){
        return $this->buildingtype_level2;
    }

    public function getNumber_level2_ru(){
        return $this->number_level2;
    }

    public function getBuildingtype_level2_uk(){
        return $this->buildingtype_level2;
    }

    public function getNumber_level2_uk(){
        return $this->number_level2;
    }

    public function getBuildingtype_level2_en(){
        return $this->buildingtype_level2;
    }

    public function setBuildingtype_level2_en($value){
        $this->buildingtype_level2 = $value;
    }

    public function getNumber_level2_en(){
        return $this->number_level2;
    }

    public function setNumber_level2_en($value){
        $this->number_level2 = $value;
    }

    public function getBuildingtype_level3_ru(){
        return $this->buildingtype_level3;
    }

    public function getNumber_level3_ru(){
        return $this->number_level3;
    }

    public function getBuildingtype_level3_uk(){
        return $this->buildingtype_level3;
    }

    public function getNumber_level3_uk(){
        return $this->number_level3;
    }

    public function getBuildingtype_level3_en(){
        return $this->buildingtype_level3;
    }

    public function getNumber_level3_en(){
        return $this->number_level3;
    }

    public function setBuildingtype_level3_en($value){
        $this->buildingtype_level3 = $value;
    }

    public function setNumber_level3_en($value){
        $this->number_level3 = $value;
    }

    public function getCountryModel(){
        return $this->hasOne(Country::className(),['id'=>'country_id']);
    }

    public function getRegion1Model() {
        return $this->hasOne(ListRegion::className(), ['id' => 'region_lvl1_id'])->one();
    }

    public function getRegion2Model() {
        return $this->hasOne(ListRegion::className(), ['id' => 'region_lvl2_id'])->one();
    }

    public function getCityModel(){
        return $this->hasOne(ListCity::className(), ['id' => 'city_id']);
    }

    public function getStreetModel(){
        return $this->hasOne(ListStreet::className(), ['id' => 'street_id']);
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

    public function getAvailableRegions1($lang){
        return ListRegion::getList('name', true, $lang,1, $this->country_id? "country = {$this->country_id}" : "false");
    }

    public function getAvailableRegions2($lang){
        return ListRegion::getList('name', true, $lang,2, $this->region_lvl1_id ? "parent_id = {$this->region_lvl1_id}":"false");
    }

    public function getAvailableCities($lang){
        return ListCity::getList('name', true, $lang, $this->country_id? "region in ( select id from ".ListRegion::tableName()." where country = {$this->country_id})":"false");
    }

    public function getAvailableStreets($lang){
        return ListStreet::getList('name', true, $lang, $this->city_id?"city = {$this->city_id}":"false");
    }
}

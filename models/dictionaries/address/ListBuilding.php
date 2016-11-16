<?php

namespace app\models\dictionaries\address;

use Yii;
use yii\helpers\Url;
use app\models\common\Langs;
use app\models\common\CommonModel;
use app\models\dictionaries\country\Country;

/**
 * This is the model class for table "yii2_list_building".
 *
 * @property string $id
 * @property string $index
 * @property string $street
 * @property string $buildingtype_level1
 * @property string $buildingtype_level2
 * @property string $buildingtype_level3
 * @property integer $state
 * @property integer $visible
 * @property string $number_level1
 * @property string $number_level2
 * @property string $number_level3
 * @property string $latitude
 * @property string $longitude
 *
 * @property ListBuildingType $buildingtypeLevel1
 * @property ListBuildingType $buildingtypeLevel2
 * @property ListBuildingType $buildingtypeLevel3
 * @property ListStreet $streetModel
 */
class ListBuilding extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yii2_list_building';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['street', 'buildingtype_level1', 'number_level1'], 'required'],
            [['street', 'buildingtype_level1', 'buildingtype_level2', 'buildingtype_level3', 'state', 'visible'], 'integer'],
            [['index'], 'string', 'max' => 20],
            [['number_level1', 'number_level2', 'number_level3', 'latitude', 'longitude'], 'string', 'max' => 50],
            ['number_level2', 'required', 'when' => function($model) { return $model->buildingtype_level2 != null; }],
            ['number_level3', 'required', 'when' => function($model) { return $model->buildingtype_level3 != null; }]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('address', 'ID'),
            'index' => Yii::t('address', 'Index'),
            'street' => Yii::t('address', 'Street'),
            'buildingtype_level1' => Yii::t('address', 'Buildingtype Level1'),
            'buildingtype_level2' => Yii::t('address', 'Buildingtype Level2'),
            'buildingtype_level3' => Yii::t('address', 'Buildingtype Level3'),
            'state' => Yii::t('address', 'State'),
            'visible' => Yii::t('address', 'Visible'),
            'number_level1' => Yii::t('address', 'Number Level1'),
            'number_level2' => Yii::t('address', 'Number Level2'),
            'number_level3' => Yii::t('address', 'Number Level3'),
            'latitude' => Yii::t('address', 'Latitude'),
            'longitude' => Yii::t('address', 'Longitude'),
        ];
    }

    public function generateDefaults() {
        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
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
     * ДЛЯ ВСЕХ СТРОЕНИЙ ВИД АДРЕСА - ДВЕРИ (id = 1)
     * @return int
     */
    public function getAddressKind() {
        return 1;
    }

    public function getAddressKindModel() {
        return ListAdressKind::findOne(['id' => $this->addressKind]);
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        //$this->saveSServiceData($insert);

        $this->operation = self::OPERATION_NONE;
    }

    public function toJson() {

        $street = $this->streetModel;

        return [
            'id' => $this->id,
            'state' => $this->state,
            'type_level1' => $this->buildingtypeLevel1->name,
            'number_level1' => $this->number_level1,
            'type_level2' => $this->buildingtypeLevel2->name,
            'number_level2' => $this->number_level2,
            'type_level3' => $this->buildingtypeLevel3->name,
            'number_level3' => $this->number_level3,
            'street_type' => $street->streetTypeModel->name,
            'street_name' => $street->name,
            'index' => $this->index,
            'city_type' => $street->cityModel->cityType->name,
            'city_name' => $street->cityModel->name,
            'region2_type' => $street->cityModel->regionModel->parent->regionType->name,
            'region2_name' => $street->cityModel->regionModel->parent->name,
            'region1_type' => $street->cityModel->regionModel->regionType->name,
            'region1_name' => $street->cityModel->regionModel->name,
            'country' => $street->cityModel->regionModel->countryModel->nameShort
        ];
    }

    public function getFilters() {

        $urlCities = Url::to('dictionaries/list-city/get-list');
        $urlRegion1 = Url::to(['dictionaries/list-region/get-list', 'level' => 1]);
        $urlRegion2 = Url::to(['dictionaries/list-region/get-list', 'level' => 2]);
        $urlCountries = Url::to(['dictionaries/country/get-list']);
        $t = $this->tableName();

        return  [

            ['id' => 'f_building_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language,
                'label' => Yii::t('app', 'Language').':', 'items' => Langs::$Names, 'lang_selector' => true],

            ['id' => 'f_building_id', 'field' => $t . '.id', 'operation' => '=', 'label' => $this->getAttributeLabel('id') . ':'],

            ['id' => 'f_building_index', 'field' => $t . '.index', 'operation' => '=', 'label' => Yii::t('address', 'Index') . ':'],

            [
                'id' => 'f_building_country', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.country',
                'items' => Country::getListFast('name_short', false, Yii::$app->language),
                'lang_dependency' => true, 'url' => $urlCountries,
                'label' => Yii::t('address', 'Country name (short)') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'building_full_name').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/country/index']),
                'select_tab_uniqname'=>'filterbuilding_country',
                'view_tab_title'=>Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/country/view']),
                'view_tab_uniqname'=>'country_{0}',
            ],

            [
                'id' => 'f_building_region1', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.parent_id',
                'items' => ListRegion::getList(), 'lang_dependency' => true, 'url' => $urlRegion1,
                'label' => Yii::t('address', '1-st level region') . ':' ,

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'building_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index']),
                'select_tab_uniqname'=>'filterbuilding_region1',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view']),
                'view_tab_uniqname'=>'region_{0}',
            ],

            [
                'id' => 'f_building_region2', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'c.region',
                'items' => ListRegion::getList('name', true, null, 2), 'lang_dependency' => true,'url' => $urlRegion2,
                'label' => Yii::t('address', '2-nd lvl region') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'building_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index2']),
                'select_tab_uniqname'=>'filterbuilding_region2',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view2']),
                'view_tab_uniqname'=>'region2_{0}',
            ],

            [
                'id' => 'f_building_city', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 's.city',
                'items' => ListCity::getList('name', false, Yii::$app->language),
                'lang_dependency' => true, 'url' => $urlCities, 'label' => Yii::t('address', 'City name') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'building_full_name').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname'=>'filterbuilding_city',
                'view_tab_title'=>Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname'=>'city_{0}',
            ],


            ['id' => 'f_building_type1', 'type' => self::FILTER_DROPDOWN, 'items' => ListBuildingType::getList('name', true, null, 1),
                'operation' => '=', 'field' => 'buildingtype_level1'],

            ['id' => 'f_building_number1', 'operation' => '=', 'field' => 'number_level1'],

            ['id' => 'f_building_type2', 'type' => self::FILTER_DROPDOWN, 'items' => ListBuildingType::getList('name', true, null, 2),
                'operation' => '=', 'field' => 'buildingtype_level2'],

            ['id' => 'f_building_number2', 'operation' => '=', 'field' => 'number_level2'],

            ['id' => 'f_building_type3', 'type' => self::FILTER_DROPDOWN, 'items' => ListBuildingType::getList('name', true, null, 3),
                'operation' => '=', 'field' => 'buildingtype_level3'],

            ['id' => 'f_building_number3', 'operation' => '=', 'field' => 'number_level3'],

            ['id' => 'f_building_state', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getStateList(true),
                'operation' => '=', 'field' => $t . '.state', 'label' => Yii::t('app', 'State') . ':'],

            ['id' => 'f_building_visible', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getVisibilityList(true),
                'operation' => '=', 'field' => $t . '.visible', 'label' => Yii::t('app', 'Visible') . ':']

        ];
    }
}

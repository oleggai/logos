<?php

namespace app\models\dictionaries\address;

use app\models\dictionaries\country\Country;
use app\models\common\Langs;
use Yii;
use app\models\common\CommonModel;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Справочник улиц
 *
 * @property string $id Код
 * @property string $street_type Тип улицы
 * @property string $name_en Наименование англ.
 * @property string $name_ru Наименование рус.
 * @property string $name_uk Наименование укр.
 * @property string $name
 * @property string $city Нас. пункт
 * @property string $begin_per_indexes Начало периода индексов
 * @property string $end_per_indexes Конец периода индексов
 * @property integer $state Состояние
 * @property integer $visible Доступность выбора
 *
 * @property ListStreetType $streetTypeModel
 * @property ListCity $cityModel
 */
class ListStreet extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_street}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(), [
            [['street_type', 'name_en', 'name_ru', 'name_uk', 'city'], 'required'],
            [['street_type', 'city', 'state', 'visible'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 100],
            [['begin_per_indexes', 'end_per_indexes'], 'string', 'max' => 20],
            [['begin_per_indexes', 'end_per_indexes'], 'validateIndexes']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('address', 'ID'),
            'street_type' => Yii::t('address', 'Street Type'),
            'name_en' => Yii::t('address', 'Name En'),
            'name_ru' => Yii::t('address', 'Name Ru'),
            'name_uk' => Yii::t('address', 'Name Uk'),
            'city' => Yii::t('address', 'City'),
            'begin_per_indexes' => Yii::t('address', 'Beginnig of period of indexes'),
            'end_per_indexes' => Yii::t('address', 'End of period of indexes'),
            'state' => Yii::t('address', 'State'),
            'visible' => Yii::t('address', 'Visible'),
        ];
    }

    public function generateDefaults($params) {
        if ($this->hasErrors())
            return null;
        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyStreet($params);
    }

    public function copyStreet($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $street = ListStreet::findOne(['id' => $params['id']]);
            if($street) {
                $this->attributes = $street->getAttributes();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStreetTypeModel() {
        return $this->hasOne(ListStreetType::className(), ['id' => 'street_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCityModel() {
        return $this->hasOne(ListCity::className(), ['id' => 'city']);
    }

    public function getName() {
        return $this->getAttribute('name_' . Yii::$app->language);
    }

    public static function getList($field = 'name', $empty = false, $lang = null, $andWhere = '1=1') {

        if (!$lang)
            $lang = Yii::$app->language;

        $models = self::find()
            ->where('visible = '.self::VISIBLE.' AND state != '.CommonModel::STATE_DELETED)
            ->andWhere($andWhere)
            ->all();

        $result =  ArrayHelper::map($models, 'id', $field . '_' . $lang);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);

        $this->operation = self::OPERATION_NONE;
    }

    public function validateIndexes($attribute) {

        if ($this->begin_per_indexes && !$this->end_per_indexes) {
            $this->addError($attribute, Yii::t('address', 'Enter End of period of indexes'));
        }
        if (!$this->begin_per_indexes && $this->end_per_indexes) {
            $this->addError($attribute, Yii::t('address', 'Enter Begin of period of indexes'));
        }
    }

    public function toJson() {

        $city = $this->cityModel;

        return [
            'id' => $this->id,
            'state' => $this->state,
            'name_ru' => $this->name_ru,
            'name_en' => $this->name_en,
            'name_uk' => $this->name_uk,
            'street_type_name' => $this->streetTypeModel->name,
            'country' => $city->regionModel->countryModel->nameShort,
            'begin_per_indexes' => $this->begin_per_indexes,
            'end_per_indexes' => $this->end_per_indexes,
            'city_type_name' => $city->cityType->name,
            'city_name' => $city->name,
            'region2_type_name' => $city->regionModel->regionType->name,
            'region2_name' => $city->regionModel->name,
            'region1_type_name' => $city->regionModel->parent->regionType->name,
            'region1_name' => $city->regionModel->parent->name,
            'visibilityText' => $this->visibilityText,
        ];
    }

    public function getFilters() {

        $urlCities = Url::to('dictionaries/list-city/get-list');
        $urlRegion1 = Url::to(['dictionaries/list-region/get-list', 'level' => 1]);
        $urlRegion2 = Url::to(['dictionaries/list-region/get-list', 'level' => 2]);
        $urlCountries = Url::to(['dictionaries/country/get-list']);

        return  [

            ['id' => 'f_street_state', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getStateList(true),
                'operation' => '=', 'field' => $this->tableName() . '.state', 'label' => Yii::t('app', 'State') . ':'],

            ['id' => 'f_street_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language,
                'label' => Yii::t('app', 'Language').':', 'items' => Langs::$Names, 'lang_selector' => true],

            ['id' => 'f_street_id', 'field' => $this->tableName().'.id', 'operation' => '=',
                'label' => $this->getAttributeLabel('id').':'],

            // Поле индекса
            ['id' => 'f_street_index', 'label' => Yii::t('address', 'Index') . ':'],

            ['id' => 'f_street_name', 'operation' => 'starts', 'field' => $this->tableName() . '.name',
                'lang_field' => true, 'label' => Yii::t('address', 'Name') . ':'],

            ['id' => 'f_street_type', 'type' => self::FILTER_DROPDOWN,
                'items' => ListStreetType::getList('name', true), 'operation' => '=', 'field' => 'street_type'],

            // Подразделение - позже будет справочник

            [
                'id' => 'f_street_city', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'city',
                'items' => ListCity::getList('name', false, Yii::$app->language),
                'lang_dependency' => true, 'url' => $urlCities,
                'label' => Yii::t('address', 'City name') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'street_full_name').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname'=>'filterstreet_city',
                'view_tab_title'=>Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname'=>'city_{0}',
            ],

            [
                'id' => 'f_street_region2', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'c1.region',
                'items' => ListRegion::getList('name', true, null, 2), 'lang_dependency' => true,'url' => $urlRegion2,
                'label' => Yii::t('address', '2-nd lvl region') . ':' ,

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'street_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index2']),
                'select_tab_uniqname'=>'filterstreet_region2',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view2']),
                'view_tab_uniqname'=>'region2_{0}',
            ],

            [
                'id' => 'f_street_region1', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.parent_id',
                'items' => ListRegion::getList(), 'lang_dependency' => true, 'url' => $urlRegion1,
                'label' => Yii::t('address', '1-st level region') . ':' ,

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'street_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index']),
                'select_tab_uniqname'=>'filterstreet_region1',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view']),
                'view_tab_uniqname'=>'region_{0}',
            ],

            [
                'id' => 'f_street_country', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.country',
                'items' => Country::getListFast('name_short', false, Yii::$app->language),
                'lang_dependency' => true, 'url' => $urlCountries,
                'label' => Yii::t('address', 'Country name (short)') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'street_full_name').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/country/index']),
                'select_tab_uniqname'=>'filterstreet_country',
                'view_tab_title'=>Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/country/view']),
                'view_tab_uniqname'=>'country_{0}',
            ],

            ['id' => 'f_city_visible', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getVisibilityList(true),
                'operation' => '=', 'field' => $this->tableName() . '.visible', 'label' => Yii::t('app', 'Visible') . ':']

        ];
    }

    public function getAvailableCities() {
        if ($this->cityModel->regionModel->id)
            return ListCity::getList('name', false, null, 'region = ' . $this->cityModel->regionModel->id);
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

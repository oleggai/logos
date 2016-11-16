<?php

namespace app\models\dictionaries\address;

use app\models\common\CommonModel;
use app\models\dictionaries\country\Country;
use app\models\common\Langs;
use app\models\dictionaries\tariff\ListTariffZone;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Справочник регионов
 *
 * @property string $id Код
 * @property string $parent_id Системный код родителя
 * @property string $name_en Наименование англ.
 * @property string $name_ru Наименование рус.
 * @property string $name_uk Наименование укр.
 * @property string $tariff_zone Тарифная зона
 * @property integer $level Уровень записи
 * @property string $region_type Тип региона
 * @property integer $state Состояние
 * @property integer $visible Доступность выбора
 * @property string $addition_info Дополнительная информация
 * @property string $country Ссылка на страну
 *
 * @property ListCity[] $listCities Список моделей стран
 * @property ListRegion $parent Родительский регион
 * @property ListRegion[] $listRegions Дочерние регионы
 * @property ListRegionType $regionType Тип региона
 * @property Country $countryModel Страна
 * @property mixed name
 */
class ListRegion extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_region}}';
    }

    /**
     * Получение ассоциативного массива регионов
     * @param string $field name
     * @param bool $empty false
     * @param string $lang null
     * @param int $level 1
     * @param string $ext_where id=field
     * @return array
     */
    public static function getList($field = 'name', $empty = false, $lang = null, $level = 1, $ext_where='1=1') {

        if (!$lang)
            $lang = Yii::$app->language;

        $models = self::find()
            ->where('visible = '.self::VISIBLE.' AND state != '.CommonModel::STATE_DELETED.' and level = '.$level)
            ->andWhere($ext_where)
            ->all();

        if($field == 'name'){
            $field = $field . '_'.$lang;
        }
        
        $result =  ArrayHelper::map($models, 'id', $field);

        if ($empty)
            $result = [null=>''] +$result;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['parent_id', 'level', 'region_type', 'state', 'visible', 'country', 'tariff_zone'], 'integer'],
            [['name_en', 'name_ru', 'name_uk', 'region_type', 'country'], 'required'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
            [['addition_info'], 'string', 'max' => 100]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('address', 'ID'),
            'parent_id' => Yii::t('address', '1-st level region'),
            'name_en' => Yii::t('address', 'Name En'),
            'name_ru' => Yii::t('address', 'Name Ru'),
            'name_uk' => Yii::t('address', 'Name Uk'),
            'level' => Yii::t('address', 'Level'),
            'region_type' => Yii::t('address', 'Region Type'),
            'state' => Yii::t('address', 'State'),
            'visible' => Yii::t('address', 'Visible'),
            'addition_info' => Yii::t('address', 'Addition Info'),
            'country' => Yii::t('app', 'Country'),
            'region_name'=> Yii::t('address', 'Region type'),
            'operation' => Yii::t('app', 'Operation'),
            'tariff_zone' => Yii::t('address', 'Tariff zone'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListCities()
    {
        return $this->hasMany(ListCity::className(), ['region' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(ListRegion::className(), ['id' => 'parent_id']);
    }
    

    /**
     * Получение модели тарифконй зоны
     * @return \yii\db\ActiveQuery
     */
    public function getTariffZone() {
        return $this->hasOne(\app\models\dictionaries\tariff\ListTariffZone::className(), ['id' => 'tariff_zone']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListRegions()
    {
        return $this->hasMany(ListRegion::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegionType()
    {
        return $this->hasOne(ListRegionType::className(), ['id' => 'region_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountryModel()
    {
        return $this->hasOne(Country::className(), ['id' => 'country']);
    }


    public function toJson($prefix=''){

        $result = [
            $prefix.'id' => $this->id,
            $prefix.'region_name' => $this->regionType->name,
            $prefix.'name_ru' => $this->name_ru,
            $prefix.'name_en' => $this->name_en,
            $prefix.'name_uk' => $this->name_uk,
            $prefix.'country_name' => $this->countryModel->nameShort,
            $prefix.'state' =>$this->state,
            $prefix.'visibilityText' =>$this->visibilityText,
            $prefix.'tariff_zone' => $this->tariffZone->{'name_' . Yii::$app->language},
        ];

        if ($this->parent != null)
            $result = $result + $this->parent->toJson('level1_');

        return $result;
    }

    public function getFilters($level = 1){

        $urlRegionTypes = Url::to(['region-type/get-list']);
        $urlCountries = Url::to(['dictionaries/country/get-list']);

        $filters =  [

            ['id' => 'f_region_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language,
                'label' => Yii::t('app','Language').':', 'items' => Langs::$Names, 'lang_selector' => true],

            ['id' => 'f_region_id', 'field' => 'id','operation' => '='],

            ['id' => 'f_region_type', 'type' => self::FILTER_DROPDOWN, 'items' => ListRegionType::getList('name', true, null, $level),
                'operation' => '=', 'field' => 'region_type', 'lang_dependency' => true, 'url' => $urlRegionTypes],

            ['id'=>'f_region_name', 'operation' => 'starts', 'field' => 'name', 'lang_field' => true, 'label' => Yii::t('address', 'Name')],

            [
                'id' => 'f_region_country',
                'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'country',
                'items' => Country::getListFast('name_short', false, Yii::$app->language),
                'lang_dependency' => true,
                'url' => $urlCountries,

                'use_select_widget' => true,
                'select_tab_title' => Yii::t('tab_title', 'region_full_name').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url' => Url::to(['dictionaries/country/index']),
                'select_tab_uniqname' => 'findregion1_country',
                'view_tab_title' => Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url' => Url::to(['dictionaries/country/view']),
                'view_tab_uniqname' => 'country_{0}',
            ],

            ['id' => 'f_region_visible', 'type' => self::FILTER_DROPDOWN, 'value' => '',
                'items' => $this->getVisibilityList(true), 'operation' => '=', 'field' => 'visible'],

            ['id' => 'f_region_state', 'type' => self::FILTER_DROPDOWN,
                'items' => $this->getStateList(true), 'operation' => '=', 'field' => 'state'],
            ['id' => 'f_tariff_zone', 'type' => self::FILTER_SELECT2, 'value' => '',
                'items' => ListTariffZone::getList(null, true), 'operation' => '=', 'field' => 'tariff_zone',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'region_full_name').': '. Yii::t('tab_title', 'Tariff zone') .' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-tariff-zone/index']),
                'select_tab_uniqname'=>'findregion_tariffzone',
                'view_tab_title'=> Yii::t('tab_title', 'Tariff zone') .' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-tariff-zone/view']),
                'view_tab_uniqname'=>'tariff_zone_{0}',
            ],
        ];

        if ($level == 2){
            $filters[]=['id' => 'f_parent_region', 'type' => self::FILTER_SELECT2, 'value' => '',
                'items' => self::getList(), 'operation' => '=', 'field' => 'parent_id',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'region_full_name').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index']),
                'select_tab_uniqname'=>'findregion_region1',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view']),
                'view_tab_uniqname'=>'region_{0}',

            ];
        }

        return $filters;
    }


    /**
     * Метод вызывается перед сохранением сущности
     * @param bool $insert параметр
     * @return bool результат выполнения
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            if ($this->level == 2 && $this->parent_id){
                $this->country = self::findOne(['id'=>$this->parent_id])->country;
            }
            return true;
        }

        return false;
    }

    public function generateDefaults($params, $level = 1) {
        if ($this->hasErrors())
            return null;
        $this->state = CommonModel::STATE_CREATED;
        $this->level = $level;

        if ($params['operation'] != null)
            $this->copyRegion($params);
    }

    public function copyRegion($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $region = ListRegion::findOne(['id' => $params['id']]);
            if($region) {
                $this->attributes = $region->getAttributes();
                $c = 0;
            }
        }
    }

    public function getName(){
        return $this->{'name_'.Yii::$app->language};
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);

        $this->operation = self::OPERATION_NONE;
    }

    public function getGridOperationsOptions(){


        if ($this->level == 2){
            return [
                self::OPERATION_VIEW => ['url' => Url::to(['view2']), 'name_for_tab'=> Yii::t('tab_title', 'view_command')],
                self::OPERATION_UPDATE => ['url' => Url::to(['update2']),'state_depend'=>[self::STATE_CREATED], 'name_for_tab'=> Yii::t('tab_title', 'edit_command') ],
                self::OPERATION_DELETE=> ['url' => Url::to(['delete']), 'state_depend'=>[self::STATE_CREATED], 'no_tab'=>true ],
                self::OPERATION_CANCEL=> ['url' => Url::to(['restore']),'state_depend'=>[self::STATE_DELETED], 'no_tab'=>true ],
                self::OPERATION_COPY => ['url' => Url::to(['create2']),  'separator_before'=>true, 'tab_name_sufix'=>'copy'],
            ];

        }

        return [
            self::OPERATION_VIEW => ['url' => Url::to(['view']), 'name_for_tab' => Yii::t('tab_title', 'view_command')],
            self::OPERATION_UPDATE => ['url' => Url::to(['update']), 'state_depend' => [self::STATE_CREATED], 'name_for_tab' => Yii::t('tab_title', 'edit_command')],
            self::OPERATION_DELETE => ['url' => Url::to(['delete']), 'state_depend' => [self::STATE_CREATED], 'no_tab' => true],
            self::OPERATION_CANCEL => ['url' => Url::to(['restore']), 'state_depend' => [self::STATE_DELETED], 'no_tab' => true],
            self::OPERATION_COPY => ['url' => Url::to(['create']), 'separator_before' => true, 'tab_name_sufix' => 'copy'],
        ];
    }

    public function getGridOperations() {
        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app', 'Copy'),
        ];
    }

}

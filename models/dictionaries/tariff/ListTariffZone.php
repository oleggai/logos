<?php

/**
 * Файл класса модели тарифных зон
 */

namespace app\models\dictionaries\tariff;

use app\models\common\CommonModel;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;
use app\models\common\Langs;
use app\models\dictionaries\country\Country;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\address\ListCity;

/**
 * Класс модели тарифных зон
 * 
 * @author Дмитрий Чеусов
 * @category Tariff
 *
 * @property string $id ID
 * @property string $name_en Наименование англ
 * @property string $name_ru Наименование рус
 * @property string $name_uk Наименование укр
 * @property string $language Язык
 * @property integer $visible Доступность выбора (1-да, 0-нет)
 * @property integer $state Состояние
 *
 * @property ListCity[] $listCities Города тарифной зоны
 * @property ListCity[] $listRegions Города тарифной зоны
 * @property ListCity[] $listCountries Города тарифной зоны
 * @property ListTariff[] $listTariffs Тарифы отправителя
 * @property ListTariff[] $listTariffs0 Тарифы получателя
 */
class ListTariffZone extends CommonModel {

    private $listCitiesInput;
    private $listRegions1Input;
    private $listRegions2Input;
    private $listCountries;
    /**
     * Название таблицы
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%list_tariff_zone}}';
    }

    /**
     * Правила валидации
     * @inheritdoc
     */
    public function rules() {
        return array_merge(parent::rules(), [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
            [['name_en'], 'match', 'pattern' => '/^[\w\s\W0-9]+[^А-яҐЄЇІґєїіЪЫЁЭъыёэ!@#\$%^*?=]+$/u'],
            [['name_uk'], 'match', 'pattern' => '/^[А-яҐЄЇІіґєї\s\W\[\]0-9]+[^A-zЪЫЁЭъыёэ!@#\$%\^\*\?=]+$/u'],
            [['name_ru'], 'match', 'pattern' => '/^[А-яЁё\s\W\[\]0-9]+[^A-zҐЄЇІґєїі!@#\$%\^\*\?=]+$/u'],
            ['visible', 'in', 'range' => [self::VISIBLE, self::INVISIBLE]],
        ]);
    }

    /**
     * Названия полей
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('tariff', 'ID'),
            'name_en' => Yii::t('tariff', 'Tariff zone name (Eng.)'),
            'name_ru' => Yii::t('tariff', 'Tariff zone name (Rus)'),
            'name_uk' => Yii::t('tariff', 'Tariff zone name (Ukr)'),
            'tariff_zone_name' => Yii::t('tariff', 'Tariff zone name'),
            'visible' => Yii::t('tariff', 'Availability of choice'),
            'state' => Yii::t('tariff', 'State'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListCities() {
        if($this->listCitiesInput) {
            return $this->listCitiesInput;
        }
        return $this->hasMany(ListCity::className(), ['tariff_zone' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListCitiesModel() {
        return $this->hasMany(ListCity::className(), ['tariff_zone' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListRegions() {
        return $this->hasMany(ListRegion::className(), ['tariff_zone' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListCountries() {
        return $this->hasMany(Country::className(), ['tariff_zone' => 'id']);
    }

    /**
     * Инициализация массива listCitiesInput связанными сущностями
     * @param $value
     */
    public function setListCities($value) {
        for ($i = 0; $i < count($value); $i++) {

            $listCity = new ListCity();

            if ($value[$i]['id'] > 0) {
                $listCity = ListCity::findOne(['id' => $value[$i]['id']]);
            }
            $listCity->load($value, $i);
            $this->listCitiesInput[] = $listCity;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListTariffsShiper() {
        return $this->hasMany(ListTariff::className(), ['tariff_zone_shiper' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListTariffsConsig() {
        return $this->hasMany(ListTariff::className(), ['tariff_zone_consig' => 'id']);
    }

    /**
     * Получение списка полей для виджета фильтрации
     * @return array массив для виджета фильтрации
     */
    public function getFilters() {

        $urlRegion1 = Url::to(['dictionaries/list-region/get-list', 'level' => 1]);
        $urlRegion2 = Url::to(['dictionaries/list-region/get-list', 'level' => 2]);
        $urlCountries = Url::to(['dictionaries/country/get-list']);
        $urlCities = Url::to(['dictionaries/list-city/get-list']);

        return [
            ['id' => 'f_tariff_zone_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language, 'label' => Yii::t('app', 'Language') . ':',
                'items' => Langs::$Names, 'lang_selector' => true],
            ['id' => ('f_tariff_zone_id'), 'operation' => '=', 'field' => $this->tableName() . '.id', 'label' => Yii::t('tariff', 'Code') . ':'],
            ['id' => 'f_tariff_zone_name', 'operation' => 'starts', 'field' => $this->tableName() . '.name', 'lang_field' => true,
                'label' => Yii::t('tariff', 'Tariff zone name') . ':'],
            [
                'id' => 'f_tariff_zone_country', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.country',
                'items' => Country::getListFast('name_short', false, Yii::$app->language),
                'lang_dependency' => true, 'url' => $urlCountries,
                'label' => Yii::t('address', 'Country') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'Tariff zone').': '.Yii::t('tab_title', 'country_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/country/index']),
                'select_tab_uniqname'=>'filtertariffzone_country',
                'view_tab_title'=>Yii::t('tab_title', 'country_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/country/view']),
                'view_tab_uniqname'=>'country_{0}',

            ],
            [
                'id' => 'f_tariff_zone_region1', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'r2.parent_id',
                'items' => ListRegion::getList(), 'lang_dependency' => true, 'url' => $urlRegion1,
                'label' => Yii::t('address', '1-st level region') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'Tariff zone').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index']),
                'select_tab_uniqname'=>'filtertariffzone_region1',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view']),
                'view_tab_uniqname'=>'region_{0}',
            ],
            [
                'id' => 'f_tariff_zone_region2', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'region',
                'items' => ListRegion::getList('name', true, null, 2), 'lang_dependency' => true, 'url' => $urlRegion2,
                'label' => Yii::t('address', '2-nd lvl region') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'Tariff zone').': '.Yii::t('tab_title', 'region_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-region/index2']),
                'select_tab_uniqname'=>'filtertariffzone_region2',
                'view_tab_title'=>Yii::t('tab_title', 'region_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-region/view2']),
                'view_tab_uniqname'=>'region2_{0}',
            ],
            [
                'id' => 'f_tariff_zone_city', 'type' => self::FILTER_SELECT2, 'operation' => '=', 'field' => 'c2.id',
                'items' => ListCity::getList(), 'lang_dependency' => true, 'url' => $urlCities,
                'label' => Yii::t('address', 'City') . ':',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'Tariff zone').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-city/index']),
                'select_tab_uniqname'=>'filtertariffzone_city',
                'view_tab_title'=>Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-city/view']),
                'view_tab_uniqname'=>'city_{0}',
            ],
            ['id' => 'f_tariff_zone_visible',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'items' => $this->getVisibilityList(true),
                'operation' => '=',
                'field' => 'visible',
            ],
            ['id' => 'f_tariff_zone_state',
                'type' => self::FILTER_DROPDOWN,
                'value' => '',
                'items' => $this->getStateList(true),
                'operation' => '=',
                'field' => 'state',
            ],
        ];
    }

    /**
     * Получение списка тарифных зон в виде ассоциативного массива, где ключ - id, 
     * значение - значение поля переданного параметром ('name_en' по-умолчанию)
     * @param string $field поле для отображения
     * @param boolean $empty
     * @return array массив зон
     */
    public static function getList($field = null, $empty = false) {
        if ($field == null) {
            $field = 'name_' . Yii::$app->language;
        }
        $arr = ListTariffZone::find()
                ->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])
                ->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty) {
            $r = [null => ''] + $r;
        }
        return $r;
    }

    /**
     * Формирование полей по-умолчанию
     * @param $params
     */
    public function generateDefaults($params) {
        if ($this->hasErrors()) {
            return;
        }
        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyTariffZone($params);
    }

    /**
     * Копирование тарифных зон
     * @param $params
     */
    public function copyTariffZone($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $tariffZone = ListTariffZone::findOne(['id' => $params['id']]);
            if($tariffZone) {
                $this->attributes = $tariffZone->getAttributes();
                $this->listCities = $tariffZone->listCities;
                $this->listRegions1 = $tariffZone->listRegions1;
                $this->listRegions2 = $tariffZone->listRegions2;
                $this->listCountries = $tariffZone->listCountries;

            }
        }
    }

    /**
     * Метод после сохранения модели
     * Содержит функионал добавления тарифа в список городов
     * @param boolean $insert whether this method called while inserting a record.
     * @param array $changedAttributes The old values of attributes that had changed and were saved.
     */
    public function afterSave($insert, $changedAttributes) {

        $cities = [];
        
        // Сабмит грида
        $items = Yii::$app->request->post()['ListTariffZone']['entries'];
        
        ListCity::updateAll(['tariff_zone' => null], 'tariff_zone = ' . $this->id);
        ListRegion::updateAll(['tariff_zone' => null], 'tariff_zone = ' . $this->id);
        Country::updateAll(['tariff_zone' => null], 'tariff_zone = ' . $this->id);
        
        if (!empty($items)) {
            foreach ($items as $item) {
                if (!empty($item['city'])) {
                    ListCity::updateAll(['tariff_zone' => $this->id], "id = '" . (int) $item['city'] . "'");
                } elseif (!empty($item['region2'])) {
                    ListRegion::updateAll(['tariff_zone' => $this->id], "id = '" . (int) $item['region2'] . "'");
                } elseif (!empty($item['region1'])) {
                    ListRegion::updateAll(['tariff_zone' => $this->id], "id = '" . (int) $item['region1'] . "'");
                } elseif (!empty($item['country'])) {
                    Country::updateAll(['tariff_zone' => $this->id], "id = '" . (int) $item['country'] . "'");
                }
            }
        }
        
        $this->saveSServiceData($insert);
        return parent::afterSave($insert, $changedAttributes);
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
     * Возвращает вхождения
     * @return array вхождения
     */
    public function getEntries()
    {
        if ($this->getIsNewRecord()) {
            return [];
        }
        
        $cities = ListCity::find()
                ->where(['tariff_zone' => $this->id])
                ->all();
        $regions = ListRegion::find()
                ->where(['tariff_zone' => $this->id])
                ->all();
        $countries = Country::find()
                ->where(['tariff_zone' => $this->id])
                ->all();

        $result = [];
        
        foreach ($countries as $country) {
            $result[] = [
                'state' => $country->state,
                'city' => '',
                'region2' => '',
                'region1' => '',
                'country' => $country->id,
            ];
        }
        
        foreach ($regions as $region) {
            $result[] = [
                'state' => $region->state,
                'city' => '',
                'region2' => $region->level == 2 ? $region->id : null,
                'region1' => $region->level == 2 ? $region->parent_id : $region->id,
                'country' => $region->country,
            ];
        }
        
        foreach ($cities as $city) {
            $result[] = [
                'state' => $city->state,
                'city' => $city->id,
                'region2' => $city->region,
                'region1' => $city->regionModel ? $city->regionModel->parent_id : null,
                'country' => $city->regionModel ? $city->regionModel->country : null,
            ];
        }
        
        return $result;
    }
    
}

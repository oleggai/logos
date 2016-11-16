<?php

/**
 * Файл класса модели тарифов
 */

namespace app\models\dictionaries\tariff;

use app\models\common\CommonModel;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Yii;
use app\models\common\Langs;
use app\models\dictionaries\currency\Currency;
use app\models\dictionaries\delivery\ListTariffDeliveryType;
use app\models\dictionaries\service\ListTariffServiceType;
use app\models\dictionaries\shipment\ListTariffShipmentFormat;
use app\models\dictionaries\warehouse\ListCargoType;
use app\models\dictionaries\delivery\DeliveryType;
use app\models\dictionaries\service\ServiceType;
use app\models\dictionaries\shipment\ShipmentFormat;
use app\models\common\ShortDateFormatBehavior;
use app\models\common\Setup;

/**
 * Класс модели тарифов
 * 
 * @author Дмитрий Чеусов
 * @category Tariff
 *
 * @property string $id ID
 * @property string $name_en Название тарифной зоны (Англ.)
 * @property string $name_ru Название тарифной зоны (Рус.)
 * @property string $name_uk Название тарифной зоны (Укр.)
 * @property string $tariff_zone_shiper Ссылка на тарифную зону (отправителя)
 * @property string $tariff_zone_consig Ссылка на тарифную зону (получателя)
 * @property string $cargo_type_id Ссылка на справочник типов грузов
 * @property string $calc_weight_from Расчетный вес, кг от
 * @property string $calc_weight_to Расчетный вес, кг до
 * @property string $cust_declare_cost_from Стоимость таможенного декларирования от
 * @property string $cust_declare_cost_to Стоимость таможенного декларирования до
 * @property string $cust_declare_currency Валюта стоимости таможенного декларирования
 * @property string $cost_premium_cust_declare Стоимость надбавки от стоимости таможенного декларирования
 * @property string $date_action_from Дата действия с
 * @property string $date_action_to Дата действия по
 * @property integer $visible Доступность выбора (1-да, 0-нет)
 * @property integer $state Состояние
 * @property string $cost Стоимость
 * @property string $cost_currency Валюта стоимости
 *
 * "property Currency $costCurrency Валюта стоимости
 * @property ListTariffZone $tariffZoneShiper Тарифная зона отправителя
 * @property ListTariffZone $tariffZoneConsig Тарифная зона получателя
 * @property ListCargoType $cargoType Тип груза
 * @property Currency $custDeclareCurrency Валюта стоимости таможенного декларирования
 * 
 * @property ListTariffDeliveryType[] $listTariffDeliveryTypes Вид доставки
 * @property ListTariffServiceType[] $listTariffServiceTypes Тип услуги
 * @property ListTariffShipmentFormat[] $listTariffShipmentFormats Формат отправления
 * 
 */
class ListTariff extends CommonModel {

    /**
     * @var ListTariffDeliveryType[] $listTariffDeliveryTypesInput переменная для хранения связанных сущностей ListTariffDeliveryType
     */
    private $listTariffDeliveryTypesInput;
    //private $listTariffDeliveryTypesInputAppend;
    /**
     * @var ListTariffServiceType[] $listTariffServiceTypesInput переменная для хранения связанных сущностей ListTariffServiceType
     */
    private $listTariffServiceTypesInput;
    //private $listTariffServiceTypesInputAppend;
    /**
     * @var ListTariffShipmentFormat[] $listTariffShipmentFormatsInput переменная для хранения связанных сущностей ListTariffShipmentFormat
     */
    private $listTariffShipmentFormatsInput;
    //private $listTariffShipmentFormatsInputAppend;

    /**
     * Название таблицы
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%list_tariff}}';
    }

    /**
     * Правила валидации
     * @inheritdoc
     */
    public function rules() {
        return array_merge(parent::rules(), [
            [['name_en', 'name_ru', 'name_uk', 'tariff_zone_shiper', 'tariff_zone_consig', 'calc_weight_from',
                'calc_weight_to', 'cost', 'date_action_from', 'date_action_to', 'cargo_type_id', 'cust_declare_cost_from',
                'cust_declare_cost_to', 'cost_premium_cust_declare', 'costCurrency', 'custDeclareCurrency'], 'required'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
            [['name_en'], 'match', 'pattern' => '/^[\w\s\W0-9]+[^А-яҐЄЇІґєїіЪЫЁЭъыёэ!@#\$%^*?=]+$/u'],
            [['name_uk'], 'match', 'pattern' => '/^[А-яҐЄЇІіґєї\s\W\[\]0-9]+[^A-zЪЫЁЭъыёэ!@#\$%\^\*\?=]+$/u'],
            [['name_ru'], 'match', 'pattern' => '/^[А-яЁё\s\W\[\]0-9]+[^A-zҐЄЇІґєїі!@#\$%\^\*\?=]+$/u'],
            [['calc_weight_from', 'calc_weight_to', 'cust_declare_cost_from', 'cust_declare_cost_to', 'cost_premium_cust_declare', 'cost'], 'number'],
            [['tariff_zone_shiper', 'tariff_zone_consig', 'cargo_type_id', 'cust_declare_currency', 'visible', 'state', 'cost_currency'], 'integer'],
            [['date_action_from', 'date_action_to'], 'validateDate'],
            [['date_action_from', 'date_action_to'], 'validateDateFromTo'],
            [['calc_weight_from', 'calc_weight_to'], 'validateWeightFromTo'],
            [['cust_declare_cost_from', 'cust_declare_cost_to'], 'validateCostFromTo'],
            [['listTariffDeliveryTypes'], 'validateListTariffDeliveryTypes'],
            [['listTariffServiceTypes'], 'validateListTariffServiceTypes'],
            [['listTariffShipmentFormats'], 'validateListTariffShipmentFormats'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('tariff', 'ID'),
            'name_en' => Yii::t('tariff', 'Tariff name (Eng.)'),
            'name_ru' => Yii::t('tariff', 'Tariff name (Rus.)'),
            'name_uk' => Yii::t('tariff', 'Tariff name (Ukr.)'),
            'tariff_zone_shiper' => Yii::t('tariff', 'Shipper’s tariff zone'),
            'tariff_zone_consig' => Yii::t('tariff', 'Consignee’s tariff zone'),
            'tariffZoneShiper' => Yii::t('tariff', 'Shipper’s tariff zone'),
            'tariffZoneConsig' => Yii::t('tariff', 'Consignee’s tariff zone'),
            'cargo_type_id' => Yii::t('tariff', 'Shipment type'),
            'cargoType' => Yii::t('tariff', 'Shipment type'),
            'calc_weight_from' => Yii::t('tariff', 'Calculated weight, kg from'),
            'calc_weight_to' => Yii::t('tariff', 'Calculated weight, kg to'),
            'cust_declare_cost_from' => Yii::t('tariff', 'Customs declaration cost from'),
            'cust_declare_cost_to' => Yii::t('tariff', 'Customs declaration cost to'),
            'cust_declare_currency' => Yii::t('tariff', 'Currency of customs declaration cost'),
            'custDeclareCurrency' => Yii::t('tariff', 'Currency of customs declaration cost'),
            'cost_premium_cust_declare' => Yii::t('tariff', 'The cost of premiums of the cost of customs declaration') . ', %',
            'date_action_from' => Yii::t('tariff', 'Date action from'),
            'date_action_to' => Yii::t('tariff', 'Date action to'),
            'listTariffDeliveryTypes' => Yii::t('tariff', 'Type of delivery'),
            'listTariffServiceTypes' => Yii::t('tariff', 'Service type'),
            'listTariffShipmentFormats' => Yii::t('tariff', 'Format of shipment'),
            'cost' => Yii::t('app', 'Cost'),
            'cost_currency' => Yii::t('app', 'Currency'),
            'costCurrency' => Yii::t('app', 'Currency'),
            'visible' => Yii::t('tariff', 'Availability of choice'),
            'state' => Yii::t('tariff', 'State'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCostCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'cost_currency']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariffZoneShiper() {
        return $this->hasOne(ListTariffZone::className(), ['id' => 'tariff_zone_shiper']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariffZoneConsig() {
        return $this->hasOne(ListTariffZone::className(), ['id' => 'tariff_zone_consig']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCargoType() {
        return $this->hasOne(ListCargoType::className(), ['id' => 'cargo_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustDeclareCurrency() {
        return $this->hasOne(Currency::className(), ['id' => 'cust_declare_currency']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListTariffDeliveryTypes() {

        if(!$this->listTariffDeliveryTypesInput) {
            $this->listTariffDeliveryTypesInput = $this->hasMany(ListTariffDeliveryType::className(), ['tariff_id' => 'id'])->all();
        }
        return $this->listTariffDeliveryTypesInput;
    }

    /**
     * Инициализация массива listTariffDeliveryTypesInput связанными сущностями
     * @param $value
     */
    public function setListTariffDeliveryTypes($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        //$this->listTariffDeliveryTypesInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        foreach ($value as $key => $val) {

            $type = new ListTariffDeliveryType();

            if ($val['id'] > 0)
                $type = ListTariffDeliveryType::findOne(['id' => $val['id']]);

            $type->load($value, $key);
            $this->listTariffDeliveryTypesInput[] = $type;
        }
    }

    public function validateListTariffDeliveryTypes() {

        if ($this->listTariffDeliveryTypesInput) {

            $keys = [];
            foreach ($this->listTariffDeliveryTypesInput as $type) {
                if (!$type->validate()) {
                    $this->addErrors($type->errors);
                    break;
                }
                $keys[] = $type->delivery_type;
            }

            if (count($keys) > count(array_unique($keys))) {
                $this->addError('listTariffDeliveryTypes', Yii::t('error',
                    (new ListTariffDeliveryType())->getAttributeLabel('delivery_type') . ' ' . 'должно быть уникальным значением'));
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListTariffServiceTypes() {

        if (!$this->listTariffServiceTypesInput) {
            $this->listTariffServiceTypesInput = $this->hasMany(ListTariffServiceType::className(), ['tariff_id' => 'id'])->all();
        }

        return $this->listTariffServiceTypesInput;

        /*if($this->listTariffServiceTypesInput) {
            return $this->listTariffServiceTypesInput;
        }
        return $this->hasMany(ListTariffServiceType::className(), ['tariff_id' => 'id']);*/
    }

    /**
     * Инициализация массива listTariffServiceTypesInput связанными сущностями
     * @param $value
     */
    public function setListTariffServiceTypes($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        //$this->listTariffServiceTypesInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        foreach ($value as $key => $val) {

            $type = new ListTariffServiceType();

            if ($val['id'] > 0)
                $type = ListTariffServiceType::findOne(['id' => $val['id']]);

            $type->load($value, $key);
            $this->listTariffServiceTypesInput[] = $type;
        }
    }

    public function validateListTariffServiceTypes() {

        if ($this->listTariffServiceTypesInput) {

            $keys = [];
            foreach ($this->listTariffServiceTypesInput as $type) {
                if (!$type->validate()) {
                    $this->addErrors($type->errors);
                    break;
                }
                $keys[] = $type->service_type;
            }

            if (count($keys) > count(array_unique($keys))) {
                $this->addError('listTariffServiceTypes', Yii::t('error',
                    (new ListTariffServiceType())->getAttributeLabel('service_type') . ' ' . 'должно быть уникальным значением'));
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListTariffShipmentFormats() {

        if (!$this->listTariffShipmentFormatsInput) {
            $this->listTariffShipmentFormatsInput = $this->hasMany(ListTariffShipmentFormat::className(), ['tariff_id' => 'id'])->all();
        }
        return $this->listTariffShipmentFormatsInput;
    }
    /**
     * Инициализация массива listTariffShipmentFormatsInput связанными сущностями
     * @param $value
     */

    public function setListTariffShipmentFormats($value) {

        // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
        //$this->listTariffShipmentFormatsInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

        foreach ($value as $key => $val) {

            $type = new ListTariffShipmentFormat();

            if ($val['id'] > 0)
                $type = ListTariffShipmentFormat::findOne(['id' => $val['id']]);

            $type->load($value, $key);
            $this->listTariffShipmentFormatsInput[] = $type;
        }
    }

    public function validateListTariffShipmentFormats() {

        if ($this->listTariffShipmentFormatsInput) {

            $keys = [];
            foreach ($this->listTariffShipmentFormatsInput as $format) {
                if (!$format->validate()) {
                    $this->addErrors($format->errors);
                    break;
                }
                $keys[] = $format->shipment_format;
            }

            if (count($keys) > count(array_unique($keys))) {
                $this->addError('listTariffShipmentFormats', Yii::t('error',
                    (new ListTariffShipmentFormat())->getAttributeLabel('shipment_format') . ' ' . 'должно быть уникальным значением'));
            }
        }
    }

    /**
     * Получение списка полей для виджета фильтрации
     * @return array массив для виджета фильтрации
     */
    public function getFilters() {

        return [
            ['id' => 'f_tariff_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language, 'label' => Yii::t('app', 'Language') . ':',
                'items' => Langs::$Names, 'lang_selector' => true],
            ['id' => ('f_tariff_id'), 'operation' => '=', 'field' => ('yii2_list_tariff.id'),
                'label' => Yii::t('tariff', 'Code') . ':'],
            ['id' => 'f_tariff_name', 'operation' => 'starts', 'field' => $this->tableName() . '.name', 'lang_field' => true,
                'label' => Yii::t('tariff', 'Tariff name') . ':'],
            ['id' => 'f_tariff_zone_shiper', 'type' => self::FILTER_DROPDOWN,
                'items' => ListTariffZone::getList(null, true),
                'operation' => '=',
                'field' => 'tariff_zone_shiper',
                'value' => '',
                'label' => Yii::t('tariff', 'Shipper’s tariff zone') . ':',
            ],
            ['id' => 'f_tariff_zone_consig', 'type' => self::FILTER_DROPDOWN,
                'items' => ListTariffZone::getList(null, true),
                'operation' => '=',
                'field' => 'tariff_zone_consig',
                'value' => '',
                'label' => Yii::t('tariff', 'Consignee’s tariff zone') . ':',
            ],
            ['id' => 'f_cargo_type', 'type' => self::FILTER_DROPDOWN,
                'items' => ListCargoType::getList(true),
                'operation' => '=',
                'field' => 'cargo_type_id',
                'value' => '',
                'label' => Yii::t('tariff', 'Shipment type') . ':',
            ],
            ['id' => 'f_tariff_zone_delivery', 'type' => self::FILTER_DROPDOWN,
                'items' => DeliveryType::getList(null, true),
                'operation' => '=',
                'field' => 'yii2_list_tariff_delivery_type.delivery_type',
                'value' => '',
                'label' => Yii::t('tariff', 'Type of delivery') . ':',
            ],
            ['id' => 'f_tariff_zone_service', 'type' => self::FILTER_DROPDOWN,
                'items' => ServiceType::getList(true),
                'operation' => '=',
                'field' => 'yii2_list_tariff_service_type.service_type',
                'value' => '',
                'label' => Yii::t('tariff', 'Service type') . ':',
            ],
            ['id' => 'f_tariff_zone_shipment', 'type' => self::FILTER_DROPDOWN,
                'items' => ShipmentFormat::getList(null, true),
                'operation' => '=',
                'field' => 'yii2_list_tariff_shipment_format.shipment_format',
                'value' => '',
                'label' => Yii::t('tariff', 'Format of shipment') . ':',
            ],
            ['id' => 'f_tariff_state', 'type' => self::FILTER_DROPDOWN,
                'items' => $this->getStateList(true), 'operation' => '=', 'field' => 'state'],
            ['id' => 'f_tariff_visible', 'type' => self::FILTER_DROPDOWN, 'value' => '',
                'items' => $this->getVisibilityList(true), 'operation' => '=', 'field' => 'visible'],
        ];
    }


    /**
     * Формирование полей по-умолчанию, перед созданием нового тарифа
     * @param $params
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyTariff($params);
    }

    /**
     * Копирование Тарифа на основании существующего
     * @param $params
     */
    public function copyTariff($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $tariff = ListTariff::findOne(['id' => $params['id']]);
            if($tariff) {
                $this->attributes = $tariff->getAttributes();
                $this->listTariffDeliveryTypes = $tariff->listTariffDeliveryTypes;
                $this->listTariffServiceTypes = $tariff->listTariffServiceTypes;
                $this->listTariffShipmentFormats = $tariff->listTariffShipmentFormats;

            }
        }
    }

    /**
     * Метод после загрузки
     * @return bool
     */
    public function afterFind() {
        $this->date_action_from = date(Setup::DATE_FORMAT, strtotime($this->date_action_from));
        $this->date_action_to = date(Setup::DATE_FORMAT, strtotime($this->date_action_to));
        return  parent::afterFind();
    }

    /**
     * Метод перед сохранением
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert) {
        $this->date_action_from = date(Setup::MYSQL_DATE_FORMAT, strtotime($this->date_action_from));
        $this->date_action_to = date(Setup::MYSQL_DATE_FORMAT, strtotime($this->date_action_to));
        return  parent::beforeSave($insert);
    }

    /**
     * Метод после сохранения
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        //$this->saveSServiceData($insert);

        $this->saveTariffDeliveryTypes();
        $this->saveTariffServiceTypes();
        $this->saveShipmentFormats();

        $this->saveSServiceData($insert);
    }

    /**
     * Сохранение связаных DeliveryTypes
     */
    public function saveTariffDeliveryTypes() {

        $typeInBase = $this->hasMany(ListTariffDeliveryType::className(), ['tariff_id' => 'id'])->all();
        $typeSaved = [];

        if ($this->listTariffDeliveryTypesInput) {

            foreach ($this->listTariffDeliveryTypesInput as $type) {
                $type->tariff_id = $this->id;
                $type->save();
                $typeSaved[] = $type->id;
            }
        }

        //if (!$this->listTariffDeliveryTypesInputAppend) {
            foreach ($typeInBase as $type) {
                if (!in_array($type->id, $typeSaved))
                    $type->delete(false);
            }
        //}

        /*$listTariffDeliveryTypes = Yii::$app->request->post()['ListTariff']['listTariffDeliveryTypes'];

        if ($listTariffDeliveryTypes) {
            ListTariffDeliveryType::deleteAll(['tariff_id' => $this->id]);
            foreach ($listTariffDeliveryTypes as $deliveryType) {
                $deliveryTypeNew = new ListTariffDeliveryType();
                $deliveryTypeNew->tariff_id = $this->id;
                $deliveryTypeNew->delivery_type = $deliveryType['delivery_type'];
                $deliveryTypeNew->save();
            }
        }*/
    }

    /**
     * Сохранение связаных ServiceTypes
     */
    public function saveTariffServiceTypes() {

        $typeInBase = $this->hasMany(ListTariffServiceType::className(), ['tariff_id' => 'id'])->all();
        $typeSaved = [];

        if ($this->listTariffServiceTypesInput) {

            foreach ($this->listTariffServiceTypesInput as $type) {
                $type->tariff_id = $this->id;
                $type->save();
                $typeSaved[] = $type->id;
            }
        }

        //if (!$this->listTariffServiceTypesInputAppend) {
            foreach ($typeInBase as $type) {
                if (!in_array($type->id, $typeSaved))
                    $type->delete(false);
            }

        //}

        /*$listTariffServiceTypes = Yii::$app->request->post()['ListTariff']['listTariffServiceTypes'];

        if ($listTariffServiceTypes) {
            ListTariffServiceType::deleteAll(['tariff_id' => $this->id]);
            foreach ($listTariffServiceTypes as $serviceType) {
                $serviceTypeNew = new ListTariffServiceType();
                $serviceTypeNew->tariff_id = $this->id;
                $serviceTypeNew->service_type = $serviceType['service_type'];
                $serviceTypeNew->save();
            }
        }*/
    }

    /**
     * Сохранение связаных ShipmentFormats
     */
    public function saveShipmentFormats() {

        $formatInBase = $this->hasMany(ListTariffShipmentFormat::className(), ['tariff_id' => 'id'])->all();
        $formatSaved = [];

        if ($this->listTariffShipmentFormatsInput) {

            foreach ($this->listTariffShipmentFormatsInput as $format) {
                $format->tariff_id = $this->id;
                $format->save();
                $formatSaved[] = $format->id;
            }
        }

        //if (!$this->listTariffShipmentFormatsInputAppend) {
            foreach ($formatInBase as $format) {
                if (!in_array($format->id, $formatSaved))
                    $format->delete(false);
            }
        //}

        /*$listTariffShipmentFormats = Yii::$app->request->post()['ListTariff']['listTariffShipmentFormats'];

        if ($listTariffShipmentFormats) {
            ListTariffShipmentFormat::deleteAll(['tariff_id' => $this->id]);
            foreach ($listTariffShipmentFormats as $shipmentFormat) {
                $shipmentFormatNew = new ListTariffShipmentFormat();
                $shipmentFormatNew->tariff_id = $this->id;
                $shipmentFormatNew->shipment_format = $shipmentFormat['shipment_format'];
                $shipmentFormatNew->save();
            }
        }*/
    }

    /**
     * Проверка даты [['date_action_from', 'date_action_to'], 'validateShort'],
     * @todo вынести в общие валидаторы
     * @param string $attribute Имя аттрибута
     * @param array $params параметры валидации
     */
    public function validateDate($attribute, $params) {
        $value = Yii::$app->request->post(end(explode('\\', __CLASS__)))[$attribute];
        if ((!ShortDateFormatBehavior::validate($value)) 
                || empty($value) 
                || strpos($value, '_') 
                || (int) $value == 0) {
            $this->addError($attribute, Yii::t('app', 'Date format error'));
        }
    }

    /**
     * [['date_action_from', 'date_action_to'], 'validateDateFromTo'],
     * @todo обобщить в метод validateFromTo($attribute, $params) и вынести в общие валидаторы
     */
    public function validateDateFromTo() {
        if (strtotime($this->date_action_from) > strtotime($this->date_action_to)) {
            $this->addError('date_action_from', Yii::t('app', 'Date from must be less than Date to'));
        }
    }

    /**
     * [['calc_weight_from', 'calc_weight_to'], 'validateWeightFromTo'],
     */
    public function validateWeightFromTo() {
        if ($this->calc_weight_from > $this->calc_weight_to) {
            $this->addError('calc_weight_from', Yii::t('app', 'Weight from must be less than Weight to'));
        }
    }

    /**
     * [['cust_declare_cost_from', 'cust_declare_cost_to'], 'validateCostFromTo'],
     */
    public function validateCostFromTo() {
        if ($this->cust_declare_cost_from > $this->cust_declare_cost_to) {
            $this->addError('cust_declare_cost_from', Yii::t('app', 'Cost from must be less than Cost to'));
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
    /**
     * Список использованных идентификаторов в перечне типов доставки связанных с тарифом
     */
    public function getTariffDeliveryTypes() {
        $inp = $this->listTariffDeliveryTypes;
        if(!$inp)
            return '';
        return CommonModel::array2string(ArrayHelper::map($inp, 'id', 'delivery_type'));
    }
    /**
     * Список использованных идентификаторов в перечне типов услуг связанных с тарифом
     */
    public function getTariffServiceTypes() {
        $inp = $this->listTariffServiceTypes;
        if(!$inp)
            return '';
        return CommonModel::array2string(ArrayHelper::map($inp, 'id', 'service_type'));
    }
}

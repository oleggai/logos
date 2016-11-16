<?php

namespace app\models\dictionaries\warehouse;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;

/**
 * Модель для зон подразделения
 * @author Richok FG
 * @category warehouse
 *
 * @property string $id
 * @property string $warehouse
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $actual_weight_from
 * @property string $actual_weight_to
 * @property string $lenght_from
 * @property string $lenght_to
 * @property string $width_from
 * @property string $width_to
 * @property string $height_from
 * @property string $height_to
 * $property string $name
 *
 * @property ListWarehouseCamera[] $listWarehouseCameras
 * @property ListWarehouseRoute[] $listWarehouseRoutes
 * @property ListCargoType $cargoType
 * @property ListWarehouse $warehouseModel
 */
class ListWarehouseZone extends CommonModel
{

    public $zoneCtInput;

    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%list_warehouse_zone}}';
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['warehouse', 'cargo_type'], 'integer'],
            [['actual_weight_from', 'actual_weight_to', 'lenght_from', 'lenght_to', 'width_from', 'width_to', 'height_from', 'height_to'], 'number'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50]
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
            'warehouse' => Yii::t('warehouse', 'Warehouse'),
            'name_en' => Yii::t('warehouse', 'Zone name (Eng.)'),
            'name_ru' => Yii::t('warehouse', 'Zone name (Rus.)'),
            'name_uk' => Yii::t('warehouse', 'Zone name (Ukr.)'),
            //'cargo_type' => Yii::t('warehouse', 'Cargo type'),
            'actual_weight_from' => Yii::t('warehouse', 'Actual weight from, kg'),
            'actual_weight_to' => Yii::t('warehouse', 'Actual weight to, kg'),
            'lenght_from' => Yii::t('warehouse', 'Length from, m'),
            'lenght_to' => Yii::t('warehouse', 'Length to, m'),
            'width_from' => Yii::t('warehouse', 'Width from, m'),
            'width_to' => Yii::t('warehouse', 'Width to, m'),
            'height_from' => Yii::t('warehouse', 'Height from, m'),
            'height_to' => Yii::t('warehouse', 'Height to, m'),
        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием новой зоны
     */
    public function generateDefaults() {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
    }

    /**
     * Получить название на текущем языке
     * @return mixed
     */
    public function getName() {
        return $this->getAttribute('name_' . Yii::$app->language);
    }

    /**
     * Получить все камер в этой зоне
     * @return \yii\db\ActiveQuery
     */
    public function getListWarehouseCameras()
    {
        return $this->hasMany(ListWarehouseCamera::className(), ['zone' => 'id']);
    }

    /**
     *
     * @return \yii\db\ActiveQuery
     */
    public function getListWarehouseRoutes()
    {
        return $this->hasMany(ListWarehouseRoute::className(), ['zone' => 'id']);
    }

    public function getListWarehouseZonesCt()
    {
        return $this->hasMany(ListWarehouseZoneCt::className(), ['warehouse_zone' => 'id']);
    }

    /**
     * Получить модель типа груза
     * @return \yii\db\ActiveQuery
     */
    public function getCargoTypes()
    {
        return ListCargoType::find()->where(['in', 'id', ArrayHelper::map($this->listWarehouseZonesCt, 'cargo_type', 'cargo_type')])->all();
        //return $this->hasOne(ListCargoType::className(), ['id' => 'cargo_type']);
    }

    public function getCargo_type() {

        if (!$this->zoneCtInput && $this->listWarehouseZonesCt)
            $this->zoneCtInput = $this->listWarehouseZonesCt[0]->cargo_type;

        return $this->zoneCtInput;
    }

    public function setCargo_type($value) {

        $this->zoneCtInput = $value;
    }

    public function saveCargoType() {

        if (!$this->zoneCtInput) {
            ListWarehouseZoneCt::deleteAll('warehouse_zone = :zone', [':zone' => ($this->id) ? $this->id : 0]);
        }
        else {
            if ($this->listWarehouseZonesCt) {
                $zoneCt = $this->listWarehouseZonesCt[0];
                $zoneCt->cargo_type = $this->zoneCtInput;
                $zoneCt->save();
            }
            else {
                $zoneCt = new ListWarehouseZoneCt();
                $zoneCt->warehouse_zone = $this->id;
                $zoneCt->cargo_type = $this->zoneCtInput;
                $zoneCt->save();
            }
        }
    }

    /*public function getCargo_type() {
        if ($this->listWarehouseZonesCt)
            return $this->listWarehouseZonesCt[0]->cargo_type;
    }

    public function setCargo_type($value) {
        if (!$value) {
            ListWarehouseZoneCt::deleteAll('warehouse_zone = :zone', [':zone' => ($this->id) ? $this->id : 0]);
        }
        else {
            if ($this->listWarehouseZonesCt) {
                $zoneCt = $this->listWarehouseZonesCt[0];
                $zoneCt->cargo_type = $value;
                $zoneCt->save();
            }
            else {
                $zoneCt = new ListWarehouseZoneCt();
                $zoneCt->warehouse_zone = $this->id;
                $zoneCt->cargo_type = $value;
                $zoneCt->save();
            }
        }
    }*/

    /**
     * Получить модель подразделения
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseModel()
    {
        return $this->hasOne(ListWarehouse::className(), ['id' => 'warehouse']);
    }

    public static function getList($field = 'name', $empty = false, $lang = null, $andWhere = '1 = 1') {

        if (!$lang)
            $lang = Yii::$app->language;

        $models = self::find()->where($andWhere)->all();

        $result = ArrayHelper::map($models, 'id', $field . '_' . $lang);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    public function afterSave($insert, $changedAttributes) {

        $this->saveCargoType();
    }

    public function beforeDelete() {

        ListWarehouseZoneCt::deleteAll('warehouse_zone = :zone', [':zone' => $this->id]);
        return parent::beforeDelete();
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля => значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_uk' => $this->name_uk,
            'name_ru' => $this->name_ru,
            'cargo_type' => $this->cargo_type,
            'actual_weight_from' => $this->actual_weight_from,
            'actual_weight_to' => $this->actual_weight_to,
            'lenght_from' => $this->lenght_from,
            'lenght_to' => $this->lenght_to,
            'width_from' => $this->width_from,
            'width_to' => $this->width_to,
            'height_from' => $this->height_from,
            'height_to' => $this->height_to,
        ];
    }
}

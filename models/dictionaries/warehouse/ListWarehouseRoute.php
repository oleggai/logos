<?php

namespace app\models\dictionaries\warehouse;

use Yii;
use app\models\common\CommonModel;
use app\models\dictionaries\address\ListCity;

/**
 * This is the model class for table "yii2_list_warehouse_route".
 *
 * @property string $id
 * @property string $warehouse
 * @property string $city
 * @property string $zone
 * @property string $departure_time
 * @property integer $monday
 * @property integer $tuesday
 * @property integer $wednesday
 * @property integer $thursday
 * @property integer $friday
 * @property integer $saturday
 * @property integer $sunday
 *
 * @property ListWarehouse $warehouseModel
 * @property ListCity $cityModel
 * @property ListWarehouseZone $zoneModel
 */
class ListWarehouseRoute extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_warehouse_route}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['zone', 'departure_time'], 'required'],
            [['warehouse', 'city', 'zone', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'], 'integer'],
            [['departure_time'], 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('warehouse', 'ID'),
            'warehouse' => Yii::t('warehouse', 'Warehouse'),
            'city' => Yii::t('warehouse', 'City'),
            'zone' => Yii::t('warehouse', 'Warehouse zone'),
            'departure_time' => Yii::t('warehouse', 'Departure time'),
            'monday' => Yii::t('warehouse', 'Monday'),
            'tuesday' => Yii::t('warehouse', 'Tuesday'),
            'wednesday' => Yii::t('warehouse', 'Wednesday'),
            'thursday' => Yii::t('warehouse', 'Thursday'),
            'friday' => Yii::t('warehouse', 'Friday'),
            'saturday' => Yii::t('warehouse', 'Saturday'),
            'sunday' => Yii::t('warehouse', 'Sunday'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseModel()
    {
        return $this->hasOne(ListWarehouse::className(), ['id' => 'warehouse']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCityModel()
    {
        return $this->hasOne(ListCity::className(), ['id' => 'city']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZoneModel()
    {
        return $this->hasOne(ListWarehouseZone::className(), ['id' => 'zone']);
    }

    public function toJson() {
        return [
            'id' => $this->id,
            'country' => $this->cityModel->regionModel->country,
            'city' => $this->city,
            'zone' => $this->zone,
            'departure_time' => substr($this->departure_time, 0, 5),
            'monday' => $this->monday,
            'tuesday' => $this->tuesday,
            'wednesday' => $this->wednesday,
            'thursday' => $this->thursday,
            'friday' => $this->friday,
            'saturday' => $this->saturday,
            'sunday' => $this->sunday,
        ];
    }
}

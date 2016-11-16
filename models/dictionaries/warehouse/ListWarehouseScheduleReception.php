<?php

namespace app\models\dictionaries\warehouse;

use Yii;
use app\models\common\CommonModel;
use app\models\dictionaries\address\ListDayofweek;

/**
 * This is the model class for table "yii2_list_warehouse_schedule_reception".
 *
 * @property string $id
 * @property string $warehouse
 * @property string $dayofweek
 * @property string $warehouse_schedule_type
 * @property string $time_begin
 * @property string $time_end
 *
 * @property ListWarehouse $warehouseModel
 * @property ListDayofweek $dayofweekModel
 * @property ListWarehouseScheduleType $warehouseScheduleType
 */
class ListWarehouseScheduleReception extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_warehouse_schedule_reception}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['dayofweek', 'warehouse_schedule_type', 'time_begin', 'time_end'], 'required'],
            [['warehouse', 'dayofweek', 'warehouse_schedule_type'], 'integer'],
            [['time_begin', 'time_end'], 'safe'],
            [['warehouse', 'dayofweek', 'warehouse_schedule_type'], 'unique',
                'targetAttribute' => ['warehouse', 'dayofweek', 'warehouse_schedule_type'],
                'message' => 'The combination of Warehouse, Dayofweek and Warehouse Schedule Type has already been taken.']
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
            'dayofweek' => Yii::t('warehouse', 'Dayofweek'),
            'warehouse_schedule_type' => Yii::t('warehouse', 'Warehouse Schedule Type'),
            'time_begin' => Yii::t('warehouse', 'Time Begin'),
            'time_end' => Yii::t('warehouse', 'Time End'),
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
    public function getDayofweekModel()
    {
        return $this->hasOne(ListDayofweek::className(), ['id' => 'dayofweek']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseScheduleType()
    {
        return $this->hasOne(ListWarehouseScheduleType::className(), ['id' => 'warehouse_schedule_type']);
    }

    public function toJson(){

        return [
            'id' => $this->id,
            'dayofweek' => $this->dayofweek,
            'dayofweekText' => $this->dayofweekModel->name,
            'warehouse_schedule_type' => $this->warehouse_schedule_type,
            'time_begin' => substr($this->time_begin, 0, 5),
            'time_end' => substr($this->time_end, 0, 5),
        ];
    }

    public function validateTimes($attribute, $params){

        if ($this->time_begin > $this->time_end)
            $this->addError($attribute,
                Yii::t('address', "'{$this->getAttributeLabel('time_begin')}' more than '{$this->getAttributeLabel('time_end')}'"));
    }
}

<?php

namespace app\models\dictionaries\warehouse;

use Yii;
use app\models\common\CommonModel;

/**
 * This is the model class for table "yii2_list_warehouse_zone_ct".
 *
 * @property string $id
 * @property string $warehouse_zone
 * @property string $cargo_type
 *
 * @property ListWarehouseZone $warehouseZone
 * @property ListCargoType $cargoType
 */
class ListWarehouseZoneCt extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_warehouse_zone_ct}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['warehouse_zone', 'cargo_type'], 'required'],
            [['warehouse_zone', 'cargo_type'], 'integer'],
            [['warehouse_zone', 'cargo_type'], 'unique', 'targetAttribute' => ['warehouse_zone', 'cargo_type'], 'message' => 'The combination of Warehouse Zone and Cargo Type has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('warehouse', 'ID'),
            'warehouse_zone' => Yii::t('warehouse', 'Warehouse Zone'),
            'cargo_type' => Yii::t('warehouse', 'Cargo Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseZone()
    {
        return $this->hasOne(ListWarehouseZone::className(), ['id' => 'warehouse_zone']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCargoType()
    {
        return $this->hasOne(ListCargoType::className(), ['id' => 'cargo_type']);
    }
}

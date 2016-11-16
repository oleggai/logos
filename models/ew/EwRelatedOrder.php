<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use app\models\common\DateFormatBehavior;
use Yii;

/**
 * This is the model class for table "{{%ew_related_order}}".
 *
 * @property string $id
 * @property string $ew_id
 * @property string $wb_order_num
 * @property string $wb_order_type
 * @property string $carrier_id
 * @property string $wb_order_date
 *
 * @property ExpressWaybill $ew
 * @property WbOrderType $wbOrderType
 */
class EwRelatedOrder extends CommonModel
{
    const ENTITY_TYPE  = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ew_related_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ew_id', 'carrier_id'], 'integer'],
            [['wb_order_num', 'wb_order_type'], 'required'],
            [['wb_order_num'], 'validateUnique'],
            [['_wb_order_date'], 'validateDate'],
            [['wb_order_num'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ew', 'ID'),
            'ew_id' => Yii::t('ew', 'Ew ID'),
            'wb_order_num' => Yii::t('ew', 'Wb Order Num'),
            'wb_order_type' => Yii::t('ew', 'Wb Order Type'),
            'carrier_id' => Yii::t('ew', 'Carrier'),
            'wb_order_date' => Yii::t('ew', 'Wb Order Date'),
            '_wb_order_date' => Yii::t('ew', 'Wb Order Date'),
        ];
    }

    /**
     * Поведения
     */
    function behaviors()
    {
        return [
            [
                'class' => DateFormatBehavior::className(),
                'attributes' => [
                    '_wb_order_date' => 'wb_order_date',
                ]
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEw()
    {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWbOrderType()
    {
        return $this->hasOne(WbOrderType::className(), ['id' => 'wb_order_type']);
    }

    public function validateDate($attribute,$params){
        if (!DateFormatBehavior::validate($this->$attribute))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }

    public function validateUnique($attribute, $params) {
        $orders = self::find()
            ->where('id != :id AND ew_id = :ew_id AND wb_order_num = :wb_order_num AND wb_order_type = :wb_order_type',
                [':id' => $this->id, ':ew_id' => $this->ew_id, ':wb_order_num' => $this->wb_order_num, ':wb_order_type' => $this->wb_order_type])
            ->all();
        if ($orders)
            $this->addError($attribute, Yii::t('error',
                $this->getAttributeLabel('wb_order_num') . ' + ' . $this->getAttributeLabel('wb_order_type') . ' ' . 'должно быть уникальным значением'));
    }

    public function toJson(){
        return [
            'id'=>$this->id,
            'wb_order_num'=>$this->wb_order_num,
            'wb_order_type'=>$this->wb_order_type,
            'carrier_id'=>$this->carrier_id,
            '_wb_order_date'=>$this->_wb_order_date,
            'state' => 1,
         ];
    }

}

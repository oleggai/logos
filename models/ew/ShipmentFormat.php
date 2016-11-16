<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%shipment_format}}".
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 *
 * @property ExpressWaybill[] $expressWaybills
 */
class ShipmentFormat extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%shipment_format}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 100],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('ew', 'ID'),
            'name_ru' => Yii::t('ew', 'Name Ru'),
            'name_en' => Yii::t('ew', 'Name En'),
            'name_uk' => Yii::t('ew', 'Name Uk'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpressWaybills() {
        return $this->hasMany(ExpressWaybill::className(), ['shipment_format' => 'id']);
    }

}

<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;

/**
 * This is the model class for table "yii2_fact_payment".
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property integer $visible
 * @property integer $state
 *
 * @property EwAddService[] $ewAddServices
 * @property EwCost[] $ewCosts
 * @property EwCost[] $ewCosts0
 */
class FactPayment extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yii2_fact_payment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['visible', 'state'], 'integer'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 100],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name_ru' => Yii::t('app', 'Name Ru'),
            'name_en' => Yii::t('app', 'Name En'),
            'name_uk' => Yii::t('app', 'Name Uk'),
            'visible' => Yii::t('app', 'Visible'),
            'state' => Yii::t('app', 'State'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEwAddServices()
    {
        return $this->hasMany(EwAddService::className(), ['fact_pay' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEwCosts()
    {
        return $this->hasMany(EwCost::className(), ['fact_pay_int_deliv' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEwCosts0()
    {
        return $this->hasMany(EwCost::className(), ['fact_pay_ccs' => 'id']);
    }
}

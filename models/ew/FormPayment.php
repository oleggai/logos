<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель типа платежа
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property integer $visible
 * @property integer $state
 *
 */
class FormPayment extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%form_payment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 100],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique'],
            [['visible', 'state'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ew', 'ID'),
            'name_ru' => Yii::t('ew', 'Name Ru'),
            'name_en' => Yii::t('ew', 'Name En'),
            'name_uk' => Yii::t('ew', 'Name Uk'),
        ];
    }


}

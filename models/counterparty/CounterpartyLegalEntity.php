<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;

/**
 * This is the model class for table "{{%counterparty_legal_entity}}".
 *
 * @property string $counterparty
 * @property string $form_ownership
 * @property string $full_name_en
 * @property string $full_name_ru
 * @property string $full_name_uk
 * @property string $short_name_en
 * @property string $short_name_ru
 * @property string $short_name_uk
 * @property string $manual_name_en
 * @property string $manual_name_ru
 * @property string $manual_name_uk
 * @property string $display_name_en
 * @property string $display_name_ru
 * @property string $display_name_uk
 * @property string $edrpou_code
 * @property string $itn_code
 * @property string $tax_number
 * @property integer $obligor
 * @property integer $clearing
 * @property integer $maybe_thirdparty
 * @property integer $thirdparty
 * @property integer $parent_company
 *
 * @property Counterparty $counterparty0
 * @property ListFormOwnership $formOwnership
 */
class CounterpartyLegalEntity extends CommonModel
{

    /**
     * @var integer Переменная присваивается перед валидацией модели из поля основной модели
     */
    public $residentOfUkraine;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_legal_entity}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counterparty', 'form_ownership', 'obligor', 'clearing', 'maybe_thirdparty', 'thirdparty', 'parent_company'], 'integer'],
            [['edrpou_code'], 'number'],
            [['itn_code'], 'number'],
            [['full_name_en', 'full_name_ru', 'full_name_uk', 'short_name_en', 'short_name_ru', 'short_name_uk'], 'string', 'max' => 100],
            [['manual_name_en', 'manual_name_ru', 'manual_name_uk', 'display_name_en', 'display_name_ru', 'display_name_uk'], 'string', 'max' => 250],
            [['tax_number'], 'string', 'max' => 20],
            [['counterparty'], 'validateDisplayName'],
            [['edrpou_code'], 'required', 'when' => function($model) {return $model->residentOfUkraine == 1;}],
            //[['tax_number'], 'required', 'when' => function($model) {return $model->residentOfUkraine != 1;}],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'counterparty' => Yii::t('counterparty', 'Counterparty'),
            'form_ownership' => Yii::t('counterparty', 'Form Ownership'),
            'full_name_en' => Yii::t('counterparty', 'Full Name En'),
            'full_name_ru' => Yii::t('counterparty', 'Full Name Ru'),
            'full_name_uk' => Yii::t('counterparty', 'Full Name Uk'),
            'short_name_en' => Yii::t('counterparty', 'Short Name En'),
            'short_name_ru' => Yii::t('counterparty', 'Short Name Ru'),
            'short_name_uk' => Yii::t('counterparty', 'Short Name Uk'),
            'manual_name_en' => Yii::t('counterparty', 'Manual Name En'),
            'manual_name_ru' => Yii::t('counterparty', 'Manual Name Ru'),
            'manual_name_uk' => Yii::t('counterparty', 'Manual Name Uk'),
            'display_name_en' => Yii::t('counterparty', 'Display Name En'),
            'display_name_ru' => Yii::t('counterparty', 'Display Name Ru'),
            'display_name_uk' => Yii::t('counterparty', 'Display Name Uk'),
            'edrpou_code' => Yii::t('counterparty', 'Edrpou Code'),
            'itn_code' => Yii::t('counterparty', 'ITN'),
            'tax_number' => Yii::t('counterparty', 'Tax Number'),
            'obligor' => Yii::t('counterparty', 'Obligor'),
            'clearing' => Yii::t('counterparty', 'Clearing'),
            'maybe_thirdparty' => Yii::t('counterparty', 'Maybe Thirdparty'),
            'thirdparty' => Yii::t('counterparty', 'Thirdparty'),
            'parent_company' => Yii::t('counterparty', 'Parent Company'),
        ];
    }

    public function validateDisplayName(){
        if (!$this->display_name_en) {
            $this->addError('full_name_en', Yii::t('app', 'Required '.Yii::t('counterparty', 'Full Name En').' or '. Yii::t('counterparty', 'Manual Name En')));
            $this->addError('manual_name_en');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterparty0()
    {
        return $this->hasOne(Counterparty::className(), ['id' => 'counterparty']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFormOwnership()
    {
        return $this->hasOne(ListFormOwnership::className(), ['id' => 'form_ownership']);
    }


}

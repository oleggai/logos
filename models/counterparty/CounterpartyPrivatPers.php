<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;

/**
 * This is the model class for table "{{%counterparty_privat_pers}}".
 *
 * @property string $counterparty
 * @property string $sex
 * @property string $surname_en
 * @property string $surname_ru
 * @property string $surname_uk
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $secondname_en
 * @property string $secondname_ru
 * @property string $secondname_uk
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
 * @property string $tax_number
 *
 * @property Counterparty $counterparty0
 * @property ListSex $sex0
 */
class CounterpartyPrivatPers extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_privat_pers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counterparty', 'sex'], 'integer'],
            [['surname_en', 'surname_ru', 'surname_uk', 'name_en', 'name_ru', 'name_uk', 'secondname_en', 'secondname_ru', 'secondname_uk', 'full_name_en', 'full_name_ru', 'full_name_uk', 'short_name_en', 'short_name_ru', 'short_name_uk'], 'string', 'max' => 100],
            [['manual_name_en', 'manual_name_ru', 'manual_name_uk', 'display_name_en', 'display_name_ru', 'display_name_uk'], 'string', 'max' => 150],
            [['tax_number'], 'string', 'max' => 20],
            [['counterparty'], 'validateDisplayName'],
            [['surname_en', 'name_en', 'short_name_en'], 'required', 'when' => function($model) {return $model->manual_name_en == '';}],
        ];
    }

    public function validateDisplayName(){
        if (!$this->display_name_en) {
            $this->addError('full_name_en', Yii::t('app', 'Required '.Yii::t('counterparty', 'Full Name En').' or '. Yii::t('counterparty', 'Manual Name En')));
            $this->addError('manual_name_en');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'counterparty' => Yii::t('counterparty', 'Counterparty'),
            'sex' => Yii::t('counterparty', 'Sex'),
            'surname_en' => Yii::t('counterparty', 'Surname En'),
            'surname_ru' => Yii::t('counterparty', 'Surname Ru'),
            'surname_uk' => Yii::t('counterparty', 'Surname Uk'),
            'name_en' => Yii::t('counterparty', 'Name En'),
            'name_ru' => Yii::t('counterparty', 'Name Ru'),
            'name_uk' => Yii::t('counterparty', 'Name Uk'),
            'secondname_en' => Yii::t('counterparty', 'Secondname En'),
            'secondname_ru' => Yii::t('counterparty', 'Secondname Ru'),
            'secondname_uk' => Yii::t('counterparty', 'Secondname Uk'),/*
            'full_name_en' => Yii::t('counterparty', 'Full Name En'),
            'full_name_ru' => Yii::t('counterparty', 'Full Name Ru'),
            'full_name_uk' => Yii::t('counterparty', 'Full Name Uk'),

            'short_name_en' => Yii::t('counterparty', 'Short Name En'),
            'short_name_ru' => Yii::t('counterparty', 'Short Name Ru'),
            'short_name_uk' => Yii::t('counterparty', 'Short Name Uk'),
            'manual_name_en' => Yii::t('counterparty', 'Manual Name En'),
            'manual_name_ru' => Yii::t('counterparty', 'Manual Name Ru'),
            'manual_name_uk' => Yii::t('counterparty', 'Manual Name Uk'),
              */
            'display_name_en' => Yii::t('counterparty', 'Display Name En'),
            'display_name_ru' => Yii::t('counterparty', 'Display Name Ru'),
            'display_name_uk' => Yii::t('counterparty', 'Display Name Uk'),


            'manual_fio_ru'=>Yii::t('counterparty', 'Manual full name Ru'),
            'full_fio_ru'=>Yii::t('counterparty', 'Full full name Ru'),
            'short_fio_ru'=>Yii::t('counterparty', 'Short full name Ru'),

            'manual_fio_uk'=>Yii::t('counterparty', 'Manual full name Uk'),
            'full_fio_uk'=>Yii::t('counterparty', 'Full full name Uk'),
            'short_fio_uk'=>Yii::t('counterparty', 'Short full name Uk'),

            'manual_fio_en'=>Yii::t('counterparty', 'Manual full name En'),
            'full_fio_en'=>Yii::t('counterparty', 'Full full name En'),
            'short_fio_en'=>Yii::t('counterparty', 'Short full name En'),

            'tax_number' => Yii::t('counterparty', 'Tax Number'),

            'resident_of_ukraine'=>Yii::t('counterparty', 'Resident of Ukraine'),
        ];
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
    public function getSex0()
    {
        return $this->hasOne(ListSex::className(), ['id' => 'sex']);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'sex' => $this->sex,
            'surname_en' => $this->surname_en,
            'surname_ru' => $this->surname_ru,
            'surname_uk' => $this->surname_uk,
            'name_en' => $this->name_en,
            'name_ru' => $this->name_ru,
            'name_uk' => $this->name_uk,
            'secondname_en' => $this->secondname_en,
            'secondname_ru' => $this->secondname_ru,
            'secondname_uk' => $this->secondname_uk,
            'full_name_en' => $this->full_name_en,
            'full_name_ru' => $this->full_name_ru,
            'full_name_uk' => $this->full_name_uk,
            'short_name_en' => $this->short_name_en,
            'short_name_ru' => $this->short_name_ru,
            'short_name_uk' => $this->short_name_uk,
            'manual_name_en' => $this->manual_name_en,
            'manual_name_ru' => $this->manual_name_ru,
            'manual_name_uk' => $this->manual_name_uk,
            'display_name_en' => $this->display_name_en,
            'display_name_ru' => $this->display_name_ru,
            'display_name_uk' => $this->display_name_uk
        ];
    }

}

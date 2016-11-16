<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;

/**
 * This is the model class for table "{{%counterparty_contact_pers_othercontact}}".
 *
 * @property string $id
 * @property string $counterparty_contact_pers
 * @property string $contact_type
 * @property string $contact
 * @property integer $state
 *
 * @property CounterpartyContactPers $counterpartyContactPers
 * @property ListContactType $contactType
 */
class CounterpartyContactPersOthercontact extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_contact_pers_othercontact}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counterparty_contact_pers', 'contact_type', 'contact'], 'required'],
            [['counterparty_contact_pers', 'contact_type', 'state'], 'integer'],
            [['contact'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('counterparty', 'ID'),
            'counterparty_contact_pers' => Yii::t('counterparty', 'Counterparty Contact Pers'),
            'contact_type' => Yii::t('counterparty', 'Contact Type'),
            'contact' => Yii::t('counterparty', 'Contact'),
            'state' => Yii::t('counterparty', 'State'),
            'contact_type_name' => Yii::t('counterparty', 'Other Contact Type'),
            'counterparty_contact_pers_name_en' => Yii::t('counterparty', 'Counterparty Contact Pers Name En'),
            'counterparty_contact_pers_name_uk' => Yii::t('counterparty', 'Counterparty Contact Pers Name Uk'),
            'counterparty_contact_pers_name_ru' => Yii::t('counterparty', 'Counterparty Contact Pers Name Ru'),
            'operation' => Yii::t('app', 'Operation')
        ];
    }

    /**
     * Метод получения доступных операция
     */
    public function getOperations() {
        if ($this->counterpartyContactPers->state == CommonModel::STATE_DELETED)
            return [];
        return parent::getOperations();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPers()
    {
        return $this->hasOne(CounterpartyContactPers::className(), ['id' => 'counterparty_contact_pers']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactType()
    {
        return $this->hasOne(ListContactType::className(), ['id' => 'contact_type']);
    }


    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'state'=>$this->state,
            'contact'=>$this->contact,
            'contact_type'=>$this->contact_type,
            'contact_type_name'=>$this->contact_type,
            'counterparty_contact_pers_name_en'=>$this->counterpartyContactPers->display_name_en,
            'counterparty_contact_pers_name_uk'=>$this->counterpartyContactPers->display_name_uk,
            'counterparty_contact_pers_name_ru'=>$this->counterpartyContactPers->display_name_ru,
            'counterparty_contact_pers' => $this->counterparty_contact_pers,
        ];
    }
}

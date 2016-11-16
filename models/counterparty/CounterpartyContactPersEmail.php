<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;

/**
 * This is the model class for table "yii2_counterparty_contact_pers_email".
 *
 * @property string $id
 * @property string $counterparty_contact_pers
 * @property string $email
 * @property integer $primary
 * @property integer $state
 *
 * @property CounterpartyContactPers $counterpartyContactPers
 */
class CounterpartyContactPersEmail extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_contact_pers_email}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counterparty_contact_pers', 'email'], 'required'],
            [['email'], 'validateEmail'],
            [['counterparty_contact_pers', 'primary', 'state'], 'integer'],
            [['email'], 'string', 'max' => 100]
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
            'email' => Yii::t('counterparty', 'Email'),
            'primary' => Yii::t('counterparty', 'Primary'),
            'state' => Yii::t('counterparty', 'State'),
            'operation' => Yii::t('app', 'Operation')
        ];
    }

    public function validateEmail($attribute){
        if (!strpos($this->email, '@'))
            $this->addError($attribute, Yii::t('app', 'Email format error'));
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
     * Метод вызывается перед сохранением сущности
     * @param bool $insert параметр
     * @return bool результат выполнения
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            if ($insert){
                if (self::find()->where('counterparty_contact_pers in (select id from '.CounterpartyContactPers::tableName().
                        ' where counterparty='.$this->counterpartyContactPers->counterparty.') and state='.self::STATE_CREATED)->count() == 0) {
                    $this->primary = 1;
                }
            } elseif (self::find()->where('id<>'.$this->id.' and counterparty_contact_pers in (select id from '.CounterpartyContactPers::tableName().
                    ' where counterparty='.$this->counterpartyContactPers->counterparty.') and state='.self::STATE_CREATED)->count() == 0){
                $this->primary = 1;
            }
            return true;
        }

        return false;
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        if ( $this->primary == 1){
            self::updateAll(['primary'=>0], 'id<>'.$this->id.' and counterparty_contact_pers in (select id from '.CounterpartyContactPers::tableName().
                ' where counterparty='.$this->counterpartyContactPers->counterparty.')');
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPers()
    {
        return $this->hasOne(CounterpartyContactPers::className(), ['id' => 'counterparty_contact_pers']);
    }

    public function getChecked($val) {
        if ($val == '1')
            return 'checked';

        return '';
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'state'=>$this->state,
            'primary_name'=>$this->getChecked($this->primary),
            'primary'=>$this->primary,
            'email'=>$this->email,
            'counterparty_contact_pers_name_en'=>$this->counterpartyContactPers->full_name_en,
            'counterparty_contact_pers_name_uk'=>$this->counterpartyContactPers->full_name_uk,
            'counterparty_contact_pers_name_ru'=>$this->counterpartyContactPers->full_name_ru,
            'counterparty_contact_pers' => $this->counterparty_contact_pers,
        ];
    }

}

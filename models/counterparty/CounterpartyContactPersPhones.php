<?php

namespace app\models\counterparty;

use Yii;
use app\models\common\CommonModel;

/**
 * This is the model class for table "{{%counterparty_contact_pers_phones}}".
 *
 * @property string $id
 * @property string $counterparty_contact_pers
 * @property string $phone_num_type
 * @property string $operator_code
 * @property string $phone_number
 * @property integer $primary
 * @property integer $state
 *
 *  @property string $displayPhone
 *
 * @property CounterpartyContactPers $counterpartyContactPers
 * @property ListPhoneNumType $phoneNumType
 *
 */
class CounterpartyContactPersPhones extends CommonModel
{
    /**
     * @var int 1 - нужно или 0 - нет делать проверки на дубли перед сохранением
     */
    public $forceSave = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_contact_pers_phones}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(),
        [
            [['counterparty_contact_pers', 'phone_num_type', 'phone_number'], 'required'],
            [['counterparty_contact_pers', 'phone_num_type', 'primary', 'state'], 'integer'],
            [['operator_code'], 'number'],
            [['phone_number'], 'string', 'max' => 20],
            ['forceSave', 'safe']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('counterparty', 'ID'),
            'counterparty_contact_pers' => Yii::t('counterparty', 'Counterparty Contact Pers'),
            'phone_num_type' => Yii::t('counterparty', 'Phone Num Type'),
            'operator_code' => Yii::t('counterparty', 'Operator Code'),
            'phone_number' => Yii::t('counterparty', 'Phone Number'),
            'primary' => Yii::t('counterparty', 'Primary'),
            'state' => Yii::t('counterparty', 'State'),
            'phone_num_type_name' => Yii::t('counterparty', 'Phone Num Type'),
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
     * Метод вызывается перед сохранением сущности
     * @param bool $insert параметр
     * @return bool результат выполнения
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            if (!$this->forceSave && ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE)) {

                //проверка на дубликаты и вывод предупреждений
                $warnings = Array();
                $ids = Array();
                $cf_id = $this->id == null ? 0 : $this->id;

                $exists = CounterpartyContactPers::find()->leftJoin(CounterpartyContactPersPhones::tableName(),
                    CounterpartyContactPers::tableName().'.id='.CounterpartyContactPersPhones::tableName().'.counterparty_contact_pers')->
                where(CounterpartyContactPersPhones::tableName().'.id<>' . $cf_id
                    . ' and CONCAT(coalesce(operator_code,""), phone_number)=' . $this->operator_code.$this->phone_number)->all();
                foreach ($exists as $exist) {
                    if (!in_array($exist->counterparty, $ids)) {
                        $cp = Counterparty::findOne($exist->counterparty);
                        $warnings[] = $cp->counterparty_id;
                        $ids[] = $exist->counterparty;
                    }
                }

                if (sizeof($warnings) > 0) {
                    Yii::$app->session->setFlash('cp_warning', $warnings);
                    Yii::$app->session->setFlash('cp_ids', $ids);
                    $this->forceSave = 0;
                    return false;
                }
            }

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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhoneNumType()
    {
        return $this->hasOne(ListPhoneNumType::className(), ['id' => 'phone_num_type']);
    }

    public function getDisplayPhone()
    {
//        return $this->operator_code.
//            ($this->phone_number == '' ? '' : ($this->operator_code == '' ? '' : '-').$this->phone_number);
        return $this->operator_code.$this->phone_number;
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
            'phone_number'=>$this->phone_number,
            'operator_code'=>$this->operator_code,
            'phone_num_type'=>$this->phone_num_type,
            'phone_num_type_name'=>$this->phone_num_type,
            'counterparty_contact_pers_name_en'=>$this->counterpartyContactPers->display_name_en,
            'counterparty_contact_pers_name_uk'=>$this->counterpartyContactPers->display_name_uk,
            'counterparty_contact_pers_name_ru'=>$this->counterpartyContactPers->display_name_ru,
            'counterparty_contact_pers' => $this->counterparty_contact_pers,
            'display_phone'=>$this->displayPhone,
        ];
    }

}

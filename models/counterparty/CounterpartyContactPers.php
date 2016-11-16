<?php

namespace app\models\counterparty;

use Yii;
use app\models\dictionaries\employee\JobPosition;
use \app\models\common\CommonModel;

/**
 * This is the model class for table "{{%counterparty_contact_pers}}".
 *
 * @property string $id
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
 * @property string $job_position
 * @property integer $primary_person
 * @property string $addition_info
 * @property integer $state
 *
 * @property Counterparty $counterpartyModel
 * @property ListSex $contactPersSex
 * @property JobPosition $jobPosition
 * @property CounterpartyContactPersEmail[] $counterpartyContactPersEmails
 * @property CounterpartyContactPersPhones[] $counterpartyContactPersPhones
 * @property CounterpartyContactPersOthercontact[] $counterpartyContactPersOthercontacts
 *
 * @property string $full_name
 * @property string $display_name
 */
class CounterpartyContactPers extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_contact_pers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counterparty', 'display_name_en'], 'required'],
            [['counterparty', 'sex', 'job_position', 'primary_person', 'state'], 'integer'],
            [['surname_en', 'surname_ru', 'surname_uk', 'name_en', 'name_ru', 'name_uk', 'secondname_en', 'secondname_ru', 'secondname_uk', 'full_name_en', 'full_name_ru', 'full_name_uk', 'short_name_en', 'short_name_ru', 'short_name_uk', 'addition_info'], 'string', 'max' => 100],
            [['manual_name_en', 'manual_name_ru', 'manual_name_uk', 'display_name_en', 'display_name_ru', 'display_name_uk'], 'string', 'max' => 150]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('counterparty', 'ID'),
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
            'secondname_uk' => Yii::t('counterparty', 'Secondname Uk'),
            'full_name_en' => Yii::t('counterparty', 'Full Name En'),
            'full_name_ru' => Yii::t('counterparty', 'Full Name Ru'),
            'full_name_uk' => Yii::t('counterparty', 'Full Name Uk'),
            'short_name_en' => Yii::t('counterparty', 'Short Name En'),
            'short_name_ru' => Yii::t('counterparty', 'Short Name Ru'),
            'short_name_uk' => Yii::t('counterparty', 'Short Name Uk'),
            'manual_name_en' => Yii::t('counterparty', 'Manual Name En'),
            'manual_name_ru' => Yii::t('counterparty', 'Manual Name Ru'),
            'manual_name_uk' => Yii::t('counterparty', 'Manual Name Uk'),
            'display_name_en' => Yii::t('counterparty', 'Контактное лицо для отображения (Англ.)'),
            'display_name_ru' => Yii::t('counterparty', 'Контактное лицо для отображения (Рус.)'),
            'display_name_uk' => Yii::t('counterparty', 'Контактное лицо для отображения (Укр.)'),
            'job_position' => Yii::t('counterparty', 'Job Position'),
            'primary_person' => Yii::t('counterparty', 'Primary Person'),
            'addition_info' => Yii::t('counterparty', 'Addition Info'),
            'state' => Yii::t('counterparty', 'State'),

            'sex_name' => Yii::t('counterparty', 'Sex'),
            'primary_person_name' => Yii::t('counterparty', 'Primary Person'),
            'job_position_name' => Yii::t('counterparty', 'Job Position'),
            'operation' => Yii::t('app', 'Operation')
        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового контрагента
     */
    public function generateDefaults() {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
    }

    /**
     * Метод получения доступных операция
     */
    public function getOperations() {
        if ($this->counterpartyModel->state == CommonModel::STATE_DELETED)
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
                if (self::find()->where('counterparty='.$this->counterparty.' and state='.self::STATE_CREATED)->count() == 0) {
                    $this->primary_person = 1;
                }
            } elseif (self::find()->where('id<>'.$this->id.' and counterparty='.$this->counterparty.' and state='.self::STATE_CREATED)->count() == 0){
                $this->primary_person = 1;
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

        if ( $this->primary_person == 1){
            self::updateAll(['primary_person'=>0], 'id<>'.$this->id.' and counterparty='.$this->counterparty);
        }

        //при удалении удаляем все связанные сущности, при восстановлении восстанавливаем
        if ($this->operation == self::OPERATION_DELETE) {
            foreach($this->counterpartyContactPersPhones as $phone) {
                $phone->operation = CommonModel::OPERATION_DELETE;
                $phone->save();
            }
            foreach($this->counterpartyContactPersEmails as $email) {
                $email->operation = CommonModel::OPERATION_DELETE;
                $email->save();
            }
            foreach($this->counterpartyContactPersOthercontacts as $contact) {
                $contact->operation = CommonModel::OPERATION_DELETE;
                $contact->save();
            }
        }
        if ($this->operation == self::OPERATION_CANCEL) {
            foreach($this->counterpartyContactPersPhones as $phone) {
                $phone->operation = CommonModel::OPERATION_CANCEL;
                $phone->save();
            }
            foreach($this->counterpartyContactPersEmails as $email) {
                $email->operation = CommonModel::OPERATION_CANCEL;
                $email->save();
            }
            foreach($this->counterpartyContactPersOthercontacts as $contact) {
                $contact->operation = CommonModel::OPERATION_CANCEL;
                $contact->save();
            }
        }

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyModel()
    {
        return $this->hasOne(Counterparty::className(), ['id' => 'counterparty']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactPersSex()
    {
        return $this->hasOne(ListSex::className(), ['id' => 'sex']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJobPosition()
    {
        return $this->hasOne(JobPosition::className(), ['id' => 'job_position']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPersEmails()
    {
        return $this->hasMany(CounterpartyContactPersEmail::className(), ['counterparty_contact_pers' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPersPhones()
    {
        return $this->hasMany(CounterpartyContactPersPhones::className(), ['counterparty_contact_pers' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPersOthercontacts()
    {
        return $this->hasMany(CounterpartyContactPersOthercontact::className(), ['counterparty_contact_pers' => 'id']);
    }

    public function getFull_name()
    {
        return $this->{"full_name_".Yii::$app->language};
    }

    public function getDisplay_name()
    {
        return $this->{"display_name_".Yii::$app->language};
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
            'sex_name'=>$this->contactPersSex->name,
            'primary_person_name'=>$this->getChecked($this->primary_person),
            'full_name_en'=>$this->full_name_en,
            'full_name_uk'=>$this->full_name_uk,
            'full_name_ru'=>$this->full_name_ru,
            'job_position_name'=>$this->job_position,
            'addition_info'=>$this->addition_info,
            'state' => $this->state,
            'sex' => $this->sex,
            'job_position'=>$this->job_position,
            'primary_person' => $this->primary_person,
            'display_name_en' => $this->display_name_en,
            'surname_en'=>$this->surname_en,
            'name_en'=>$this->name_en,
            'secondname_en'=>$this->secondname_en,
            'short_name_en'=>$this->short_name_en,
            'manual_name_en'=>$this->manual_name_en,
            'display_name_uk' => $this->display_name_uk,
            'surname_uk'=>$this->surname_uk,
            'name_uk'=>$this->name_uk,
            'secondname_uk'=>$this->secondname_uk,
            'short_name_uk'=>$this->short_name_uk,
            'manual_name_uk'=>$this->manual_name_uk,
            'display_name_ru' => $this->display_name_ru,
            'surname_ru'=>$this->surname_ru,
            'name_ru'=>$this->name_ru,
            'secondname_ru'=>$this->secondname_ru,
            'short_name_ru'=>$this->short_name_ru,
            'manual_name_ru'=>$this->manual_name_ru,
            'display_name' => $this->display_name,
        ];
    }

}

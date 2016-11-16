<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%list_phone_num_type}}".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 *
 * @property CounterpartyContactPersPhones[] $counterpartyContactPersPhones
 */
class ListPhoneNumType extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_phone_num_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
            [['state', 'visible'], 'integer'],
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
            'id' => Yii::t('phonenumtype', 'ID'),
            'name_en' => Yii::t('phonenumtype', 'Name En'),
            'name_ru' => Yii::t('phonenumtype', 'Name Ru'),
            'name_uk' => Yii::t('phonenumtype', 'Name Uk'),
            'name' => Yii::t('phonenumtype', 'Phone Number Type'),
            'state' => Yii::t('phonenumtype', 'State'),
            'visible' => Yii::t('phonenumtype', 'Visible'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPersPhones()
    {
        return $this->hasMany(CounterpartyContactPersPhones::className(), ['phone_num_type' => 'id']);
    }

    /**
     * Возвращает имя соответствующее текущему языку
     * @return string
     */
    public function getName()
    {
        return $this->{"name_".Yii::$app->language};
    }

    /**
     * Получение списка видов номеров телефона в виде ассоциативного массива, где ключ - id, значение - название
     * @param bool $empty true = добавлять пустое значение
     * @return array массив видов номеров телефона
     */
    //public static function getList($field = 'name', $empty = false) {
    //    $models = ListPhoneNumType::find()
    //        ->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])
    //        ->all();
    //    $result = ArrayHelper::map($models, 'id', $field);

    //    if ($empty)
    //        $result = [null => ''] +  $result;
    //    return  $result;
    //}


    public function generateDefaults($params) {

        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyPhoneNumType($params);
    }

    public function copyPhoneNumType($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $type = ListPhoneNumType::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
    }

    public function toJson() {

        return [
            'id' => $this->id,
            'state' => $this->state,
            'name_ru' => $this->name_ru,
            'name_en' => $this->name_en,
            'name_uk' => $this->name_uk,
            'visibilityText' => $this->visibilityText
        ];
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);

        $this->operation = self::OPERATION_NONE;
    }

    public function getGridOperations() {
        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app','Copy'),
        ];
    }

    public function getGridOperationsOptions() {
        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before'=>true, 'tab_name_sufix'=>'copy'],
        ];
    }

}

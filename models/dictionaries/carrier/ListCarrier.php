<?php

namespace app\models\dictionaries\carrier;

use app\models\counterparty\Counterparty;
use app\models\common\CommonModel;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%list_carrier}}".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $counterparty
 * @property integer $visible
 * @property integer $state
 *
 * @property Counterparty $counterpartyItem
 * @property string $counterparty_name
 */
class ListCarrier extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_carrier}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['counterparty', 'visible', 'state'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'unique'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
            [['counterparty'], 'exist', 'skipOnError' => true, 'targetClass' => Counterparty::className(), 'targetAttribute' => ['counterparty' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('carrier', 'ID'),
            'name_en' => Yii::t('carrier', 'Name En'),
            'name_ru' => Yii::t('carrier', 'Name Ru'),
            'name_uk' => Yii::t('carrier', 'Name Uk'),
            'counterparty' => Yii::t('carrier', 'Counterparty'),
            'visible' => Yii::t('carrier', 'Visible'),
            'state' => Yii::t('carrier', 'State'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyItem()
    {
        return $this->hasOne(Counterparty::className(), ['id' => 'counterparty']);
    }

    /**
     * получить название контрагента
     */
    public function getCounterpartyName() {
        if ($this->counterparty != null)
            return $this->counterpartyItem->CounterpartyName;
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyListCarrier($params);
    }

    public function copyListCarrier($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $state = ListCarrier::findOne(['id' => $params['id']]);
            if($state) {
                $this->attributes = $state->getAttributes();
            }
        }
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ru' => $this->name_ru,
            'name_uk' => $this->name_uk,
            //'counterparty' => $this->counterparty,
            'counterparty_name' => $this->CounterpartyName,
            'visible' => $this->visible,
            'state' => $this->state,
            'visibilityText' => $this->visibilityText,
        ];
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);
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

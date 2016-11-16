<?php

namespace app\models\counterparty;

use app\classes\DocumentStorage;
use app\models\common\CommonModel;
use Yii;
use app\models\common\ShortDateFormatBehavior;

/**
 * This is the model class for table "{{%counterparty_contract}}".
 *
 * @property string $id
 * @property string $counterparty_id
 * @property string $contract_number
 * @property string $contract_name
 * @property string $contract_type
 * @property string $signature_date
 * @property string $effective_date
 * @property string $expire_date
 * @property integer $extended
 * @property integer $state
 * @property string $addition_info
 * @property string $initiator
 *
 * @property Counterparty $counterparty
 * @property ListContractType $contractType
 */
class CounterpartyContract extends CommonModel
{

    const ENTITY_NAME = 'CONTRACT';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_contract}}';
    }

    /**
     * Поведения
     */
    function behaviors()
    {
        return [
            [
                'class' => ShortDateFormatBehavior::className(),
                'attributes' => [
                    '_signature_date' => 'signature_date',
                    '_effective_date' => 'effective_date',
                    '_expire_date' => 'expire_date'
                ]
            ],
            [
                'class' => DocumentStorage::className()
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counterparty_id', 'contract_number', 'contract_name', 'contract_type', 'signature_date', 'effective_date', 'expire_date', 'initiator'], 'required'],
            [['counterparty_id', 'contract_type', 'extended', 'state', 'initiator'], 'integer'],
            [['_signature_date', '_effective_date', '_expire_date'], 'validateDateFormat'],
            [['_signature_date'], 'validateDate'],
            [['contract_number'], 'string', 'max' => 50],
            [['contract_name'], 'string', 'max' => 150],
            [['addition_info'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('counterparty', 'ID'),
            'counterparty_id' => Yii::t('counterparty', 'Counterparty ID'),
            'contract_number' => Yii::t('counterparty', 'Contract Number'),
            'contract_name' => Yii::t('counterparty', 'Contract Name'),
            'contract_type' => Yii::t('counterparty', 'Contract Type'),
            'signature_date' => Yii::t('counterparty', 'Signature Date'),
            'effective_date' => Yii::t('counterparty', 'Effective Date'),
            'expire_date' => Yii::t('counterparty', 'Expire Date'),
            '_signature_date' => Yii::t('counterparty', 'Signature Date'),
            '_effective_date' => Yii::t('counterparty', 'Effective Date'),
            '_expire_date' => Yii::t('counterparty', 'Expire Date'),
            'extended' => Yii::t('counterparty', 'Extended'),
            'state' => Yii::t('counterparty', 'State'),
            'addition_info' => Yii::t('counterparty', 'Addition Info'),
            'initiator' => Yii::t('counterparty', 'Initiator'),
            'operation' => Yii::t('app', 'Operation')
        ];
    }

    public function validateDateFormat($attribute){
        if (!ShortDateFormatBehavior::validate($_POST['CounterpartyContract'][$attribute]))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }

    public function validateDate($attribute){
        if ($this->effective_date < $this->signature_date) {
            $this->addError('_effective_date', Yii::t('counterparty', 'Effective date must be greater then Signature date'));
        }

        if ($this->expire_date <= $this->effective_date) {
            $this->addError('_expire_date', Yii::t('counterparty', 'Expire date must be greater then Effective date'));
        }
    }

    /**
     * Метод получения доступных операция
     */
    public function getOperations() {
        if ($this->counterparty->state == CommonModel::STATE_DELETED)
            return [];
        return parent::getOperations();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterparty()
    {
        return $this->hasOne(Counterparty::className(), ['id' => 'counterparty_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContractType()
    {
        return $this->hasOne(ListContractType::className(), ['id' => 'contract_type']);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'state'=>$this->state,
            'counterparty_id'=>$this->counterparty_id,
            'contract_number'=>$this->contract_number,
            'contract_name'=>$this->contract_name,
            'contract_type'=>$this->contract_type,
            'signature_date'=>$this->_signature_date,
            'effective_date'=>$this->_effective_date,
            'expire_date'=>$this->_expire_date,
            'extended' => $this->extended,
            'addition_info' => $this->addition_info,
            'initiator' => $this->initiator
        ];
    }

}

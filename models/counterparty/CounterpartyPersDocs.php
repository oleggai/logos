<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;
use app\models\common\DateShortStringBehavior;

/**
 * This is the model class for table "{{%counterparty_pers_docs}}".
 *
 * @property string $id
 * @property string $counterparty
 * @property string $doc_type
 * @property string $doc_serial_num
 * @property string $doc_num
 * @property string $doc_date
 * @property string $_doc_date
 *
 * @property Counterparty $counterparty0
 * @property ListPersDocType $docType
 */
class CounterpartyPersDocs extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_pers_docs}}';
    }

    /**
     * Поведения
     */
    function behaviors()
    {
        return [
            [
                'class' => DateShortStringBehavior::className(),
                'attributes' => [
                    '_doc_date' => 'doc_date'
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['doc_type', 'doc_serial_num', 'doc_num', '_doc_date'], 'required'],
            [['counterparty', 'doc_type'], 'integer'],
            [['_doc_date'], 'validateDateShort'],
            [['doc_serial_num'], 'string', 'max' => 10],
            [['doc_num'], 'string', 'max' => 50]
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
            'doc_type' => Yii::t('counterparty', 'Doc Type'),
            'doc_serial_num' => Yii::t('counterparty', 'Doc Serial Num'),
            'doc_num' => Yii::t('counterparty', 'Doc Num'),
            'doc_date' => Yii::t('counterparty', 'Doc Date'),
            '_doc_date' => Yii::t('counterparty', 'Doc Date'),
        ];
    }


    public function validateDateShort($attribute){
        if (!DateShortStringBehavior::validate($_POST['Counterparty']['counterpartyPersDocs'][$attribute]))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
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
    public function getDocType()
    {
        return $this->hasOne(ListPersDocType::className(), ['id' => 'doc_type']);
    }
}

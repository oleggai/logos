<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%counterparty_sign}}".
 *
 * @property string $id
 * @property string $counterparty_id
 * @property string $counterparty_sign_id
 *
 * @property Counterparty $counterparty
 * @property ListCounterpartySign $counterpartySign
 */
class CounterpartySign extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%counterparty_sign}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counterparty_id', 'counterparty_sign_id'], 'required'],
            [['counterparty_id', 'counterparty_sign_id'], 'integer'],
            [['counterparty_id', 'counterparty_sign_id'], 'unique', 'targetAttribute' => ['counterparty_id', 'counterparty_sign_id'], 'message' => 'The combination of Counterparty ID and Counterparty Sign ID has already been taken.']
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
            'counterparty_sign_id' => Yii::t('counterparty', 'Counterparty Sign ID'),
        ];
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
    public function getCounterpartySign()
    {
        return $this->hasOne(ListCounterpartySign::className(), ['id' => 'counterparty_sign_id']);
    }

    public static function getList($empty = false, $lang=null) {

        if (!$lang)
            $lang = Yii::$app->language;

        $models = self::find()->all();
        $result = ArrayHelper::map($models, 'id', "name_$lang");

        if ($empty)
            $result = [null => ''] +  $result;
        return  $result;
    }
}

<?php

namespace app\models\counterparty;

use Yii;

/**
 * Класс модели АПИ Контрагентов
 *
 * @property string $id
 * @property string $counterparty Ссылка на контрагента
 * @property string $api_key Ключ API
 * @property string $date_create Дата создания
 * @property string $date_valid Дата окончания срока действия
 * @property integer $status Статус 1-действует, 2-не действует
 *
 * @property Counterparty $counterparty0
 */
class CounterpartyApi extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yii2_counterparty_api';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['counterparty', 'api_key'], 'required'],
            [['counterparty', 'status'], 'integer'],
            [['date_create', 'date_valid'], 'safe'],
            [['api_key'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'counterparty' => 'Ссылка на контрагента',
            'api_key' => 'Ключ API',
            'date_create' => 'Дата создания',
            'date_valid' => 'Дата окончания срока действия',
            'status' => 'Статус 1-действует, 2-не действует',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterparty0()
    {
        return $this->hasOne(Counterparty::className(), ['id' => 'counterparty']);
    }
}

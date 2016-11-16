<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%list_counterparty_sign}}".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $name //имя на текущем языке
 *
 * @property CounterpartySign[] $counterpartySigns
 */
class ListCounterpartySign extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_counterparty_sign}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
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
            'id' => Yii::t('counterparty', 'ID'),
            'name_en' => Yii::t('counterparty', 'Name En'),
            'name_ru' => Yii::t('counterparty', 'Name Ru'),
            'name_uk' => Yii::t('counterparty', 'Name Uk'),
            'name' => Yii::t('counterparty', 'Counterparty sign'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartySigns()
    {
        return $this->hasMany(CounterpartySign::className(), ['counterparty_sign_id' => 'id']);
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
     * Получение списка статусов контрагентов в виде ассоциативного массива, где ключ - id, значение - название
     * @param bool $empty true = добавлять пустое значение
     * @return array массив типов
     */
    public static function getList($empty = false, $lang=null) {
        if (!$lang)
            $lang = Yii::$app->language;

        $result = ArrayHelper::map(ListCounterpartySign::find()->all(), 'id', "name_$lang");

        if ($empty)
            $result = [null => ''] +  $result;
        return  $result;
    }
}

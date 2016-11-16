<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%list_sex}}".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 *
 * @property CounterpartyContactPers[] $counterpartyContactPers
 * @property CounterpartyPrivatPers[] $counterpartyPrivatPers
 */
class ListSex extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_sex}}';
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
            'name' => Yii::t('counterparty', 'Sex'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyContactPers()
    {
        return $this->hasMany(CounterpartyContactPers::className(), ['sex' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyPrivatPers()
    {
        return $this->hasMany(CounterpartyPrivatPers::className(), ['sex' => 'id']);
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
     * Получение списка виде ассоциативного массива, где ключ - id, значение - короткое название
     * @param bool $empty true = добавлять пустое значение
     * @return array массив
     */
    public static function getList($empty = false) {
        $models = ListSex::find()->all();
        $result = ArrayHelper::map($models, 'id', 'name');

        if ($empty)
            $result = [null => ''] +  $result;
        return  $result;
    }

}

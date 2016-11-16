<?php

namespace app\models\dictionaries\address;

use app\models\counterparty\CounterpartyManualAdress;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;

/**
 * This is the model class for table "{{%list_adress_kind}}".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 *
 * @property CounterpartyManualAdress[] $counterpartyManualAdresses
 * @property mixed name
 */
class ListAdressKind extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_adress_kind}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
            [['name_en', 'name_ru', 'name_uk'], 'unique'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('adress', 'ID'),
            'name_en' => Yii::t('adress', 'Name En'),
            'name_ru' => Yii::t('adress', 'Name Ru'),
            'name_uk' => Yii::t('adress', 'Name Uk'),
            'name' => Yii::t('adress', 'Adress Kind'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyManualAdresses()
    {
        return $this->hasMany(CounterpartyManualAdress::className(), ['adress_kind' => 'id']);
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
     * Получение списка видов адресов в виде ассоциативного массива, где ключ - id, значение - название
     * @param bool $empty true = добавлять пустое значение
     * @return array массив видов
     */
    public static function getList($empty = false) {
        $models = ListAdressKind::find()->all();
        $result = ArrayHelper::map($models, 'id', 'name');

        if ($empty)
            $result = [null => ''] +  $result;
        return  $result;
    }

}

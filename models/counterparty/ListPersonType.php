<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель справочника типов контрагентов
 * @author Tochonyi DM
 * @category Counterparty
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $name //имя на текущем языке
 *
 * @property Counterparty[] $counterparties
 */
class ListPersonType extends CommonModel
{

    const PERSON_TYPE_PRIVATE = 1; // физлицо
    const PERSON_TYPE_LEGAL = 2; // юрлицо
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_person_type}}';
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
            'name' => Yii::t('counterparty', 'Person Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterparties()
    {
        return $this->hasMany(Counterparty::className(), ['person_type' => 'id']);
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
     * Получение списка типов контрагентов в виде ассоциативного массива, где ключ - id, значение - название
     * @param bool $empty true = добавлять пустое значение
     * @return array массив типов
     */
    //public static function getList($empty = false) {
    //    $models = ListPersonType::find()
    //        ->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])
    //        ->all();
    //    $result = ArrayHelper::map($models, 'id', 'name');

    //    if ($empty)
    //        $result = [null => ''] +  $result;
    //    return  $result;
    //}

}

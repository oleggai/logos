<?php

namespace app\models\dictionaries\address;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Справочник видов графика
 *
 * @property string $id Код
 * @property string $name_en Наименование англ.
 * @property string $name_ru Наименование рус.
 * @property string $name_uk Наименование укр.
 * @property integer $visible Доступность выбора
 *
 * @property ListCityScheduleReception[] $listCityScheduleReceptions
 */
class ListScheduleType extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_schedule_type}}';
    }

    public static function getList( $empty = false) {

        $models = self::find()
            ->where('visible = '.self::VISIBLE.' AND state != '.CommonModel::STATE_DELETED)
            ->all();

        $result =  ArrayHelper::map($models, 'id', 'name');

        if ($empty)
            $result = [null=>''] +$result;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['visible'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 100],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique']
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
            'visible' => Yii::t('adress', 'Visible'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListCityScheduleReceptions()
    {
        return $this->hasMany(ListCityScheduleReception::className(), ['schedule_type' => 'id']);
    }

    public function getName(){
        return $this->{'name_'.Yii::$app->language};
    }

}

<?php

namespace app\models\dictionaries\address;

use app\models\common\CommonModel;
use Yii;

/**
 * Справочник направления перевозок
 *
 * @property string $city1 Ссылка на справочник нас.пунктов
 * @property string $city2 Ссылка на справочник нас.пунктов
 *
 * @property ListCity $city1Model
 * @property ListCity $city2Model
 */
class ListCityRoute extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_city_route}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['city1', 'city2'], 'required'],
            [['city1', 'city2'], 'integer']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'city1' => Yii::t('adress', 'City1'),
            'city2' => Yii::t('adress', 'City2'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity1Model()
    {
        return $this->hasOne(ListCity::className(), ['id' => 'city1']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity2Model()
    {
        return $this->hasOne(ListCity::className(), ['id' => 'city2']);
    }
}

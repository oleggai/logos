<?php

namespace app\models\dictionaries\warehouse;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;

/**
 * Модель для типов товаров
 * @author Richok FG
 * @category warehouse
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 *
 * @property ListWarehouseZone[] $listWarehouseZones
 */
class ListCargoType extends CommonModel
{
    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%list_cargo_type}}';
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
            'id' => Yii::t('warehouse', 'ID'),
            'name_en' => Yii::t('warehouse', 'Name En'),
            'name_ru' => Yii::t('warehouse', 'Name Ru'),
            'name_uk' => Yii::t('warehouse', 'Name Uk'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListWarehouseZones()
    {
        return $this->hasMany(ListWarehouseZone::className(), ['cargo_type' => 'id']);
    }

    /**
     * Возвращает список сущностей
     * @param bool $empty false
     * @param string $field name
     * @param string $lang current language
     * @return array
     */
    public static function getList($empty = false, $field = 'name', $lang = '') {

        if (empty($lang))
            $lang = \Yii::$app->language;

        if ($field == 'name')
            $field = $field . '_' . $lang;

        $arr = ListCargoType::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }
}

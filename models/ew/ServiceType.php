<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель вида доставки
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property string $name_short_ru
 * @property string $name_short_en
 * @property string $name_short_uk
 * @property integer $visible
 * @property integer $state
 *
 */
class ServiceType extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_service_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 100],
            [['name_short_ru', 'name_short_en', 'name_short_uk'], 'string'],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique'],
            [['visible', 'state'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ew', 'ID'),
            'name_ru' => Yii::t('ew', 'Name Ru'),
            'name_en' => Yii::t('ew', 'Name En'),
            'name_uk' => Yii::t('ew', 'Name Uk'),
            'name_short_ru' => Yii::t('ew', 'Name Short Ru'),
            'name_short_en' => Yii::t('ew', 'Name Short En'),
            'name_short_uk' => Yii::t('ew', 'Name Short Uk'),
        ];
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

        $arr = ListEntityType::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }


}

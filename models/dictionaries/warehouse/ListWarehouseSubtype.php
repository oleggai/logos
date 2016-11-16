<?php

namespace app\models\dictionaries\warehouse;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;

/**
 * Модель для подтипов подразделений
 * @author Richok FG
 * @category warehouse
 *
 * @property string $id
 * @property string $name
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 *
 * @property ListWarehouse[] $listWarehouses
 */
class ListWarehouseSubtype extends CommonModel
{
    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%list_warehouse_subtype}}';
    }

    /**
     * Получить список моделей подразделений этого подтипа
     * @return \yii\db\ActiveQuery
     */
    public function getListWarehouses()
    {
        return $this->hasMany(ListWarehouse::className(), ['warehouse_subtype' => 'id']);
    }

    /**
     * Получить название подтипа подразделения на текущем языке
     * @return string|void название подтипа на текущем языке
     */
    public function getName() {
        return $this->getAttribute('name_' . Yii::$app->language);
    }

    /**
     * Получить список подтипов подразделений
     * @param bool $empty Создать пустую запись в начале списка
     * @param null $lang Язык записей
     * @return array Список подтипов подразделений
     */
    public static function getList($empty = false, $lang = null) {

        if (!$lang)
            $lang = Yii::$app->language;

        $arr = ArrayHelper::map(ListWarehouseSubtype::find()->all(), 'id', "name_$lang");
        if ($empty)
            $arr = [null => ''] + $arr;

        return $arr;
    }
}

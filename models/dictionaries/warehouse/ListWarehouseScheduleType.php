<?php

namespace app\models\dictionaries\warehouse;

use Yii;
use app\models\common\CommonModel;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yii2_list_warehouse_schedule_type".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property integer $visible
 * @property integer $state
 *
 * @property ListWarehouseScheduleReception[] $listWarehouseScheduleReceptions
 */
class ListWarehouseScheduleType extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_warehouse_schedule_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['visible', 'state'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 100],
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
            'visible' => Yii::t('warehouse', 'Visible'),
            'state' => Yii::t('warehouse', 'State'),
        ];
    }

    public function getName() {
        return $this->getAttribute('name_' . Yii::$app->language);
    }

    public static function getList($empty = false) {

        $models = self::find()
            ->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])
            ->all();

        $result =  ArrayHelper::map($models, 'id', 'name');

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListWarehouseScheduleReceptions()
    {
        return $this->hasMany(ListWarehouseScheduleReception::className(), ['warehouse_schedule_type' => 'id']);
    }


}

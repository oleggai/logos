<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%list_wb_order_type}}".
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property integer $visible
 *
 * @property EwRelatedOrder[] $ewRelatedOrders
 */
class WbOrderType extends CommonModel
{
    const VISIBLE = 1;

    const ClientOrderId = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_wb_order_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['visible'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'unique'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 100]
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
//            'counterparty' => Yii::t('ew', 'Counterparty'),
            'visible' => Yii::t('ew', 'Visible'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEwRelatedOrders()
    {
        return $this->hasMany(EwRelatedOrder::className(), ['wb_order_type' => 'id']);
    } 

    /**
     * Возвращает список
     * @param bool $empty
     * @param string $field
     * @return array
     */
    public static function getVisibleList($empty = false, $field = 'name', $lang = '') {

        if (empty($lang))
            $lang = \Yii::$app->language;

        if ($field == 'name')
            $field = $field . '_' . $lang;

        $arr = WbOrderType::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    public function toJson() {

        return [
            'id' => $this->id,
            'state' => $this->state,
            'name_ru' => $this->name_ru,
            'name_en' => $this->name_en,
            'name_uk' => $this->name_uk,
            'visibilityText' => $this->visibilityText
        ];
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);

        $this->operation = self::OPERATION_NONE;
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyWbOrderType($params);
    }

    public function copyWbOrderType($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $type = WbOrderType::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
    }

    public function getGridOperations() {
        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app','Copy'),
        ];
    }

    public function getGridOperationsOptions() {
        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before'=>true, 'tab_name_sufix'=>'copy'],
        ];
    }

}

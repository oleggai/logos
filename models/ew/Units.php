<?php

namespace app\models\ew;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\models\common\CommonModel;


/**
 * This is the model class for table "{{%units}}".
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property string $name_short_ru
 * @property string $name_short_en
 * @property string $name_short_uk
 *
 * @property InvoicePosition[] $invoicePositions
 */
class Units extends CommonModel
{

    const KG_UNIT   = 1;
    const PCS_UNIT  = 2;
    const M_UNIT    = 3;
    const PACK_UNIT = 4;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%units}}';
    }
  /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_ru', 'name_en', 'name_uk', 'name_short_ru', 'name_short_en', 'name_short_uk'], 'required'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 255],
            [['name_short_ru', 'name_short_en', 'name_short_uk'], 'string', 'max' => 10],
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
            'id' => Yii::t('app', 'ID'),
            'name_ru' => Yii::t('app', 'Name Ru'),
            'name_en' => Yii::t('app', 'Name En'),
            'name_uk' => Yii::t('app', 'Name Uk'),
            'name_short_ru' => Yii::t('app', 'Name Short Ru'),
            'name_short_en' => Yii::t('app', 'Name Short En'),
            'name_short_uk' => Yii::t('app', 'Name Short Uk'),
        ];
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyUnit($params);
    }

    public function copyUnit($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $type = Units::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoicePositions()
    {
        return $this->hasMany(InvoicePosition::className(), ['units_of_measurement' => 'id']);
    }


    public static function getById($id) {
        return static::findOne(['id' => $id]);
    }
/*
    public static function getList($field, $empty = false) {
        if (in_array($field,['name','name_short'])) $field=$field.'_'.Yii::$app->language;
        $arr = Units::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

*/
    public function getName(){
        return $this->{'name_'.Yii::$app->language};
    }


    public function getNameShort(){
        return $this->{'name_short_'.Yii::$app->language};
    }

    /**
     * Возвращает данные для грида
     * @return array
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ru' => $this->name_ru,
            'name_uk' => $this->name_uk,
            'state' => $this->state,
            'name_short_en' => $this->name_short_en,
            'name_short_ru' => $this->name_short_ru,
            'name_short_uk' => $this->name_short_uk,
            'visible' => $this->visible,
            'visibilityText' => $this->visibilityText
        ];
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);
    }

    public function getGridOperations() {

        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app', 'Copy'),
        ];
    }

    public function getGridOperationsOptions() {

        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before' => true, 'tab_name_sufix' => 'copy'],
        ];
    }
}

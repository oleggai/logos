<?php

namespace app\models\dictionaries\address;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;
use app\models\common\Langs;
use yii\helpers\Url;

/**
 * This is the model class for table "yii2_list_building_type".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $name_short_ru
 * @property string $name_short_en
 * @property string $name_short_uk
 * @property string $name
 * @property string $nameShort
 * @property integer $level
 * @property integer $state
 * @property integer $visible
 *
 * @property ListBuilding[] $listBuildings
 * @property ListBuilding[] $listBuildings0
 * @property ListBuilding[] $listBuildings1
 */
class ListBuildingType extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_building_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_en', 'name_ru', 'name_uk', 'name_short_ru', 'name_short_en', 'name_short_uk'], 'required'],
            [['level', 'state', 'visible'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 100],
            [['name_short_ru', 'name_short_en', 'name_short_uk'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('address', 'ID'),
            'name_en' => Yii::t('address', 'Name En'),
            'name_ru' => Yii::t('address', 'Name Ru'),
            'name_uk' => Yii::t('address', 'Name Uk'),
            'name_short_ru' => Yii::t('address', 'Name Short Ru'),
            'name_short_en' => Yii::t('address', 'Name Short En'),
            'name_short_uk' => Yii::t('address', 'Name Short Uk'),
            'level' => Yii::t('address', 'Number type level'),
            'state' => Yii::t('address', 'State'),
            'visible' => Yii::t('address', 'Visible'),
        ];
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyBuildingType($params);
    }

    public function copyBuildingType($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $type = ListBuildingType::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
    }

    public function getName() {
        return $this->getAttribute('name_' . Yii::$app->language);
    }

    public function getNameShort() {
        return $this->getAttribute('name_short_' . Yii::$app->language);
    }

    public static function getList($field = 'name', $empty = false, $lang = null, $level = 1, $andWhere = '1 = 1') {
        if (!$lang)
            $lang = Yii::$app->language;
        $models = self::find()
            ->where('visible = :visible AND state != :state AND level = :level',
                [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED, ':level' => $level])
            ->andWhere($andWhere)
            ->all();

        $result = ArrayHelper::map($models, 'id', $field . '_' . $lang);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    public static function getLevelList() {
        return [
            1 => Yii::t('address', '1-st level'),
            2 => Yii::t('address', '2-nd level'),
            3 => Yii::t('address', '3-rd level'),
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

    public function toJson() {

        return [
            'id' => $this->id,
            'state' => $this->state,
            'name_ru' => $this->name_ru,
            'name_en' => $this->name_en,
            'name_uk' => $this->name_uk,
            'name_short_ru' => $this->name_short_ru,
            'name_short_en' => $this->name_short_en,
            'name_short_uk' => $this->name_short_uk,
            'visibilityText' => $this->visibilityText
        ];
    }

    public function getFilters(){

        return  [
            ['id' => 'f_building_lang', 'type' => self::FILTER_DROPDOWN, 'value' => Yii::$app->language,
                'label' => Yii::t('app','Language') . ':', 'items' => Langs::$Names, 'lang_selector' => true],

            ['id' => 'f_building_id', 'field' => 'id', 'operation' => '='],

            ['id' => 'f_building_name', 'operation' => 'starts', 'field' => 'name', 'lang_field' => true,
                'label' => Yii::t('address', 'Name')],

            ['id' => 'f_building_name_short', 'operation' => 'starts', 'field' => 'name_short', 'lang_field' => true,
                'label' => Yii::t('address', 'Name short')],

            ['id' => 'f_building_state', 'type' => self::FILTER_DROPDOWN, 'items' => $this->getStateList(true),
                'operation' => '=', 'field' => 'state', 'label' => Yii::t('address', 'State')],

            ['id' => 'f_builing_visible', 'type' => self::FILTER_DROPDOWN,
                'items' => $this->getVisibilityList(true), 'operation' => '=', 'field' => 'visible'],
        ];
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

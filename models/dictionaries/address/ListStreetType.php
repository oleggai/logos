<?php

namespace app\models\dictionaries\address;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;
use app\models\common\Langs;
use yii\helpers\Url;

/**
 * Справочник типов улиц
 *
 * @property string $id Код
 * @property string $name_en Наименование англ
 * @property string $name_ru Наименование рус
 * @property string $name_uk Наименование укр
 * @property string $name Наименование на текущем языке
 * @property string $name_short_en Наименование сокращенное англ
 * @property string $name_short_ru Наименование сокращенное рус
 * @property string $name_short_uk Наименование сокращенное укр
 * @property integer $visible Доступность выбора
 * @property integer $state Состояние
 *
 * @property ListStreet[] $listStreets
 */
class ListStreetType extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_street_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(),
        [
            [['name_en', 'name_ru', 'name_uk', 'name_short_ru', 'name_short_en', 'name_short_uk'], 'required'],
            [['visible'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 100],
            [['name_short_ru', 'name_short_en', 'name_short_uk'], 'string', 'max' => 10],
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
            'id' => Yii::t('address', 'ID'),
            'name_en' => Yii::t('address', 'Name En'),
            'name_ru' => Yii::t('address', 'Name Ru'),
            'name_uk' => Yii::t('address', 'Name Uk'),
            'visible' => Yii::t('address', 'Visible'),
            'name_short_ru' => Yii::t('address', 'Name Short Ru'),
            'name_short_en' => Yii::t('address', 'Name Short En'),
            'name_short_uk' => Yii::t('address', 'Name Short Uk'),
            'operation' => Yii::t('app', 'Operation')
        ];
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyStreetType($params);
    }

    public function copyStreetType($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $type = ListStreetType::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListStreets()
    {
        return $this->hasMany(ListStreet::className(), ['street_type' => 'id']);
    }

    public function getName() {
        return $this->getAttribute('name_' . Yii::$app->language);
    }

    public static function getList($field = 'name', $empty = false, $lang = null, $andWhere = '1 = 1') {
        if (!$lang)
            $lang = Yii::$app->language;
        $models = self::find()
            ->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])
            ->andWhere($andWhere)
            ->all();

        $result = ArrayHelper::map($models, 'id', $field.'_'.$lang);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    public function toJson(){

        return [
            'id' => $this->id,
            'name_ru' => $this->name_ru,
            'name_en' => $this->name_en,
            'name_uk' => $this->name_uk,
            'name_short_ru' => $this->name_short_ru,
            'name_short_en' => $this->name_short_en,
            'name_short_uk' => $this->name_short_uk,
            'state' =>$this->state,
            'stateText' =>$this->stateText,
            'visibilityText' =>$this->visibilityText,
        ];
    }


    public function getFilters(){

        return  [

            ['id'=>'f_street_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,'label'=>Yii::t('app','Language').':',
                'items'=>Langs::$Names, 'lang_selector'=>true],

            ['id'=>'f_street_id', 'field' => 'id','operation' => '='],
            ['id'=>'f_street_name', 'operation' => 'starts', 'field' => 'name','lang_field' => true, 'label' => Yii::t('address', 'Name')],
            ['id'=>'f_street_name_short', 'operation' => 'starts', 'field' => 'name_short', 'lang_field' => true, 'label' => Yii::t('address', 'Name short')],

            ['id'=>'f_street_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'operation' => '=', 'field' => 'state', 'label' => Yii::t('address', 'State')],

            ['id'=>'f_street_visible', 'type'=>self::FILTER_DROPDOWN, 'value'=>'',
                'items'=>$this->getVisibilityList(true), 'operation' => '=', 'field' => 'visible'],

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

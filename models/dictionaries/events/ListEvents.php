<?php

/**
 * В файле описана модель собитя
 *
 * @author Мельник И.А.
 * @category События
 */

namespace app\models\dictionaries\events;

use app\models\common\CommonModel;
use app\models\common\Translator;
use app\models\common\Langs;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Модель события
 *
 * @property string $id Идентификатор
 * @property string $parent_id Отцовский элемент
 * @property string $code Код
 * @property integer $level Уровень
 * @property integer $state Состояние
 * @property integer $visible Видимость
 * @property string $description Описание
 * @property string $name Название
 * @property string $nameUk Название на украинском
 * @property string $nameEn Название на английском
 * @property string $nameRu Название на русском
 *
 * @property ListEvents $parent Отцовский элемент
 * @property ListEvents[] $childs
 * @property mixed visibilityList
 * @property mixed isCategory
 * @property mixed categoryJson
 * @property mixed visibilityText
 */
class ListEvents extends CommonModel
{
    /**
     * @var array Все переводы названия
     */
    public $names;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_events}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['parent_id', 'level', 'state', 'visible'], 'integer'],
            [['code'], 'required'],
            [['code'], 'unique'],
            [['code'], 'string', 'max' => 10],
            [['description'], 'string', 'max' => 255],
            [['nameEn', 'nameUk', 'nameRu'], 'required'],
            [['nameEn', 'nameUk', 'nameRu'], 'string', 'max' => 100],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('events', 'ID'),
            'parent_id' => Yii::t('events', 'Parent ID'),
            'code' => Yii::t('events', 'Code'),
            'category_code' => Yii::t('events', 'Category code'),
            'level' => Yii::t('events', 'Level'),
            'state' => Yii::t('events', 'State Event'),
            'visible' => Yii::t('events', 'Availability of choice'),
            'visibilityText' => Yii::t('events', 'Availability of choice'),
            'category_visibilityText' => Yii::t('events', 'Category availability of choice'),
            'description' => Yii::t('events', 'Description'),
            'name' => Yii::t('events', 'Name Event'),
            'nameEn' => Yii::t('events', 'Name (Eng)'),
            'nameUk' => Yii::t('events', 'Name (Ukr)'),
            'nameRu' => Yii::t('events', 'Name (Rus)'),
            'category_nameEn' => Yii::t('events', 'Category name (Eng)'),
            'category_nameUk' => Yii::t('events', 'Category name (Ukr)'),
            'category_nameRu' => Yii::t('events', 'Category name (Rus)'),
            'category_name' => Yii::t('events', 'Category name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(ListEvents::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChilds()
    {
        return $this->hasMany(ListEvents::className(), ['parent_id' => 'id']);
    }

    /**
     * Получение названия на английском
     * @return string Название на английском
     */
    public function getNameEn() {
        if ($this->names != null && array_key_exists('en',$this->names))
            return $this->names['en'];

        return '';
    }

    /**
     * Установка названия на английском
     * @param $value string Название на английском
     */
    public function setNameEn($value) {
        $this->names['en'] = $value;
    }

    /**
     * Получение названия на украинском
     * @return string Название на украинском
     */
    public function getNameUk() {
        if ($this->names != null && array_key_exists('uk',$this->names))
            return $this->names['uk'];

        return '';
    }

    /**
     * Установка названия на украинском
     * @param $value string Название на украинском
     */
    public function setNameUk($value) {
        $this->names['uk'] = $value;
    }

    /**
     * Получение названия на русском
     * @return string Нназвание на русском
     */
    public function getNameRu() {
        if ($this->names != null && array_key_exists('ru',$this->names))
            return $this->names['ru'];

        return '';
    }

    /**
     * Установка названия на русском
     * @param $value string Название на русском
     */
    public function setNameRu($value) {
        $this->names['ru'] = $value;
    }

    /**
     * Получение названия на язвыке систем ы
     * @return string Нназвание на русском
     */
    public function getName() {
        if ($this->names != null && array_key_exists(Yii::$app->language,$this->names))
            return $this->names[Yii::$app->language];

        return '';
    }

    /**
     * Метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {

        parent::afterFind();

        // загрузка переводов
        $this->names = Translator::getAll('{{%list_events_translate}}', 'name', 'list_events_id', $this->id);
    }

    /**
     * Метод перед сохранением
     * @param bool $insert Вставка или обновление
     * @return bool Результат метода
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            // если операция Удаление, то удалить все дочерние элементы
            if ($this->operation == self::OPERATION_DELETE && $this->level < 2) {
                $children = ListEvents::findAll(['parent_id' => $this->id]);
                foreach ($children as $child) {
                    $child->delete();
                }

            }
            return true;
        }

        return false;
    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            // сохранение всех переводов
            Translator::setAll('{{%list_events_translate}}', 'list_events_id', $this->id, ['name' => $this->names]);
        }
        $this->saveSServiceData($insert);
        $this->operation = self::OPERATION_NONE;
    }

    /**
     * Формирование полей по-умолчанию
     * @param $params
     * @param string $parent_ref string Ссылка на родительский элемент
     */
    public function generateDefaults($params, $parent_ref = null) {

        if ($this->hasErrors())
            return;

        if ($parent_ref==0)
            $parent_ref = null;

        $this->parent_id = $parent_ref;
        $this->state = self::STATE_CREATED;
        $this->level = 1;

        if ($parent_ref!=null){
            $this->level = $this->parent->level+1;
        }

        if ($params['operation'] != null)
            $this->copyEvents($params);
    }

    public function copyEvents($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $event = ListEvents::findOne(['id' => $params['id']]);
            if($event) {
                $this->attributes = $event->getAttributes();
                $this->names = $event->names;
            }
        }
    }


    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson($prefix=''){
        return  array_merge($this->categoryJson,
            [
                $prefix.'id'=>$this->id,
                $prefix.'code'=>$this->code,
                $prefix.'nameUk'=>$this->nameUk,
                $prefix.'nameEn'=>$this->nameEn,
                $prefix.'nameRu'=>$this->nameRu,
                $prefix.'state'=>$this->state,
                $prefix.'level'=>$this->level,
                $prefix.'visibilityText'=>$this->visibilityText,
                $prefix.'description'=>$this->description,
                $prefix.'parent_ref'=>$this->parent_id,
            ]);
    }

    /**
     * Получение категории в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function getCategoryJson(){
        if ($this->parent!=null){
            return $this->parent->toJson('category_');
        }

        return [];
    }

    /**
     * Получение максимального уровня иерархии
     * @return int Максимальный уровень
     */
    public function getMaxLevel(){
        return 2;
    }

    public function getIsCategory(){
        return $this->level == 1;
    }

    /**
     * Получение списка полей для виджета фильтрации
     * @return array массив для виджета фильтрации
     */
    public function getFilters(){

        return  [
            [ 'id' => 'use_hierarchy', 'hidden' => true],
            [ 'id' => 'parent_ref', 'value'=>null, 'hidden' => true],

            ['id'=>'f_events_id','operation' => '=', 'field' => ListEvents::tableName().'.code',
                'label'=>$this->getAttributeLabel('code')],

            ['id'=>'f_events_parent_code','operation' => '=', 'field' => 'p.code',
                'label'=>$this->getAttributeLabel('category_code')],

            ['id'=>'f_events_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,
                'items'=>Langs::$Names, 'operation' => '=', 'field' => 'tr_s.lang', 'label'=>Yii::t('app', 'Language')],

            ['id'=>'f_events_parent_name','operation' => 'starts', 'field' => 'tr_p.name',
                'label'=>$this->getAttributeLabel('category_name')],

            ['id'=>'f_events_name','operation' => 'starts', 'field' => 'tr_s.name',
                'label'=>$this->getAttributeLabel('name')],

            ['id'=>'f_events_parent_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'operation' => '=',
                'field' => 'coalesce(p.state,'.ListEvents::tableName().'.state)',
                'label'=>Yii::t('events', 'Category State')],

            ['id'=>'f_events_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'operation' => '=', 'field' => ListEvents::tableName().'.state',
                'label'=>$this->getAttributeLabel('state')],

            ['id'=>'f_events_parent_visible', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getVisibilityList(true), 'operation' => '=',
                'field'=>'coalesce(p.visible,'.ListEvents::tableName().'.visible)',
                'label'=>$this->getAttributeLabel('category_visibilityText')],

            ['id'=>'f_events_visible', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getVisibilityList(true), 'operation' => '=', 'field'=>ListEvents::tableName().'.visible',
                'label'=>$this->getAttributeLabel('visible')],

        ];
    }

    public static function getList($key='code', $field='name', $prefix = ''){

        $selfTable= self::tableName();
        $translateTable= '{{%list_events_translate}}';
        $lang = Yii::$app->language;

        $arr = self::find()
            ->select("$key,$field")
            ->leftJoin("$translateTable tr", "tr.list_events_id = $selfTable.id and lang='$lang'" )
            ->where(" visible = ".self::VISIBLE.
                    " and state != ".CommonModel::STATE_DELETED.
                    " and level = ".self::getMaxLevel().
                    " and code like '".$prefix."%'")
            ->all();

        return [null=>'']+ArrayHelper::map($arr, $key, $field);
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

    public static function checkUniqueEventNum($eventNum) {
        return ListEvents::findOne(['code' => $eventNum]) ? Yii::t('error', "Attention! $eventNum already exists") : '';
    }
}

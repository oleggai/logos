<?php

namespace app\models\dictionaries\nondelivery;

use Yii;
use yii\db\Query;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\common\Translator;
use app\models\common\CommonModel;
use app\models\common\Langs;
use app\models\dictionaries\currency\Currency;

/**
 * Модель причин недоставки
 * @author Richok FG
 * @category nondelivery
 *
 * @property string $id
 * @property string $parent_id
 * @property integer $occ_zone
 * @property integer $state
 * @property integer $visible
 *
 * @property NonDelivery $parent
 * @property NonDelivery[] $listNondeliveries
 */
class NonDelivery extends CommonModel
{
    /**
     * @var int зона возникновения НПИ (за пределами Украины)
     */
    const OCC_ZONE_NPI = 1;
    /**
     * @var int зона возникновения НПИ (ЦСС)
     */
    const OCC_ZONE_NPI_CSS = 2;
    /**
     * @var int зона возникновения НП
     */
    const OCC_ZONE_NP = 3;
    /**
     * @var string array массив названий на всех языках
     */
    public $names;

    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%list_nondelivery}}';
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return array_merge(parent::rules(),
            [
                [['parent_id', 'occ_zone', 'state', 'visible'], 'integer'],
                [['occ_zone', 'visible', 'nameEn', 'nameUk', 'nameRu'], 'required'],
                [['nameEn', 'nameUk', 'nameRu'], 'validateUnique'],
                [['nameEn', 'nameUk', 'nameRu'], 'string', 'max' => 100]
            ]);
    }

    /**
     * Надписи для полей
     * @return array массив названий полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('nondelivery', 'ID'),
            'name' => Yii::t('nondelivery', 'Name'),
            'nameEn' => Yii::t('nondelivery', 'Name (Eng)'),
            'nameUk' => Yii::t('nondelivery', 'Name (Ukr)'),
            'nameRu' => Yii::t('nondelivery', 'Name (Rus)'),
            'parent_id' => Yii::t('nondelivery', 'Non Delivery'),
            'occ_zone' => Yii::t('nondelivery', 'Occurrence zone'),
            'state' => Yii::t('nondelivery', 'State'),
            'visible' => Yii::t('nondelivery', 'Availability of choice'),
        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием новой валюты
     * @param int ссылка на родителя
     */
    public function generateDefaults($params, $parent_ref = null) {
        if ($this->hasErrors())
            return;

        if ($parent_ref == 0)
            $parent_ref = null;

        if ($parent_ref == null)
            $this->occ_zone = 0;

        $this->parent_id = $parent_ref;
        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyNonDelivery($params);

    }

    public function copyNonDelivery($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $nonDelivery = NonDelivery::findOne(['id' => $params['id']]);
            if($nonDelivery) {
                $this->attributes = $nonDelivery->getAttributes();
                $this->names = $nonDelivery->names;
            }
        }
    }


    /**
     * Получение названия причины недоставки на английском
     * @return string название на английском
     */
    public function getNameEn() {
        return $this->names['en'];
    }

    /**
     * Установка названия причины недоставки на английском
     * @param string $value название на английском
     */
    public function setNameEn($value) {
        $this->names['en'] = $value;
    }

    /**
     * Получение названия причины недоставки на украинском
     * @return string название на украинском
     */
    public function getNameUk() {
        return $this->names['uk'];
    }

    /**
     * Установка названия причины недоставки на украинском
     * @param string $value название на украинском
     */
    public function setNameUk($value) {
        $this->names['uk'] = $value;
    }

    /**
     * Получение названия причины недоставки на русском
     * @return string название на русском
     */
    public function getNameRu() {
        return $this->names['ru'];
    }

    /**
     * Установка названия причины недоставки на русском
     * @param string $value название на русском
     */
    public function setNameRu($value) {
        $this->names['ru'] = $value;
    }

    /**
     * Получение названия на текущем языке
     * @return string название на текущем языке
     */
    public function getName() {
        return $this->names[Yii::$app->language];
    }

    /**
     * Определяет, является ли запись подтипом
     * @return bool true - если подтип
     */
    public function getIsSubtype() {
        if ($this->parent_id == null)
            return false;
        else
            return true;
    }

    /**
     * Получение причины недоставки по уникальному идентификатору
     * @param $id integer идентификатор причины
     * @return null|Currency модель причины недоставки
     */
    public static function getById($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Получение списка причин недоставки в виде ассоциативного массива, где ключ - id, значение - значение поля переданного параметром ('name' по-умолчанию)
     * @param string $field поле для отображения
     * @return array массив причин недоставки
     */
    public static function getList($field = 'name') {

        $arr = NonDelivery::find()->where('visible = :visible AND state != :state AND parent_id IS NOT NULL',
            [':visible' => self::VISIBLE, ':state' => self::STATE_DELETED])->all();

        $mass = ArrayHelper::map($arr, 'id', $field);
        $mass[0] = "--";
        return $mass;
    }

    /**
     * Получение название текущей видимости причины недоставки
     * @return string название текущей видимости
     */
    public function getVisibilityText(){
        return $this->getVisibilityList()[$this->visible];
    }

    public static function getOccZoneList($empty = false) {
        $r = [
            self::OCC_ZONE_NPI => Yii::t('nondelivery', 'NPI (outside Ukraine)'),
            self::OCC_ZONE_NPI_CSS => Yii::t('nondelivery', 'NPI (CSS)'),
            self::OCC_ZONE_NP => Yii::t('nondelivery', 'NP')
        ];
        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Получение родительской модели
     * @return \yii\db\ActiveQuery родительская модель
     */
    public function getParent() {
        return $this->hasOne(NonDelivery::className(), ['id' => 'parent_id']);
    }

    /**
     * Метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {
        parent::afterFind();

        $this->names = Translator::getAll('{{%list_nondelivery_translate}}', 'name', 'nondelivery_id', $this->id);
    }

    /**
     * Метод вызывается перед удалением сущности
     * @return bool флаг удаления
     */
    public function beforeDelete() {
        //Translator::delTranslation('{{%list_nondelivery_translate}}', 'nondelivery_id', $this->id);
        return parent::beforeDelete();
    }

    /**
     * Метод перед сохранением
     * @param bool $insert Вставка или обновление
     * @return bool Результат метода
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            // если операция Удаление, то удалить все дочерние элементы
            if ($this->operation == self::OPERATION_DELETE) {
                $children = NonDelivery::findAll(['parent_id' => $this->id]);
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
     * @param $insert
     * @param $changeAttributes
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        // добавление в лог операций над сущностью
        //$this->archive($insert ? LogNonDelivery::OPERATION_CREATE : LogNonDelivery::OPERATION_UPDATE);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            // созранение всех переводов
            Translator::setAll('{{%list_nondelivery_translate}}', 'nondelivery_id', $this->id, ['name' => $this->names]);
        }
        $this->saveSServiceData($insert);
        $this->operation = self::OPERATION_NONE;
    }

    public function getMaxLevel() {
        return 2;
    }

    function validateUnique($attribute, $params) {
        if ($this->operation == CommonModel::OPERATION_UPDATE) {
            return true;
        }
        $lang = $lang = strtolower(substr($attribute, 4));
        $value = $this->{$attribute};
        $res = (new Query())->from('{{%list_nondelivery_translate}}')->where(['name' => $value])->all();
        if ($res) {
            foreach ($res as $key => $val) {
                if ($val['lang'] == $lang && $val['name'] == $value) {
                    $this->addError($attribute, $this->getAttributeLabel($attribute) . ' ' . Yii::t('error', 'должно быть уникальным значением'));
                }
            }
        }
        return $this->errors ? false : true;
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля => значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'nameEn' => $this->nameEn,
            'nameUk' => $this->nameUk,
            'nameRu' => $this->nameRu,
            'occ_zone' => $this->getOccZoneList()[$this->occ_zone],
            'visible' => $this->visibilityText,
            'level' => $this->isSubtype ? 2 : 1,
            'parent_ref'=>$this->parent_id,
            'state' => $this->state,
        ];
    }

    /**
     * Получение списка полей для виджета фильтрации
     * @return array массив для виджета фильтрации
     */
    public function getFilters(){

        return  [
            [ 'id' => 'use_hierarchy', 'hidden' => true],
            [ 'id' => 'parent_ref', 'value'=>null, 'hidden' => true],

            ['id'=>'f_nondelivery_id','operation' => '=', 'field' => NonDelivery::tableName().'.id',
                'label'=>$this->getAttributeLabel('id')],
            ['id'=>'f_nondelivery_parent_id','operation' => '=', 'field' => 'p.id',
                'label'=>Yii::t('nondelivery', 'Parent ID')],

            ['id'=>'f_nondelivery_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,
                'items'=>Langs::$Names, 'operation' => '=', 'field' => 'tr_s.lang', 'label'=>Yii::t('app', 'Language')],

            ['id'=>'f_nondelivery_name','operation' => 'starts', 'field' => 'tr_s.name',
                'label'=>$this->getAttributeLabel('name')],

            ['id'=>'f_nondelivery_occ_zone', 'type'=>self::FILTER_DROPDOWN, 'value'=>null,
                'items'=>$this->getOccZoneList(true), 'operation' => '=', 'field' => NonDelivery::tableName().'.occ_zone',
                'label'=>$this->getAttributeLabel('occ_zone')],

            ['id'=>'f_nondelivery_parent_name','operation' => 'starts', 'field' => 'tr_p.name',
                'label'=>Yii::t('nondelivery', 'Parent Name')],

            ['id'=>'f_nondelivery_parent_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'operation' => '=',
                'field' => 'coalesce(p.state,'.NonDelivery::tableName().'.state)',
                'label'=>Yii::t('nondelivery', 'Parent State')],

            ['id'=>'f_nondelivery_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'operation' => '=', 'field' => NonDelivery::tableName().'.state',
                'label'=>$this->getAttributeLabel('state')],

            ['id'=>'f_nondelivery_parent_visible', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getVisibilityList(true), 'operation' => '=',
                'field'=>'coalesce(p.visible,'.NonDelivery::tableName().'.visible)',
                'label'=>Yii::t('nondelivery', 'Parent Visible')],

            ['id'=>'f_nondelivery_visible', 'type'=>self::FILTER_DROPDOWN, 'value'=>'',
                'items'=>$this->getVisibilityList(true), 'operation' => '=', 'field'=>NonDelivery::tableName().'.visible',
                'label'=>$this->getAttributeLabel('visible')],

        ];
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

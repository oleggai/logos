<?php

namespace app\models\dictionaries\currency;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\Translator;
use app\models\common\CommonModel;
use app\models\common\Langs;
//use app\controllers\LangController;

/**
 * Модель валюты
 * @author Richok FG
 * @category currency
 *
 * @property string $id
 * @property string $symbol
 * @property integer $visible
 * @property string $state
 *
 */
class Currency extends CommonModel
{

    /**
     * @var string полное название на текущем языке
     */
    public $nameFull;
    /**
     * @var string массив полных названих на всех языках
     */
    public $namesFull;
    /**
     * @var string кратное название на текущем языке
     */
    public $nameShort;
    /**
     * @var string массив кратких названий на всех языках
     */
    public $namesShort;

    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%currency}}';
    }

    public static function translateTableName()
    {
        return '{{%currency_translate}}';
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return array_merge (parent::rules(),
            [
                [['nameFullEn', 'nameFullUk', 'nameFullRu', 'nameShortEn', 'nameShortUk', 'nameShortRu'], 'required'],
                [['nameFullEn', 'nameFullUk', 'nameFullRu', 'nameShortEn', 'nameShortUk', 'nameShortRu'], 'string', 'max' => 50],
                [['nameFullEn', 'nameShortEn'], 'match', 'pattern' => '/^[\w\s\W0-9]+[^А-яҐЄЇІґєїіЪЫЁЭъыёэ!@#\$%^*?=]+$/u'],
                [['nameFullUk', 'nameShortUk'], 'match', 'pattern' => '/^[А-яҐЄЇІіґєї\s\W\[\]0-9]+[^A-zЪЫЁЭъыёэ!@#\$%\^\*\?=]+$/u'],
                [['nameFullRu', 'nameShortRu'], 'match', 'pattern' => '/^[А-яЁё\s\W\[\]0-9]+[^A-zҐЄЇІґєїі!@#\$%\^\*\?=]+$/u'],
                //[['nameFullEn', 'nameFullUk', 'nameFullRu', 'nameShortEn', 'nameShortUk', 'nameShortRu'], 'unique'],
                [['symbol', 'state'], 'required'],
                [['visible', 'state'], 'integer'],
                ['visible', 'in', 'range' => [self::VISIBLE, self::INVISIBLE]],
                [['symbol'], 'string', 'max' => 5],
            ]);
    }

    /**
     * Надписи для полей
     * @return array массив названий полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('currency', 'Id'),
            'nameFull' => Yii::t('currency', 'Name Full'),
            'nameFullEn' => Yii::t('currency', 'Name Full (Eng)'),
            'nameFullUk' => Yii::t('currency', 'Name Full (Ukr)'),
            'nameFullRu' => Yii::t('currency', 'Name Full (Rus)'),
            'nameShort' => Yii::t('currency', 'Name Short'),
            'nameShortEn' => Yii::t('currency', 'Name Short (Eng)'),
            'nameShortUk' => Yii::t('currency', 'Name Short (Ukr)'),
            'nameShortRu' => Yii::t('currency', 'Name Short (Rus)'),
            'symbol' => Yii::t('currency', 'Symbol'),
            'visible' => Yii::t('currency', 'Visible'),
            'visibilityList' => Yii::t('currency', 'Visible'),
            'visibilityText' => Yii::t('currency', 'Visible'),
            'state' => Yii::t('currency', 'State'),
            'operation' => Yii::t('app', 'Operation')

        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием новой валюты
     */
    public function generateDefaults() {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
    }


    /**
     * Получение полного названия валюты на английском
     * @return string полное название на английском
     */
    public function getNameFullEn() {
        return $this->namesFull['en'];
    }

    /**
     * Установка полного названия валюты на английском
     * @param string $value полное название на английском
     */
    public function setNameFullEn($value) {
        $this->namesFull['en'] = $value;
    }

    /**
     * Получение полного названия валюты на украинском
     * @return string полное название на украинском
     */
    public function getNameFullUk() {
        return $this->namesFull['uk'];
    }

    /**
     * Установка полного названия валюты на украинском
     * @param string $value полное название на украинском
     */
    public function setNameFullUk($value) {
        $this->namesFull['uk'] = $value;
    }

    /**
     * Получение полного названия валюты на русском
     * @return string полное название на русском
     */
    public function getNameFullRu() {
        return $this->namesFull['ru'];
    }

    /**
     * Установка полного названия валюты на русском
     * @param string $value полное название на русском
     */
    public function setNameFullRu($value) {
        $this->namesFull['ru'] = $value;
    }

    /**
     * Получение краткого названия валюты на английском
     * @return string краткое название на английском
     */
    public function getNameShortEn() {
        return $this->namesShort['en'];
    }

    /**
     * Установка краткого названия валюты на английском
     * @param string $value краткое название на английском
     */
    public function setNameShortEn($value) {
        $this->namesShort['en'] = $value;
    }

    /**
     * Получение краткого названия валюты на украинском
     * @return string краткое название на украинском
     */
    public function getNameShortUk() {
        return $this->namesShort['uk'];
    }

    /**
     * Установка краткого названия валюты на украинском
     * @param string $value краткое название на украинском
     */
    public function setNameShortUk($value) {
        $this->namesShort['uk'] = $value;
    }

    /**
     * Получение краткого названия валюты на русском
     * @return string краткое название на русском
     */
    public function getNameShortRu() {
        return $this->namesShort['ru'];
    }

    /**
     * Установка краткого названия валюты на русском
     * @param string $value краткое название на русском
     */
    public function setNameShortRu($value) {
        $this->namesShort['ru'] = $value;
    }

    /**
     * Получение валюты по уникальному идентификатору
     * @param $id string идентификатор валюты
     * @return null|Currency модель валюты
     */
    public static function getById($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Получение списка валют в виде ассоциативного массива, где ключ - id, значение - значение поля переданного параметром ('nameFull' по-умолчанию)
     * @param string $field поле для отображения
     * @patam boolean $empty первое пустое
     * @return array массив валют
     */
    public static function getList($field = 'nameFull', $empty = false) {
        $arr = Currency::find()->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Получение списка валют (в том числе удаленных и недоступных) в виде ассоциативного массива, где ключ - id, значение - значение поля переданного параметром ('nameFull' по-умолчанию)
     * @param string $field поле для отображения
     * @return array массив валют
     */
    public static function getListAll($field = 'nameFull', $empty = false) {
        $arr = Currency::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {
        // загрузка переводов полного и сокращенного названий
        $this->namesFull = Translator::getAll('{{%currency_translate}}', 'name_full', 'currency_id', $this->id);
        $this->nameFull = $this->namesFull[Yii::$app->language];
        $this->namesShort = Translator::getAll('{{%currency_translate}}', 'name_short', 'currency_id', $this->id);
        $this->nameShort = $this->namesShort[Yii::$app->language];
    }

    /**
     * Метод вызывается перед удалением сущности
     * @return bool флаг удаления
     */
    public function beforeDelete() {
        //Translator::delTranslation('{{%currency_translate}}', 'currency_id', $this->id);
        return parent::beforeDelete();
    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            // созранение всех переводов
            Translator::setAll('{{%currency_translate}}', 'currency_id', $this->id,
                ['name_full' => $this->namesFull, 'name_short' => $this->namesShort]);
        }
        $this->saveSServiceData($insert);
        $this->operation = self::OPERATION_NONE;
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'nameFull'=>$this->nameFull,
            'nameShort'=>$this->nameShort,
            'symbol'=>$this->symbol,
            'nameShortEn'=> $this->nameShortEn,
            'state'=>$this->state,
        ];

    }

    /**
     * Получение списка полей для виджета фильтрации
     * @return array массив для виджета фильтрации
     */
    public function getFilters(){

        return  [
            ['id'=>'f_currency_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'operation' => '=', 'field' => 'state'],
            ['id'=>'f_currency_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,
                'items'=>Langs::$Names, 'operation' => '=', 'field' => 'lang', 'label'=>Yii::t('app', 'Language')],
            ['id'=>'f_currency_id','operation' => '=', 'field' => 'id'],
            ['id'=>'f_currency_namefull','operation' => 'starts', 'field' => 'name_full',
                'label'=>$this->getAttributeLabel('nameFull')],
            ['id'=>'f_currency_namehort','operation' => 'starts', 'field' => 'name_short',
                'label'=>$this->getAttributeLabel('nameShort')],
            ['id'=>'f_currency_visible', 'type'=>self::FILTER_DROPDOWN, 'value'=>'',
                'items'=>$this->getVisibilityList(true), 'operation' => '=', 'field' => 'visible'],
        ];
    }


}
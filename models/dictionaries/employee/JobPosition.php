<?php

namespace app\models\dictionaries\employee;

use app\models\common\Langs;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\common\Translator;
use app\models\common\CommonModel;
use yii\helpers\Url;

/**
 * Модель должности
 * @author Richok FG
 * @category employee
 *
 * @property integer $id
 * @property string $name
 * @property string $names
 * @property integer $visible
 * @property mixed nameRu
 * @property mixed nameUk
 * @property mixed nameEn
 * @property mixed state
 * @property mixed stateStr
 * @property mixed stateList
 * @property mixed visibilityText
 * @property mixed visibilityList
 * @property mixed for_counterparty
 * @property mixed for_employee
 */
class JobPosition extends CommonModel
{

    /**
     * @var string полное название на текущем языке
     */
    public $name;
    /**
     * @var string массив полных названих на всех языках
     */
    public $names;

    /**
     * Имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName() {
        return '{{%job_position}}';
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules() {
        return array_merge(parent::rules(), [
            [['nameEn', 'nameUk', 'nameRu'], 'required'],
            [['nameEn', 'nameRu', 'nameUk'], 'validateUnique'],
            [['nameEn'], 'match', 'pattern' => '/^[\w\s\W0-9]+[^А-яҐЄЇІґєїіЪЫЁЭъыёэ!@#\$%^*?=]+$/u'],
            [['nameUk'], 'match', 'pattern' => '/^[А-яҐЄЇІіґєї\s\W\[\]0-9]+[^A-zЪЫЁЭъыёэ!@#\$%\^\*\?=]+$/u'],
            [['nameRu'], 'match', 'pattern' => '/^[А-яЁё\s\W\[\]0-9]+[^A-zҐЄЇІґєїі!@#\$%\^\*\?=]+$/u'],
            [['nameEn', 'nameUk', 'nameRu'], 'string', 'max' => 255],
            ['visible', 'in', 'range' => [self::VISIBLE, self::INVISIBLE]],
            [['for_counterparty'], 'validateForCounterparty'],
            [['for_employee'], 'integer']
        ]);
    }

    public function validateUnique($attribute, $params) {
        if($this->operation == CommonModel::OPERATION_UPDATE) {
            return true;
        }
        $lang = '';
        $value = $this->{$attribute};
        $connection = Yii::$app->db;
        $sql = 'SELECT name, lang FROM {{%job_position_translate}} WHERE name = :name';
        $command = $connection->createCommand($sql);
        $command->bindParam(':name', $value);
        $trans = $command->queryAll();
        switch($attribute) {
            case 'nameEn':
                $lang = 'en';
                break;
            case 'nameRu':
                $lang = 'ru';
                break;
            case 'nameUk':
                $lang = 'uk';
                break;
        }
        if($trans) {
            foreach($trans as $key => $val) {
                if($val['lang'] == $lang && $val['name'] == $value) {
                    $this->addError($attribute, $this->getAttributeLabel($attribute) . ' ' . Yii::t('error', 'должно быть уникальным значением'));
                }
            }
        }
        return $this->errors ? false : true;
    }

    /**
     * Надписи для полей
     * @return array массив названий полей
     */
    public function attributeLabels() {
        return [
            'name' => Yii::t('jobposition', 'Name'),
            'state' => Yii::t('jobposition', 'State'),
            'nameEn' => Yii::t('jobposition', 'Name (Eng)'),
            'nameUk' => Yii::t('jobposition', 'Name (Ukr)'),
            'nameRu' => Yii::t('jobposition', 'Name (Rus)'),
            'visible' => Yii::t('jobposition', 'Visible'),
            'visibilityText' => Yii::t('jobposition', 'Visible'),
            'operation' => Yii::t('app', 'Operation'),
            'for_counterparty' => Yii::t('jobposition', 'For counterparty'),
            'for_employee' => Yii::t('jobposition', 'For employee')
        ];
    }


    /**
     * Получение названия должности на английском
     * @return string название на английском
     */
    public function getNameEn() {
        return $this->names['en'];
    }

    /**
     * Установка названия должности на английском
     * @param string $value название на английском
     */
    public function setNameEn($value) {
        $this->names['en'] = $value;
    }

    /**
     * Получение названия должности на украинском
     * @return string название на украинском
     */
    public function getNameUk() {
        return $this->names['uk'];
    }

    /**
     * Установка названия должности на украинском
     * @param string $value название на украинском
     */
    public function setNameUk($value) {
        $this->names['uk'] = $value;
    }

    /**
     * Получение названия должности на русском
     * @return string название на русском
     */
    public function getNameRu() {
        return $this->names['ru'];
    }

    /**
     * Установка названия должности на русском
     * @param string $value название на русском
     */
    public function setNameRu($value) {
        $this->names['ru'] = $value;
    }

    /**
     * Получение должности по уникальному идентификатору
     * @param $id integer идентификатор должности
     * @return null|JobPosition модель должности
     */
    public static function getById($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Получение списка должностей в виде ассоциативного массива, где ключ - id, значение - значение поля переданного параметром ('name' по-умолчанию)
     * @param string $field поле для отображения
     * @return array массив статусов
     */
    public static function getList($field = 'name', $empty = false, $andWhere='1=1') {
        $arr = JobPosition::find()->where('visible = :visible', [':visible' => self::VISIBLE])->andWhere($andWhere)->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {
        // получение полей из таблицы перевода
        $this->names = Translator::getAll('{{%job_position_translate}}', 'name', 'job_position_id', $this->id);
        $this->name = $this->names[Yii::$app->language];
    }

    /**
     * Метод вызывается перед удалением сущности
     * @return bool флаг удаления
     */
    public function beforeDelete() {
        // удаление записей из таблицы переводов
        Translator::delTranslation('{{%job_position_translate}}', 'job_position_id', $this->id);
        return parent::beforeDelete();
    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            // созранение всех переводов
            Translator::setAll('{{%job_position_translate}}', 'job_position_id', $this->id, ['name' => $this->names]);
        }
        $this->saveSServiceData($insert);
        $this->operation = self::OPERATION_NONE;
    }

    public function getState(){
        return 1;
    }

    public function getStateStr(){
        return $this->stateList[$this->state];
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'nameRu'=>$this->nameRu,
            'nameEn'=>$this->nameEn,
            'nameUk'=>$this->nameUk,
            'visibilityText'=>$this->visibilityText,
            'state'=>$this->state,
            'stateStr'=>$this->stateStr,
            'for_counterparty'=>($this->for_counterparty == 1 ? "checked" : ""),
            'for_employee'=>($this->for_employee == 1 ? "checked" : "")
        ];

    }


    public function getFilters(){

        return  [

            ['id'=>'f_position_id', 'field' => 'id','operation' => '='],

            ['id'=>'f_position_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,'label'=>Yii::t('app','Language').':',
                'items'=>Langs::$Names, 'operation' => '=', 'field' => 'lang'],

            ['id'=>'f_position_name', 'field' => 'name','operation' => 'like'],

            ['id'=>'f_position_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'label'=>Yii::t('jobposition', 'State').':',],

            ['id'=>'f_position_visible', 'type'=>self::FILTER_DROPDOWN, 'value'=>'',
                'items'=>$this->getVisibilityList(true), 'operation' => '=', 'field' => 'visible'],

        ];
    }

    public function validateForCounterparty($attribute){
        if(($this->for_employee == 0) && ($this->for_counterparty == 0))
        //if (!DateShortStringBehavior::validate($_POST['Counterparty']['counterpartyPersDocs'][$attribute]))
            $this->addError($attribute, Yii::t('app', 'At least one checkbox must be chosen'));
    }

    /**
     * Формирование полей по-умолчанию, перед созданием новой должности сотрудника
     * @param $params
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyJobPosition($params);
    }

    /**
     * Копирование должностей сотрудников
     * @param $params
     */
    public function copyJobPosition($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $jobPosition = JobPosition::findOne(['id' => $params['id']]);
            if($jobPosition) {
                $this->attributes = $jobPosition->getAttributes();
                $this->nameRu = $jobPosition->nameRu;
                $this->nameEn = $jobPosition->nameEn;
                $this->nameUk = $jobPosition->nameUk;
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
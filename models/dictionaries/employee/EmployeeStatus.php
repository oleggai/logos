<?php

namespace app\models\dictionaries\employee;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\common\Translator;
use app\models\common\CommonModel;
use yii\helpers\Url;

/**
 * Модель статуса сотрудника\
 * @author Richok FG
 * @category employee
 *
 * @property integer $id
 * @property string $name
 * @property string $nameEn
 * @property string $nameUk
 * @property string $nameRu
 * @property array $names;
 */
class EmployeeStatus extends CommonModel
{
    /**
     * @var string name полное название на текущем языке
     */
    public $name;
    /**
     * @var string names массив полных названих на всех языках
     */
    public $names;

    /**
     * Имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName() {
        return '{{%employee_status}}';
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
            [['nameEn', 'nameUk', 'nameRu'], 'string', 'max' => 50],
        ]);
    }

    /**
     * Надписи для полей
     * @return array массив названий полей
     */
    public function attributeLabels() {
        return [
            'name' => Yii::t('employeestatus', 'Name'),
            'nameEn' => Yii::t('employeestatus', 'Name (Eng)'),
            'nameUk' => Yii::t('employeestatus', 'Name (Ukr)'),
            'nameRu' => Yii::t('employeestatus', 'Name (Rus)'),
            'operation' => Yii::t('app', 'Operation')
        ];
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyStatus($params);
    }

    public function copyStatus($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $status = EmployeeStatus::findOne(['id' => $params['id']]);
            if($status) {
                $this->attributes = $status->getAttributes();
                $this->names = $status->names;
            }
        }
    }

    public function validateUnique($attribute, $params) {
        if($this->operation == CommonModel::OPERATION_UPDATE) {
            return true;
        }
        $lang = strtolower(substr($attribute, 4));
        $value = $this->{$attribute};
        $res = (new Query())->from('{{%employee_status_translate}}')->where(['name' => $value])->all();
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
     * Получение названия статуса сотрудника на английском
     * @return string название на английском
     */
    public function getNameEn() {
        return $this->names['en'];
    }

    /**
     * Установка названия статуса сотрудника на английском
     * @param string $value название на английском
     */
    public function setNameEn($value) {
        $this->names['en'] = $value;
    }

    /**
     * Получение названия статуса сотрудника на украинском
     * @return string название на украинском
     */
    public function getNameUk() {
        return $this->names['uk'];
    }

    /**
     * Установка названия статуса сотрудника на украинском
     * @param string $value название на украинском
     */
    public function setNameUk($value) {
        $this->names['uk'] = $value;
    }

    /**
     * Получение названия статуса сотрудника на русском
     * @return string название на русском
     */
    public function getNameRu() {
        return $this->names['ru'];
    }

    /**
     * Установка названия статуса сотрудника на русском
     * @param string $value название на русском
     */
    public function setNameRu($value) {
        $this->names['ru'] = $value;
    }

    /**
     * Получение статуса сотрудника по уникальному идентификатору
     * @param $id string идентификатор статуса
     * @return null|EmployeeStatus модель статуса
     */
    public static function getById($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * Получение списка статусов сотрудника в виде ассоциативного массива, где ключ - id, значение - значение поля переданного параметром ('name' по-умолчанию)
     * @param string $field поле для отображения
     * @return array массив статусов
     */
    public static function getList($field = 'name', $empty = false) {
        $r = ArrayHelper::map(EmployeeStatus::find()->all(), 'id', $field);
        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {
        // получение полей из таблицы перевода
        $this->names = Translator::getAll('{{%employee_status_translate}}', 'name', 'employee_status_id', $this->id);
        $this->name = $this->names[Yii::$app->language];
    }

    /**
     * Метод вызывается перед удалением сущности
     * @return bool флаг удаления
     */
    public function beforeDelete() {
        Translator::delTranslation('{{%employee_status_translate}}', 'employee_status_id', $this->id);
        return parent::beforeDelete();
    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            // созранение всех переводов
            Translator::setAll('{{%employee_status_translate}}', 'employee_status_id', $this->id, ['name' => $this->names]);
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
            'id' => $this->id,
            'name' => $this->name,
            'nameEn' => $this->nameEn,
            'nameUk' => $this->nameUk,
            'nameRu' => $this->nameRu,
            'state' => $this->state,
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
<?php

/**
 * Created by PhpStorm.
 * User: goga
 * Date: 31.03.2015
 * Time: 15:48
 */

namespace app\models\common;

use app\models\dictionaries\access\User;
use app\models\common\sys\SysEntity;
use app\models\common\CommonQuery;
use ReflectionClass;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * Общая модель
 *@property mixed $operations Доступные операции над моделью
 *@property mixed $gridOperationsOptions Опции операций
 *@property mixed $gridOperations Текущая операция
 *@property mixed $operation Текущая операция
 *@property array $filters Набор простых фильтров. См. описание метода getFilters()
 *@property array $afilters Набор расширенных фильтров. См. описание метода getAFilters()
 *@property mixed visibilityList Массив видимостей
 *@property mixed stateList Массив состояний
 *@property mixed stateText Надпись состояния
 *@property mixed visibilityText Надпись видимости
 *@property mixed disableEdit
 *@property mixed state
 */
class CommonModel extends ActiveRecord {

    const CRITICAL_ATTRIBUTE = '#critical_attribute#';

    const FILTER_DATETIME = 1;
    const FILTER_DROPDOWN = 2;
    const FILTER_SELECT2 = 3;
    const FILTER_CHECKBOX = 4;
    const FILTER_MASKEDEDIT = 5;
    const FILTER_HIDDEN = 6;
    const FILTER_CHECKBOXES = 7;
    const FILTER_CHECKBOXESDROPDOWN = 8;

    /**
     * @var int статус видимый
     */
    const VISIBLE = 1;
    /**
     * @var int статус невидимый
     */
    const INVISIBLE = 0;

    /**
     * Состояние создана
     */
    const STATE_CREATED = 1;
    /*
     * Состояние закрыта
     */
    const STATE_CLOSED = 2;
    /**
     * Состояние удалена
     */
    const STATE_DELETED = 100;

    /**
     * Отсутвие операции
     */
    const OPERATION_NONE = 0;
    /**
     * Операция создания
     */
    const OPERATION_CREATE = 1;
    /**
     * Операция редактирования
     */
    const OPERATION_UPDATE = 2;
    /**
     * Операция просомтра
     */
    const OPERATION_VIEW = 3;
    /**
     * Операция начала редактирования
     */
    const OPERATION_BEGIN_UPDATE = 4;
    /**
     * Операция начала удаления
     */
    const OPERATION_BEGIN_DELETE = 5;
    /**
     * Операция начала закрытия (ЭН)
     */
    const OPERATION_BEGIN_CLOSE = 6;
    /**
    * Операция начала восстановления (после удаления)
    */
    const OPERATION_BEGIN_CANCEL = 7;

    /**
     * Операция закрытия
     */
    const OPERATION_CLOSE = 50;
    /**
     * Операция отмены
     */
    const OPERATION_CANCEL = 51;
    /**
     * Операция удаления
     */
    const OPERATION_DELETE = 100;
    /**
     * Операция копирования
     */
    const OPERATION_COPY = 200;
    /**
     * Операция создания ЭН перенаправления
     */
    const OPERATION_COPY_EW_REDIRECT = 201;
    /**
     * Операция создания ЭН возврата
     */
    const OPERATION_COPY_EW_RETURN = 202;
    /**
     * Операция создания счета-фактуры
     */
    const OPERATION_COPY_EW_RECEIPT = 203;
    /**
     * Операция создания ВТБУ
     */
    const OPERATION_COPY_EW_CUSTOMS = 204;
    /**
     * Операция создания АППГ
     */
    const OPERATION_COPY_EW_ACCEPTANCE = 205;
    const OPERATION_CHANGE_STATUS = 206;
    const OPERATION_CHANGE_NONDELIVERY = 207;
    const OPERATION_SHOW_STATUS = 208;
    const OPERATION_CREATE_MN = 209;
    /**
     * Операция просмотра журнала сущности
     */
    const OPERATION_GRIDVIEW = 1000;
    /**
     * Операция просмотра журнала сущности c возможностью выбрать 2+ сущности используя чекбоксы
     */
    const OPERATION_GRIDVIEW_MULTISELECT = 1001;
    /**
     * Операция выхода пользователя из ИС
     */
    const OPERATION_LOGOUT = 1001;
    /**
     * Размер страницы данных в журналах при постраничной загрузке данных
     */
    const DATA_PAGE_SIZE = 22;
    /**
     * Признак того, что значения не были загружены клиенту. В этом случае новые записи добавляются к существующим, старые не обрабатываются
     */
    const FIELD_WAS_NOT_LOADED = '_field_was_not_loaded_';

    protected $uniqueId;
    
    /**
     * @var int Текущая операция
     */
    private $operation = self::OPERATION_NONE;

    /**
     * Переопределяет ActiveQuery класс
     * @return CommonQuery
     */
    public static function find()
    {
        return new CommonQuery(get_called_class());
    }
    
    /**
     * Метод получения доступных операция
     */
    public function getOperations() {
        // новая запись
        if ($this->isNewRecord)
            return [];

        // состояние удалена или закрыта
        if ($this->state == self::STATE_DELETED)
            return [self::OPERATION_CANCEL => Yii::t('app', 'Restore')];

        //if ($this->operation == self::OPERATION_VIEW)
        return [
            self::OPERATION_UPDATE => Yii::t('app', 'Update'),
            self::OPERATION_DELETE => Yii::t('app', 'Delete'),
        ];

        //return [ self::OPERATION_DELETE => Yii::t('app', 'Delete'),];
    }

    /**
     * Метод получения опций операций
     */
    public function getOperationsOptions() {
        return [];
    }

    /**
     * Правила для полей
     */
    public function rules() {
        return [
            ['operation', 'integer'],
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels() {
        $modelName = strtolower(StringHelper::basename(get_class($this)));
        $s = DIRECTORY_SEPARATOR;
        $path = Yii::getAlias('@app') . $s . 'models' . $s . 'attribute-labels' . $s . $modelName . '.php';
        $attributeLabelsModel = [];
        if(file_exists($path)) {
            $attributeLabelsModel = require($path);
        }
        $common = [
            'operation' => Yii::t('app', 'Operation'),
            'stateText' => Yii::t('app', 'State'),
        ];
        return array_merge($attributeLabelsModel, $common);
    }

    /**
     * Общий мметод удаления
     * @param bool $changeState менять состояние, или физически удалять в БД
     * @return false|int
     */
    function delete($changeState = true) {

        // если существует атрибут state - запись не удаляется, обновляется этот атрибут
        if ($changeState && array_key_exists('state', $this->attributes)) {
            return $this->updateAttributes(['state' => self::STATE_DELETED]);
        }

        return parent::delete();
    }

    public function getUniqueId()
    {
        if ($this->uniqueId === null) {
            $this->uniqueId = time() . mt_rand();
        }
        
        return $this->uniqueId;
    }
    
    public function setUniqueId($uniqId) {
        $this->uniqueId = $uniqId;
    }
    
    /**
     * Получение следующего значения счетчика (сиквенс)
     * @param $counter_name string Имя счетчика
     * @return bool|null|string
     */
    function getNextCounterValue($counter_name) {
        return Yii::$app->db->createCommand("select {{%counter}}('$counter_name')")->queryScalar();
    }

    /**
     * Метод получения текущей операции
     */
    public function getOperation() {
        return $this->operation;
    }

    /**
     * Метод сохранения текущей операции
     */
    public function setOperation($value) {
        $this->operation = $value;
    }

    /**
     * Метод получения простых фильтров.
     * Для использования:
     *  - отображение : в GridWidget необходимо передать этот массив ('filters' => $model->filters,)
     *  - обработка : в контроллере добавить where в получение грида (->where($this->getFiltersWhere($model)))
     *
     * @return null|array Массив фильтров либо null для отключения.
     * Пример ['id'=>'f_ew_num', 'label'=>Yii::t('ew','Number:'), 'operation' => 'like', 'field' => 'ew_num'].
     * id уникальный идентификатор фильтра.
     * label надпись в форме фильтров.
     * operation ( > >= < <= like in ) выполняемая операция при поиске.
     * field поле фильрации в таблице.
     */
    public function getFilters() {
        return null;
    }

    /**
     * Получение списка состояний сущности
     * @return array список состояний
     */
    public function getStateList($empty = false) {
        $result = [
            self::STATE_CREATED => Yii::t('app', 'Created'),
            self::STATE_DELETED => Yii::t('app', 'Deleted'),
        ];

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    /**
     * Получение название текущей видимости
     * @return string название текущей видимости
     */
    public function getStateText() {
        $arr = $this->stateList;
        if (array_key_exists($this->state, $arr))
            return $arr[$this->state];

        return '';
    }

    /**
     * Флаг доступности контролов на форме
     * @param null $field string|null Имя поля для проверки
     * @return bool флаг доступности контролов на форме
     */
    public function getDisableEdit($field=null) {

        if ($this->operation == self::OPERATION_VIEW)
            return true;

        if ($this->operation == self::OPERATION_DELETE)
            return true;

        if ($this->operation == self::OPERATION_CANCEL)
            return true;

        return !$this->isNewRecord && array_key_exists('state', $this->attributes)
            && $this->state == self::STATE_DELETED && $this->operation != self::OPERATION_CANCEL;
    }

    public function setDisableEdit($value = true) {
        $this->disableEdit = $value;
    }

    /**
     * Выполняется перед сохранением сущности
     * @param bool $insert создание/редактирование
     * @return bool сатисфекшн
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            // проверка доступности операций
            if ($this->operation == self::OPERATION_DELETE){

                if ($this->state != self::STATE_CREATED) {
                    $this->addError('state', Yii::t('app', "Save error. Can't delete entity with state not 'Created'"));
                    return false;
                }
                else
                    $this->state = self::STATE_DELETED;
            }
            else if ($this->operation == self::OPERATION_CANCEL){

                if ($this->state == self::STATE_CREATED) {
                    $this->addError('state', Yii::t('ew', "Save error. Can't restore EW with state 'Created'"));
                    return false;
                }
                else
                    $this->state = self::STATE_CREATED;
            }


            // если это редактирование удаленной записи и операция НЕ восстановление, то ничего не делать
            //if ($this->disableEdit) {
            //    return false;
            //}
            // одновременное редактирование сущностей
            $result = SysEntity::saveOperation($this->getEntityCode(), $this->getIdentity(), $this->operation);
            if ($result) {
                $this->refresh(); // можно убрать
                $this->addError(self::CRITICAL_ATTRIBUTE, $result);
                return false;
            }

            return true;
        }
        return false;
    }

    /**
     * Получения списка видимостей
     * @return array Массив видимость => надпись
     */
    public function getVisibilityList($empty = false) {

        $r = [
            self::VISIBLE => Yii::t('app', 'Visible'),
            self::INVISIBLE => Yii::t('app', 'Invisible'),
        ];

        if ($empty)
            $r = [null => ''] + $r;

        return $r;
    }

    /**
     * Получение название текущей видимости
     * @return string название текущей видимости
     */
    public function getVisibilityText() {
        $arr = $this->getVisibilityList();
        if (array_key_exists($this->visible, $arr))
            return $arr[$this->visible];

        return '';
    }

    /**
     * Получение списка Да/Нет
     * @return array список
     */
    public function getYesNoList($empty = false) {
        $r = [
            0 => Yii::t("app", "No"),
            1 => Yii::t("app", "Yes")
        ];

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Получение название текущего значения справочника YesNoList
     * @return string название текущего значения
     */
    public function getYesNoText($val) {
        $arr = $this->getYesNoList();
        if (array_key_exists($val, $arr))
            return $arr[$val];

        return '';
    }

    /**
     * @return mixed Получить название таблицы с архивом для сущности
     */
    public function getArchTableName() {
        return str_replace('{{%', '{{%arch_', $this->tableName());
    }

    /**
     * @return mixed Получить название таблицы краткого лога
     */
    public function getSLogTableName() {
        return str_replace('{{%', '{{%log_s_', $this->tableName());
    }

    /**
     * @return mixed Получить полное название модели логирования сущности
     */
    public function getLogModelName() {
        $shortName = (new ReflectionClass($this))->getShortName();
        return str_replace($shortName, 'Log' . $shortName, $this->className());
    }

    /**
     * Получить логи
     */
    public function getLogs() {
        return $this
                        ->hasMany($this->logModelName, ['id' => 'log_row_id'])
                        ->viaTable($this->archTableName, ['id' => 'id'])->all();
    }

    /**
     * Получить лог создания
     */
    public function getLogCreation() {
        return $this
                        ->hasMany($this->logModelName, ['id' => 'log_row_id'])
                        ->where(['type' => CommonModel::OPERATION_CREATE])
                        ->viaTable($this->archTableName, ['id' => 'id'])->one();
    }

    /**
     * Получить последний лог редактирования
     */
    public function getLogLastUpdate() {
        return $this
                        ->hasMany($this->logModelName, ['id' => 'log_row_id'])
                        ->where(['type' => CommonModel::OPERATION_UPDATE])
                        ->orderBy('date DESC')
                        ->viaTable($this->archTableName, ['id' => 'id'])->one();
    }

    /**
     * Получение служебной информации
     * @return array Массив с полями служебной информации
     */
    public function getServiceData() {
        $createArray = [];
        if ($this->getLogCreation($this->logModelName, $this->archTableName))
            $createArray = $this->getLogCreation($this->logModelName, $this->archTableName)->toJson('create_');
        $lastUpdateArray = [];
        if ($this->getLogLastUpdate($this->logModelName, $this->archTableName))
            $lastUpdateArray = $this->getLogLastUpdate($this->logModelName, $this->archTableName)->toJson('lastupdate_');

        return array_merge($createArray, $lastUpdateArray, ['state' => $this->stateText]);
    }

    /**
     * Получить сервисную информацию пользователя
     * @param $id integer ид пользователя
     * @param $prefix string префикс ключа в массиве ('create_', 'lastupdate_')
     * @return array массив с сервисной информации
     */
    public function getUserServiceData($id, $prefix) {
        $out_country = '';
        $out_city = '';
        $out_surname = '';
        $out_departament = '';

        $user = User::findOne(['user_id' => $id]);

        if ($user != null && $user->employee != null) {
            $out_city = $user->employee->city;
            $out_departament = $user->employee->departament;
            $out_surname = $user->employee->surnameFull;
            if ($user->employee->country != null)
                $out_country = $user->employee->country->nameOfficial;
        }

        return [
            $prefix . 'city' => $out_city,
            $prefix . 'country' => $out_country,
            $prefix . 'departament' => $out_departament,
            $prefix . 'surname' => $out_surname,
        ];
    }

    /**
     * Получить сервисную информацию по созданию и последнему редактированию сущности
     * @return array массив с сервисной информацией
     */
    public function getSServiceData() {

        if (!Yii::$app->db->schema->getTableSchema($this->sLogTableName))
            return;


        $row = (new Query())
                ->select(['create_user_id', 'update_user_id', 'create_date', 'update_date'])
                ->from($this->sLogTableName)
                ->where(['parent_id' => $this->getAttribute($this->modelId)])
                ->one();

        $createArray = $this->getUserServiceData($row['create_user_id'], 'create_');
        $createArray['create_date'] = (new DateFormatBehavior())->convertFromStoredFormat($row['create_date']);
        $lastUpdateArray = $this->getUserServiceData($row['update_user_id'], 'lastupdate_');
        $lastUpdateArray['lastupdate_date'] = (new DateFormatBehavior())->convertFromStoredFormat($row['update_date']);

        return array_merge($createArray, $lastUpdateArray, ['state' => $this->stateText]);
    }

    /**
     * Получить уникальный идентификатор таблицы
     * @return string уникальный идентификатор таблицы
     */
    public function getModelId() {
        return 'id';
    }

    /**
     * Сохраненить сервисную информацию по созданию и/или последнему редактированию сущности
     * @param $insert bool создание/редактирование
     */
    public function saveSServiceData($insert) {

        if (!Yii::$app->db->schema->getTableSchema($this->sLogTableName))
            return;

        $command = Yii::$app->db->createCommand();
        $userId = Yii::$app->user->id;
        $now = (new \DateTime())->format(Setup::MYSQL_DATE_FORMAT);
        if ($insert || !$this->sServiceData['create_date']) {
            $command->insert($this->sLogTableName, [
                'parent_id' => $this->id,
                'create_user_id' => $userId,
                'update_user_id' => $userId,
                'create_date' => $now,
                'update_date' => $now]
            )->execute();
        } else {
            $command->update($this->sLogTableName, ['update_user_id' => $userId, 'update_date' => $now], ['parent_id' => $this->getAttribute($this->modelId)]
            )->execute();
        }
    }

    public function getGridOperations() {
        return [
            self::OPERATION_UPDATE => Yii::t('app', 'Update'),
            self::OPERATION_VIEW => Yii::t('app', 'View'),
            self::OPERATION_DELETE => Yii::t('app', 'Delete'),
            self::OPERATION_CANCEL => Yii::t('app', 'Restore'),
        ];
    }

    public function getGridOperationsOptions() {
        return [
            self::OPERATION_VIEW => ['url' => Url::to(['view']), 'name_for_tab' => Yii::t('tab_title', 'view_command')],
            self::OPERATION_UPDATE => ['url' => Url::to(['update']), 'state_depend' => [self::STATE_CREATED], 'name_for_tab' => Yii::t('tab_title', 'edit_command')],
            self::OPERATION_DELETE => ['url' => Url::to(['delete']), 'state_depend' => [self::STATE_CREATED], 'name_for_tab' => Yii::t('tab_title', 'delete_command')],
            self::OPERATION_CANCEL => ['url' => Url::to(['restore']), 'state_depend' => [self::STATE_DELETED, self::STATE_CLOSED], 'name_for_tab' => Yii::t('tab_title', 'restore_command')],
        ];
    }

    public function afterFind() {

        parent::afterFind();
        $this->operation = self::OPERATION_UPDATE;
    }

    public function init() {

        parent::init();

        if (array_key_exists('state', $this->attributes))
            $this->state = self::STATE_CREATED;
    }

    public function getIdentity() {

        if (array_key_exists('id', $this->attributes))
            return $this->id;

        return 0;
    }

    public function setIdentity($val) {

        if (array_key_exists('id', $this->attributes))
            $this->id = $val;
    }

    public function getEntityCode() {

        $shortName = (new ReflectionClass($this))->getShortName();
        if ($shortName != 'CommonModel')
            return $shortName;

        return '';
    }
    
    public static function getBooleans($empty = false, $lang = null){
        
        if (!$lang)
            $lang = Yii::$app->language;

        $languages = [
            'en' => [
                '1' => 'True',
                '0' => 'False',
            ],
            'ru' => [
                '1' => 'Да',
                '0' => 'Нет',
            ],
            'uk' => [
                '1' => 'Так',
                '0' => 'Нi',
            ],
        ];
        $result = $languages[$lang];

        if ($empty)
            $result = [null => ''] + $result;
        
        return $result;
    }

    /** Прототип функции получения ассоциативного массива id:field модели
     * 
     * @param string $field имя поля
     * @param boolean $empty первое пустое
     * @param string $lang язык списка
     * @param string $andWhere доп. фильтры запроса
     * @return array
     */
    public static function getList($field = 'name', $empty = false, $lang = null, $andWhere = null) {

        // если параметры в обратной последовательности (getList(true))
        if (gettype($field) == "boolean" && empty($empty)) {
            $empty2 = $field;
            $field2 = 'name';
        } 
        // или getList(true,'nameShort')
        elseif (!empty($empty) && gettype($empty) == "string") {
            $empty2 = $field;
            $field2 = $empty;
        }
        if ($empty2 || $field2) {
            $empty = $empty2;
            $field = $field2;
        }
        if (!$lang)
            $lang = Yii::$app->language;
        if(!$andWhere)
            $andWhere = 'visible = '.  CommonModel::VISIBLE.' AND state != '.CommonModel::STATE_DELETED;
        $models = self::find()
//                ->where('visible = ' . self::VISIBLE . ' AND state != ' . CommonModel::STATE_DELETED)
                ->andWhere($andWhere)
                ->all();
        if ($field == 'name') {
            $field = $field . '_' . $lang;
        }
        $result = ArrayHelper::map($models, 'id', $field);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }

    /*
     * Получения ассоциативного массива id:field модели с учетом отображения текущего значения поля, которое
     * может быть невидимым и/или удалённым
     * @param string $currentIdList список id справочника необходимых к отображению вне зависимости от состояния полей
     * видимости и статуса (избранные ранее, но впоследствии удаленные или обозначенные невидимыми в справочнике)
     */
    public static function getActualList($currentIdList, $field = 'name', $empty = false, $lang = null)
    {
        if((!$currentIdList)||(StringHelper::byteLength($currentIdList) < 1))
            return self::getList($field, $empty, $lang);
        else
            $Condition = '(visible = '.  CommonModel::VISIBLE.' AND state != '.CommonModel::STATE_DELETED.') OR id in ('.$currentIdList.')';
        return self::getList($field, $empty, $lang, $Condition);
    }

    /**
     * Процедура преобразования массива в строку с разделителем
     * @param $res
     * @param string $delimiter
     * @return string
     */

    public static function array2string($res, $delimiter = ',') {
        $res = array_values($res);
        $res = array_unique($res);
        $result = '';
        foreach($res as $key=>$value)
        {
            if($result == '')
                $result = $value;
            else
                $result = $result.$delimiter.$value;
        }
        return $result;
    }
        /**
     * Протометод получения названия
     * @param string $lang en|ru|uk по умолчанию текущий язык
     * @return string
     */
    public function getName($lang = '') {
        if (empty($lang))
            $lang = Yii::$app->language;
        return $this->getAttribute('name_' . $lang);
    }

    /*
     * Получение расширенных фильтров
     * @return array filters
     */

    public function getAFilters() {
        return [];
    }
    
    /**
     * Функция вывода содержимого файла переводов элементами джаваскриптового массива
     * Для использования: tr('My message') в скриптах
     * @param type $entity Название темы = 'app'
     * @param type $lang Язык темы или текущий язык приложения
     * @return string '$from': '$to', ...
     */
    public static function getTranslations($entity = 'app', $lang = '', $values = []) {
        if (empty($lang))
            $lang = Yii::$app->language;
        $file = "../messages/$lang/$entity.php";
        if (!file_exists($file))
            return '';
        $translations = require($file);
        $t = '';
        foreach ($translations as $from => $to) {
            if (empty($values) || in_array($from, $values))
                $t.="'$from':'$to',";
        }
        return $t;
    }

    /**
     * Получение данных для постраничной загрузки
     * @param $data_source object
     * @return array
     */
    public static function getDataWithLimits($data_source, $to_json = true) {

        $sort = null;
        $limit = self::DATA_PAGE_SIZE;
        $offset = 0;
        $getParams = Yii::$app->request->get();
        $data = [];

        if ($getParams['start'])
            $offset = $getParams['start'];
        if (isset($getParams['count']))
            $limit = $getParams['count'] ?: null;
        if ($getParams['sort']) {
            $sort = $getParams['sort'];
            foreach ($sort as $k=>$v)
                $sort[$k] = $v == 'asc' ? SORT_ASC : SORT_DESC;
        }

        // выборка из запроса моделей
        if (is_a ($data_source,'yii\db\ActiveQuery')){
            $total = $data_source->count();

            if ($sort)
                $models = $data_source->limit($limit)->offset($offset)->orderBy($sort)->all();
            else
                $models = $data_source->limit($limit)->offset($offset)->all();

            foreach ($models as $model)
                $data[] = $to_json ? $model->toJson() : $model;
        }

        // получение данных из массива
        elseif (is_array($data_source)) {
            $data = array_slice($data_source, $offset, $limit);
            $total = sizeof($data_source);
        }

        // выборка из общего запроса
        else {
            $total = $data_source->count();
            if ($sort)
                $data = $data_source->limit($limit)->offset($offset)->orderBy($sort)->all();
            else
                $data = $data_source->limit($limit)->offset($offset)->all();
        }

        return [
            "data" => $data,
            "pos" =>$offset,
            "total_count" => $total
        ] ;
    }


}

<?php

/**
 * В файле описан класс модели пользователя
 *
 * @author Мельник И.А.
 * @category Доступ
 */

namespace app\models\dictionaries\access;

use app\models\common\CommonModel;
use app\models\dictionaries\employee\Employee;
use Yii;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\IdentityInterface;

/**
 * Модель пользователя
 *
 * @property string $user_id Идентификатор
 * @property string $login_name Имя для входа
 * @property string $pass Пароль
 * @property integer $status Статус
 * @property string $employee_id Ссылка на сотрудника
 *
 * @property UserRight[] $userRights Права пользователя
 * @property UserRole[] $userRoles Роли пользователя
 * @property Employee $employee Модель сотрудника
 *
 * @property mixed userRightsArray Права пользователя в виде массива
 * @property mixed userRolesArray Роли пользователя в виде массива
 * @property mixed statusList Список статусов пользователя

 */
class User extends CommonModel implements IdentityInterface
{
    /**
     * Статус удален
     */
    const STATUS_DELETED = 10;
    /**
     * Статус активный
     */
    const STATUS_ACTIVE = 0;
    public $userRightsArrayInput;
    public $userRolesArrayInput;

    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%users}}';

    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['status', 'employee_id'], 'integer'],
            ['login_name', 'string', 'max' => 60],
            ['pass', 'string', 'max' => 128],
            ['login_name', 'unique'],
            [['login_name','pass'], 'required'],
            ['userRolesNames', 'string'],
            ['userRightsNames', 'string'],
            ['userRightsArray', 'safe'],
            ['userRolesArray', 'safe'],
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('user','ID'),
            'login_name' => Yii::t('user','Name'),
            'pass' => Yii::t('user','Password'),
            'status' => Yii::t('user','Status'),
            'statusText' => Yii::t('user','Status'),
            'userRolesArray' => Yii::t('app','Roles'),
            'userRightsArray' => Yii::t('app','Rights'),
            'userRolesNames' => Yii::t('user','Roles (list)'),
            'userRightsNames' => Yii::t('user','Rights (list)'),
            'employee_id' => Yii::t('user', 'Employee'),
            'employee_name' => Yii::t('user', 'Employee name'),
        ];
    }

    /**
     * Метод получения списка прав
     */
    public function getUserRights()
    {
        return $this->hasMany(UserRight::className(), ['user_id' => 'user_id']);
    }

    /**
     * Метод получения прав в виде массива
     */
    function getUserRightsArray(){
        return ArrayHelper::map($this->userRights, 'right_id', 'right_id');
    }

    /**
     * Сохранение прав пользователя
     */
    function setUserRightsArray($values){

        $this->userRightsArrayInput = $values;
    }

    function saveUserRightsArray(){

        // удаление старых прав
        foreach ($this->userRights as $userRight)
            $userRight->delete();

        // привязка новых прав
        if (is_array($this->userRightsArrayInput))
            foreach ($this->userRightsArrayInput as $value){
                $newRight = new UserRight();
                $newRight->right_id = $value;
                $newRight->user_id = $this->user_id;
                $newRight->save();
            }
    }


/**
     * Получение прав пользователя в виде строки разделенной запятыми
     */
    function getUserRightsNames(){

        $result = array();
        foreach ($this->userRights as $userRight)
            $result[] = $userRight->right->right_name;
        return implode(", ", $result);
    }

    /**
     * Получение списка ролей
     */
    public function getUserRoles()
    {
        return $this->hasMany(UserRole::className(), ['user_id' => 'user_id']);
    }

    /**
     * Получение ролей пользователя в виде массива
     */
    function getUserRolesArray(){
        return ArrayHelper::map($this->userRoles, 'role_id', 'role_id');
    }

    /**
     * Сохранение ролей пользователя
     */
    function setUserRolesArray($values){

        $this->userRolesArrayInput = $values;
    }

    function saveUserRolesArray(){

        // удаление старых
        foreach ($this->userRoles as $userRole)
            $userRole->delete();

        // добавление новых
        if (is_array($this->userRolesArrayInput))
            foreach ($this->userRolesArrayInput as $value){
                $newRole = new UserRole();
                $newRole ->role_id = $value;
                $newRole ->user_id = $this->user_id;
                $newRole ->save();
            }

    }


/**
     * Получение ролей ползователя в виде строки разделенной запятыми
     */
    function getUserRolesNames(){

        $result = array();
        foreach ($this->userRoles as $userRole)
            $result[] = $userRole->role->role_name;

        return implode(", ", $result);
    }


    /**
     * Поиск активного пользователя по идентификатору
     * @param int|string $id
     * @return null|User|static
     */
    public static function findIdentity($id)
    {
        return static::findOne(['user_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Поиск по токену, не используется
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Получение первичного ключа
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Получение ключа авторизации (пароль)
     */
    public function getAuthKey()
    {
        return $this->pass;
    }

    /**
     * Проверка пароля
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Поиск активного польозвателя по имени
     */
    public static function findByUsername($username)
    {
        return static::findOne(['login_name' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Проверка пароля
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->pass);
    }

    /**
     * Установка пароля (в виде хеша)
     */
    public function setPassword($password)
    {
        $this->pass = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Метод перед сохраением модели
     */
    public function beforeSave($insert){

        if (parent::beforeSave($insert)) {

            // хеширование пароля при добавлении или при изменении пароля
            $passChanged = isset($this->oldAttributes['pass']) && $this->oldAttributes['pass']!=$this->attributes['pass'];
            if ($insert || $passChanged)
                $this->setPassword($this->pass);

            return true;
        }

        return false;
    }

    /**
     * Получение списка доступных статусов пользователя
     */
    public function getStatusList() {
        return
            [
                self::STATUS_DELETED=>Yii::t('user','Disable'),
                self::STATUS_ACTIVE=>Yii::t('user','Normal'),
            ];
    }

    /**
     * Получение текущего статуса в виде текста
     */
    public function getStatusText(){
        return $this->getStatusList()[$this->status];
    }

    /**
     * Проверка прав пользователя
     * @throws ForbiddenHttpException
     */
    public function checkAccess($entity, $action){

        if ($entity=='_none')
            return true;

        // получение таблицы, пока тоже самое что и index
        if ($action == 'get-table')
            $action  = 'index';

        // с user_id=1 суперпользователь
        if ($this->user_id==1)
            return true;

        // право есть в привязанных к пользователю
        foreach ($this->userRights as $userRight)
            if ($userRight->right->check($entity,$action))
                return true;


        // право есть в привязанных к пользователю роли
        foreach ($this->userRoles as $userRole)
        foreach ($userRole->role->roleRights as $roleRight)
            if ($roleRight->right->check($entity,$action))
                return true;

        // право не найдено
        return false;
    }

    /**
     * Получение сотрудника
     */
    public function getEmployee(){
        return $this->hasOne(Employee::className(), ['id' => 'employee_id']);
    }

    /**
     * Получение имени сотрудника
     */
    public function getEmployeeName(){
        $e = $this->employee;
        if ($e!=null)
            return $e->surnameFull;

        return '';
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){

        return [
            'id'            => $this->user_id,
            'login_name'    => $this->login_name,
            'status'        => $this->getStatusText(),
            'roles'         => $this->getUserRolesNames(),
            'rights'        => $this->getUserRightsNames(),
            'employee_name' => $this->employee->name,
            'state'        => self::STATE_CREATED,
        ];

    }

    /**
     * Получение состояние пользователя. Сейчас всегда состояние "Сознад"
     * @return int
     */
    public function getState(){
        return self::STATE_CREATED;
    }

    /**
     * Получение значения идентифицирующего сущность
     * @return string
     */
    public function getIdentity(){
        return $this->user_id;
    }

    /**
     * Метод после сохранения пользователя
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert,$changedAttributes);
        $this->saveUserRightsArray();
        $this->saveUserRolesArray();
    }
}


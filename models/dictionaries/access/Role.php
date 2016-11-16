<?php
/**
 * В файле описан класс модели роли
 *
 * @author Мельник И.А.
 * @category Доступ
 */

namespace app\models\dictionaries\access;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель роли
 *
 * @property string $id Идентификатор
 * @property string $role_name Имя роли
 *
 * @property RoleRight[] $roleRights Права роли
 * @property UserRole[] $roleUsers Пользователи роли
 *
 * @property mixed roleRightsArray Права роли в виде массива
 */
class Role extends CommonModel
{
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%roles}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            [['role_name'], 'required'],
            [['role_name'], 'string', 'max' => 255],
            [['role_name'], 'unique'],
            ['roleRightsNames', 'string'],
            ['roleRightsArray', 'safe'],
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('role','ID'),
            'role_name' => Yii::t('role','Name'),
            'roleRightsArray' => Yii::t('role','Rights'),
            'roleRightsNames' => Yii::t('role','Rights (list)'),
        ];
    }

    /**
     * Метод получения прав роли
     */
    public function getRoleRights()
    {
        return $this->hasMany(RoleRight::className(), ['role_id' => 'id']);
    }

    /**
     * Метод сохранения прав роли
     * @param $values array Массив прав
     * @throws \Exception
     */
    function setRoleRightsArray($values){

        // удаление старых
        foreach ($this->roleRights as $roleRight)
            $roleRight->delete();

        // привязка новых
        if (is_array($values))
            foreach ($values as $value){
                $newRight = new RoleRight();
                $newRight->right_id = $value;
                $newRight->role_id = $this->id;
                $newRight->save();
            }
    }

    /**
     * Получение списка имен прав, привязанных к роли
     * @return string
     */
    function getRoleRightsNames(){

        $result = array();
        foreach ($this->roleRights as $roleRight)
            $result[] = $roleRight->right->right_name;
        return implode(", ", $result);
    }

    /**
     * Получение списка прав, привязанных к роли
     * @return string
     */
    function getRoleRightsArray(){

        $result = array();
        foreach ($this->roleRights as $roleRight)
            $result[] = $roleRight->right->id;
        return $result;
    }

    /**
     * Получение пользователей роли
     */
    public function getRoleUsers()
    {
        return $this->hasMany(UserRight::className(), ['role_id' => 'id']);
    }

    /**
     * Получение списка всех ролей
     * @param string $field
     * @return array
     */
    static function getList($field = 'role_name'){
        return ArrayHelper::map(Role::find()->all(), 'id', $field);
    }
    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение

     */
    public function toJson(){

        return [
            'id'           => $this->id,
            'role_name'    => $this->role_name,
            'rights_names' => $this->getRoleRightsNames(),
            'state'        => self::STATE_CREATED,
        ];

    }
}

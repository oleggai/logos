<?php

/**
 * В файле описан класс вспомогательная модели для связи пользователь-роль
 *
 * @author Мельник И.А.
 * @category Доступ
 */

namespace app\models\dictionaries\access;

use app\models\common\CommonModel;
use Yii;

/**
 * Вспомогательная модель для связи пользователь-роль
 *
 * @property string $user_id Номер пользователя
 * @property string $role_id Номер роли
 *
 * @property User $user Модель пользователя
 * @property Role $role Модель роли
 */
class UserRole extends CommonModel
{
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%user_role}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            [['user_id', 'role_id'], 'required'],
            [['user_id', 'role_id'], 'integer']
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('user','ID'),
            'role_id' => Yii::t('role','ID'),
        ];
    }

    /**
     * Метод получения модели пользователя
     */
    public function getUser(){
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * Метод получения модели роли
     */
    public function getRole(){
        return $this->hasOne(Role::className(), ['id' => 'role_id']);
    }

    /**
     * Получение первичного ключа
     */
    public static function primaryKey(){
        return array('role_id', 'user_id');
    }

    /**
     * Получение значения идентифицирующего сущность
     * @return string
     */
    public function getIdentity(){
        return 0;
    }
}

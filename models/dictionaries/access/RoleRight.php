<?php
/**
 * В файле описан класс вспомогательной модели для связи роль-право
 *
 * @author Мельник И.А.
 * @category Доступ
 */

namespace app\models\dictionaries\access;

use app\models\common\CommonModel;
use Yii;

/**
 * Вспомогательная модель для связи роль-право
 *
 * @property string $role_id Номер роли
 * @property string $right_id Номер права
 *
 * @property Role $role Модель роли
 * @property Right $right Модель права
 */
class RoleRight extends CommonModel
{
    /**
     * Название таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%role_right}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            [['role_id', 'right_id'], 'required'],
            [['role_id', 'right_id'], 'integer']
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'role_id' => Yii::t('role','ID'),
            'right_id' => Yii::t('right','ID'),
        ];
    }

    /**
     * Метод получения модели роли
     */
    public function getRole()
    {
        return $this->hasOne(Role::className(), ['id' => 'role_id']);
    }

    /**
     * Метод получения модели права
     */
    public function getRight()
    {
        return $this->hasOne(Right::className(), ['id' => 'right_id']);
    }

    /**
     * Первичный ключ
     */
    public static function primaryKey(){
        return array('right_id', 'role_id');
    }
}

<?php

/**
 * В файле описан класс вспомогательной модели для связки пользователь-право
 *
 * @author Мельник И.А.
 * @category Доступ
 */

namespace app\models\dictionaries\access;

use app\models\common\CommonModel;
use Yii;

/**
 * Вспомогательная модель для связки пользователь-право
 *
 * @property string $user_id Номер пользователя
 * @property string $right_id Номер права
 *
 * @property User $user Модель пользователя
 * @property Right $right Модель права
 */
class UserRight extends CommonModel
{
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%user_right}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            [['user_id', 'right_id'], 'required'],
            [['user_id', 'right_id'], 'integer']
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('user','ID'),
            'right_id' => Yii::t('right','ID'),
        ];
    }

    /**
     * Метод получения модели пользователя
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * Метод получения модели права
     */
    public function getRight()
    {
        return $this->hasOne(Right::className(), ['id' => 'right_id']);
    }

    /**
     * Метод получения первичного ключа
     */
    public static function primaryKey(){
        return array('right_id', 'user_id');
    }

    /**
     * Получение значения идентифицирующего сущность
     * @return string
     */
    public function getIdentity(){
        return 0;
    }
}

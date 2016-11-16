<?php
/**
 * В файле описан класс модели права
 *
 * @author Мельник И.А.
 * @category Доступ
 */

namespace app\models\dictionaries\access;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Модель для прав
 *
 * @property string $id Идентификатор
 * @property string $right_name Имя права
 * @property string $right_entity Сущность для проверки
 * @property string $right_action Дейсвие для проверки
 *
 * @property RoleRight[] $roleRights Массив ролей связанных с правом
 * @property UserRight[] $userRights Массив пользователей связанных с правом
 */
class Right extends CommonModel
{
    /**
     * Получение имени таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%right}}';
    }

    /**
     * Правила для полей модели
     */
    public function rules()
    {
        return [
            [['right_name'], 'required'], // необходимое поле
            [['right_name', 'right_entity', 'right_action'], 'string', 'max' => 255], // ограничение на размер
            [['right_name'], 'unique'] // уникальное поле
        ];
    }

    /**
     * Надписи полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('right','ID'),
            'right_name' => Yii::t('right','Name'),
            'right_entity' => Yii::t('right','Right Entity'),
            'right_action' => Yii::t('right','Right Action'),
        ];
    }

    /**
     * Метод получения полей связанных с правом
     */
    public function getRoleRights()
    {
        return $this->hasMany(RoleRight::className(), ['right_id' => 'id']);
    }

    /**
     * Метод получения пользователей связанных с правом
     */
    public function getUserRights()
    {
        return $this->hasMany(UserRight::className(), ['right_id' => 'id']);
    }

    /**
     * Метод проверки права
     * @param $entity string Сущность для проверки
     * @param $action string Дейсвие
     * @return bool Результать
     */
    public function check($entity,$action){
        return $this->right_entity == $entity && $this->right_action == $action;
    }

    /**
     * Получение всех прав в виде массива
     * @param string $field Поле для отображения
     * @return array
     */
    static function getList($field='right_name'){
        return ArrayHelper::map(Right::find()->all(), 'id', $field);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){

        return [
            'id'         => $this->id,
            'right_name' => $this->right_name,
            'state'        => self::STATE_CREATED,
        ];

    }
}

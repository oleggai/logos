<?php

namespace app\models\dictionaries\country;

use Yii;
use app\models\common\CommonModel;
use app\models\dictionaries\access\User;
use app\models\common\DateFormatBehavior;

/**
 * Модель лога операций над страной
 * @author Richok FG
 * @category country
 *
 * @property string $id Идентификатор записи
 * @property integer $type Тип операции (1 - создание, 2 - изменение)
 * @property string $date Дата записи
 * @property string $user_id Идетификатор пользователя
 * @property User $user Пользователь
 * @property mixed _date
 * @property mixed typeStr
 */
class LogCountry extends CommonModel
{
    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%log_country}}';
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return [
            [['type', 'user_id'], 'required'],
            [['type', 'user_id'], 'integer'],
            [['_date'], 'safe']
        ];
    }

    /**
     * * Поведения
     * @return array Массив поведений
     */
    function behaviors()
    {
        return [
            [
                'class' => DateFormatBehavior::className(),
                'attributes' => ['_date' => 'date']
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('country', 'ID'),
            'type' => Yii::t('country', 'Operation (1-insert, 2-update, 3-delete)'),
            'date' => Yii::t('country', 'Date'),
            'user_id' => Yii::t('country', 'User'),
        ];
    }

    public function getTypesList() {

        return [
            null => '',
            self::OPERATION_CREATE => Yii::t('app', 'Create'),
            self::OPERATION_UPDATE => Yii::t('app', 'Update'),
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getTypeStr(){
        return self::getTypesList()[$this->type];
    }

    public function toJson($prefix = '') {
        $out_country = '';
        $out_city = '';
        $out_surname = '';
        $out_departament = '';

        $user = $this->user;
        if ($user != null && $user->employee != null){
            $out_city = $user->employee->city;
            $out_departament = $user->employee->departament;
            $out_surname = $user->employee->surnameFull;
            if ($user->employee->country != null)
                $out_country = $user->employee->country->nameOfficial;
        }

        return [
            $prefix.'name' => $this->typeStr,
            $prefix.'state' => 1,
            $prefix.'date' => $this->_date,
            $prefix.'city' => $out_city,
            $prefix.'country' => $out_country,
            $prefix.'departament' => $out_departament,
            $prefix.'surname' => $out_surname,
        ];
    }
}

<?php

namespace app\models\dictionaries\events;

use app\models\dictionaries\access\User;
use app\models\common\CommonModel;
use app\models\common\DateFormatBehavior;
use app\models\manifest\Manifest;
use Yii;

/**
 * Модель событий манифеста
 *
 * @property string $id
 * @property string $mn_id
 * @property string $event_id
 * @property string $date
 * @property string $creator_user_id
 *
 * @property Manifest $mn
 * @property ListEvents $event
 * @property User $creatorUser
 */
class MnHistoryEvents extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mn_history_events}}';
    }

    public static function getEventPrefix(){
        return "MN-";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mn_id', 'event_id'], 'required'],
            [['mn_id', 'event_id', 'creator_user_id'], 'integer'],
            [['date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('events', 'ID'),
            'mn_id' => Yii::t('events', 'Mn ID'),
            'event_id' => Yii::t('events', 'Event ID'),
            'date' => Yii::t('events', 'Date'),
            'creator_user_id' => Yii::t('events', 'Creator User ID'),
            'code' => Yii::t('events', 'Code'),
            'name' => Yii::t('events', 'Name'),
            'country' => Yii::t('app', 'Country'),
            'city' => Yii::t('app', 'City'),
            'departament' => Yii::t('app', 'Department'),
            'surname' => Yii::t('events', 'Surname')
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
                'attributes' => [
                    '_date' => 'date',
                ]
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMn()
    {
        return $this->hasOne(Manifest::className(), ['id' => 'mn_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEvent()
    {
        return $this->hasOne(ListEvents::className(), ['id' => 'event_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatorUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'creator_user_id']);
    }

    public function toJson($prefix=''){

        $out_country = '';
        $out_city = '';
        $out_surname = '';
        $out_departament = '';

        $user = $this->creatorUser;
        if ($user!=null && $user->employee!=null){
            $out_city = $user->employee->city;
            $out_departament = $user->employee->departament;
            $out_surname = $user->employee->surnameFull;
            if ($user->employee->country!=null)
                $out_country = $user->employee->country->nameOfficial;
        }

        return [
            $prefix.'id'=>$this->id,
            $prefix.'code'=>$this->event->code,
            $prefix.'name'=>$this->event->name,
            $prefix.'state'=>'1',
            $prefix.'date'=>$this->_date,
            $prefix.'city'=>$out_city,
            $prefix.'country'=>$out_country,
            $prefix.'departament'=>$out_departament,
            $prefix.'surname'=>$out_surname,
        ];
    }

    public static  function getAvalableEvents(){
        return ListEvents::getList('code','name',self::getEventPrefix());
    }
}

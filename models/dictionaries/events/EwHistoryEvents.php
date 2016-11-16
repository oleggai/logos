<?php

namespace app\models\dictionaries\events;

use Yii;
use app\models\dictionaries\access\User;
use app\models\common\CommonModel;
use app\models\common\DateFormatBehavior;
use app\models\ew\EwHistoryStatuses;
use app\models\ew\ExpressWaybill;
use app\models\ew\ListStatusesEw;

/**
 * Модель событий накладной
 *
 * @property string $id Код записи
 * @property string $ew_id Ссылка на накладную
 * @property string $event_id Ссылка на справочник событий
 * @property string $date Дата события
 * @property string $creator_user_id Код пользователя создавшего запись
 *
 * @property ExpressWaybill $ew Модель накладной
 * @property ListEvents $event Модель события
 * @property User $creatorUser Модель пользователя
 */
class EwHistoryEvents extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ew_history_events}}';
    }

    public static function getEventPrefix(){
        return "EW-";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ew_id', 'event_id'], 'required'],
            [['ew_id', 'event_id', 'creator_user_id'], 'integer'],
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
            'ew_id' => Yii::t('events', 'Ew ID'),
            'event_id' => Yii::t('events', 'Event ID'),
            'date' => Yii::t('events', 'Date'),
            'creator_user_id' => Yii::t('events', 'Creator User ID'),
            'code' => Yii::t('events', 'Code'),
            'name' => Yii::t('events' ,'Name'),
            'country' => Yii::t('events', 'Country'),
            'city' => Yii::t('events', 'City'),
            'departament' => Yii::t('events', 'Department'),
            'surname' => Yii::t('events', 'Surname'),

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
     * Метод получения модели накладной
     * @return ExpressWaybill Модель накладной
     */
    public function getEw()
    {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    /**
     * Метод получения модели события
     * @return ListEvents Модель события
     */
    public function getEvent()
    {
        return $this->hasOne(ListEvents::className(), ['id' => 'event_id']);
    }

    /**
     * Метод получения модели пользователя
     * @return User Модель пользователя
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
            $out_surname = $user->employee->surnameShort;
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

    public function afterSave($insert, $changedAttributes) {

        $status = new EwHistoryStatuses();
        $status->ew_id = $this->ew_id;
        $status->creator_user_id = Yii::$app->user->id;
        //$status->comment = '';

        $statusCode = null;
        if ($this->event->code == Event::EW_CREATE)
            $statusCode = EwHistoryStatuses::ON_CREATE_EW_STATUS;
        else if ($this->event->code == Event::EW_LINK_MN)
            $statusCode = EwHistoryStatuses::ON_LINK_EW_STATUS;
        else if ($this->event->code == Event::EW_CLOSE) // ЭН выдана на ЦСС
            $statusCode = EwHistoryStatuses::ON_CLOSE_EW_STATUS;

        $statusType = ListStatusesEw::findOne(['code' => $statusCode]);
        if ($statusType != null) {
            $status->status_ew_id = $statusType->id;
            $status->status_country = $status->creatorUser->employee->country_id;
            //$status->date = (new \DateTime())->format(Setup::MYSQL_DATE_FORMAT);
            $status->save();
        }
    }
}

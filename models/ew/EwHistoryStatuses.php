<?php

namespace app\models\ew;

use app\models\dictionaries\access\User;
use app\models\common\DateFormatBehavior;
use app\models\dictionaries\country\Country;
use app\models\common\CommonModel;
use Yii;
use yii\helpers\Url;

/**
 * Модель статусов трекинга
 * @author Richok FG
 * @category ew
 *
 * @property string $id
 * @property string $ew_id
 * @property string $status_ew_id
 * @property string $date
 * @property string $creator_user_id
 * @property string $comment
 * @property string $status_country
 *
 * @property ExpressWaybill $ew
 * @property ListStatusesEw $statusEw
 * @property User $creatorUser
 * @property Country $countryModel
 */
class EwHistoryStatuses extends CommonModel
{
    /**
     * код статуса текинга при создании ЭН
     */
    const ON_CREATE_EW_STATUS = '1-0';
    /*
     * код статуса трекинга при привязке ЭН к МН
     */
    const ON_LINK_EW_STATUS = '10-1';
    /*
     * код статуса трекинга при закрытии ЭН
     */
    const ON_CLOSE_EW_STATUS = '3-1';
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%ew_history_statuses}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            //[['ew_id', 'status_ew_id'], 'required'],
            [['status_ew_id'], 'required'],
            [['ew_id', 'status_ew_id', 'creator_user_id'], 'integer'],
            [['_date'], 'validateDate'],
            [['comment'], 'string', 'max' => 500],
            ['status_country', 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'ew_id' => Yii::t('app', 'Ew ID'),
            'status_ew_id' => Yii::t('app', 'Status Ew ID'),
            'date' => Yii::t('app', 'Date'),
            '_date' => Yii::t('app', 'Date'),
            'creator_user_id' => Yii::t('app', 'Creator User ID'),
            'comment' => Yii::t('app', 'Comment'),
            'status_country' => Yii::t('app', 'Status Country'),
            'country_select_entity' => Yii::t('app', 'Status Country'),
            'status_code' => Yii::t('events', 'Status Code'),
            'status_name' => Yii::t('events', 'Status Name'),
            'country' => Yii::t('events', 'Country'),
            'city' => Yii::t('events', 'City'),
            'department' => Yii::t('events', 'Department'),
            'user' => Yii::t('events', 'User'),
            'status_type_str' => Yii::t('events', 'Status Type'),
            'inner_status_str' => Yii::t('events', 'Inner Status'),
        ];
    }

    /**
     * Поведения
     */
    function behaviors() {
        return [[
            'class' => DateFormatBehavior::className(),
            'attributes' => ['_date' => 'date']
        ]];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEw() {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatusEw() {
        return $this->hasOne(ListStatusesEw::className(), ['id' => 'status_ew_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatorUser() {
        return $this->hasOne(User::className(), ['user_id' => 'creator_user_id']);
    }

 
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountryModel() {
        return $this->hasOne(\app\models\dictionaries\country\Country::className(), ['id' => 'status_country']);
    }

    public function validateDate($attribute, $params) {
        if (!DateFormatBehavior::validate($this->$attribute))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля => значение
     */
    public function toJson() {
        $city = '';
        $surname = '';
        $department = '';
        $country = '';

        if ($this->creatorUser != null && $this->creatorUser->employee != null) {
            $city = $this->creatorUser->employee->city;
            $surname = $this->creatorUser->employee->surnameFull;
            $department = $this->creatorUser->employee->departament;
            if ($this->creatorUser->employee->country != null) {
                $country = $this->creatorUser->employee->country->nameOfficial;
            }
        }
        
        if ($this->countryModel !== null) {
            $this->countryModel->setUniqueId($this->getUniqueId());
            $this->countryModel->setOperation($this->getOperation());
        }
        
        return [
            'id' => $this->id,
            'ew_id' => $this->ew_id,
            'status_ew_id' => $this->status_ew_id,
            'status_code' => $this->statusEw->code,
            //'status_name' => $this->statusEw->nameFull,
            'status_name' => $this->statusEw->id,
            '_date' => $this->_date,
            'country' => $country,
            'city' => $city, // страна пользователя
            'department' => $department,
            'creator_user_id' => $this->creator_user_id,
            'user' => $surname,
            'comment' => $this->comment,
            'status_type' => $this->status_ew_id,
            'status_type_str' => $this->statusEw->typeStr,
            'inner_status' => $this->statusEw->inner,
            'inner_status_str' => ($this->status_ew_id == "") ? "" : $this->statusEw->getInnerList()[$this->statusEw->inner],
            'uniq_id' => $this->getUniqueId(),
            'status_country' => $this->status_country,
            'country_select_entity' => $this->countryModel !== null ? $this->countryModel->getSelectEntityWidget() : '',
        ];
    }
}

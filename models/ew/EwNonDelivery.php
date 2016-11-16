<?php
/**
 * Created by PhpStorm.
 * User: ogaidaienko
 * Date: 25.06.2015
 * Time: 14:37
 */
namespace app\models\ew;

use app\models\dictionaries\access\User;
use app\models\common\CommonModel;
use app\models\common\DateFormatBehavior;
use app\models\dictionaries\employee\Employee;
use app\models\dictionaries\nondelivery\NonDelivery;
use yii\db\Query;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%ew_nondelivery}}".
 *
 * @property integer $id
 * @property integer $ew_id
 * @property integer $nondelivery_id
 * @property integer $creator_user_id
 * @property string $nondelivery_date
 * @property string $additional_info
 *
 * @property ExpressWaybill $ew
 * @property User $user
 * @property \app\models\dictionaries\employee\Employee $employee
 */
class EwNonDelivery extends CommonModel {

    /**
     * @var VISIBLE статус видимый
     */
    const VISIBLE = 1;
    /**
     * @var INVISIBLE статус невидимый
     */
    const INVISIBLE = 0;

    public static function tableName() {
        return '{{%ew_nondelivery}}';
    }

    public function rules() {
        return [
            [['ew_id','nondelivery_id', 'creator_user_id'], 'integer'],
            [['_nondelivery_date'], 'validateDate'],
            [['additional_info'], 'string'],
            ['nondelivery_id',  'validateNull'/*'required', 'when' => function($model) {
                $x= $model->nondelivery_id;
                $bool = ($model->nondelivery_id == null || $model->nondelivery_id == 0) ? true : false;
                $d= 0;
                return $bool;
            }*/],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ew', 'ID'),
            'ew_id' => Yii::t('ew', 'Ew ID'),
            'nondelivery_id' => Yii::t('ew', 'Nondelivery Name'),
            'creator_user_id' => Yii::t('ew', 'Employee'),
            'nondelivery_date' => Yii::t('ew', 'Nondelivery Date'),
            '_nondelivery_date' => Yii::t('ew', 'Nondelivery Date'),
            'parent_id' => Yii::t('ew', 'Parent Id'),
            'occ_zone' => Yii::t('ew', 'Occ Zone'),
            'country' => Yii::t('app', 'Country'),
            'city' => Yii::t('app', 'City'),
            'departament' => Yii::t('app', 'Department'),
            'additional_info' =>Yii::t('ew', 'Additional Info')
        ];
    }

    public function behaviors() {
        return [[
            'class' => DateFormatBehavior::className(),
            'attributes' => [
                '_nondelivery_date' => 'nondelivery_date',
            ],
        ]];
    }

    public function getListNonDelivery() {

        return $this->hasOne(NonDelivery::className(), ['id' => 'nondelivery_id']);
    }

   public function validateDate($attribute, $params){
        if (!DateFormatBehavior::validate($this->$attribute))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }

    public function beforeSave() {

        if(!$this->nondelivery_id) {
            $this->addError("nondelivery_id", Yii::t('app', 'Sub Nondelivery cannot be blank'));
            return false;
        }
        return true;
    }

    public function validateNull($attribute, $params) {
        if (/*$this->$attribute == null || */$this->$attribute == "0")
            $this->addError($attribute, Yii::t('app', 'Sub Nondelivery cannot be blank'));
    }

    public function getEw() {

        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    public function getUser() {

        return $this->hasOne(User::className(), ['user_id' => 'creator_user_id']);
    }

    public function getEmployee() {

        return Employee::findOne(['id' => $this->user->user_id]);
    }

    public static function getListEmployee() {

        $empTable= Employee::tableName();
        $empTranslateTable= '{{%employee_translate}}';
        $lang = Yii::$app->language;

        $users = User::find()
            ->select(User::tableName().'.user_id, surname_full ')
            ->leftJoin("$empTable emp", 'emp.id = '.User::tableName().'.employee_id')
            ->leftJoin("$empTranslateTable tr", "tr.employee_id = emp.id and lang='$lang'" )
            ->asArray(true)
            ->all();

        $listEmployee = [];
        foreach($users as $key => $user) {

            $listEmployee[] = ['id' => $user['user_id'], 'surname' => $user['surname_full']];
        }
        return ArrayHelper::map($listEmployee, 'id', 'surname');
    }

    public static function getNameList() {
        $lang = Yii::$app->language;
        $query = new Query();
        $names = $query->select('name, id')
            ->from('{{%list_nondelivery}} as a')
            ->leftJoin('{{%list_nondelivery_translate}} as b', 'a.id = b.nondelivery_id')
            ->where('lang = :lang AND parent_id IS NULL AND visible = :visible AND state != :state',
                [':lang' => $lang, ':visible' => self::VISIBLE, ':state' => self::STATE_DELETED])
            ->all();
        return ArrayHelper::map($names, 'id', 'name');
    }


    public function toJson() {
        return [
            'id' => $this->id,
            '_nondelivery_date' => $this->_nondelivery_date,
            'additional_info'   => $this->additional_info,
            'country'           => $this->employee->country->nameOfficial,
            'city'              => $this->employee->city,
            'departament'       => $this->employee->departament,
            'creator_user_id'   => $this->creator_user_id,
            'nondelivery_id'    => $this->nondelivery_id,
            'occ_zone'          => NonDelivery::getOccZoneList()[$this->listNonDelivery->occ_zone],
            'parent_id'         => $this->listNonDelivery->parent_id,
        ];
    }

}
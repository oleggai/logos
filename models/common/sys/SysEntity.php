<?php

namespace app\models\common\sys;

use app\models\dictionaries\access\User;
use app\models\common\CommonModel;
use app\models\common\DateFormatBehavior;
use app\models\common\Setup;
use app\widgets\BtnCreateTab;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%sys_entity}}".
 *
 * @property string $id
 * @property string $entity_code
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 *
 * @property SysTables[] $sysTables
 * @property SysTables $mainSysTable
 * @property SysHistoryEntityOp[] $sysHistoryEntityOps
 */
class SysEntity extends CommonModel
{

    public static $updateOperations = [
        CommonModel::OPERATION_UPDATE,
        CommonModel::OPERATION_CLOSE,
        CommonModel::OPERATION_DELETE,
        CommonModel::OPERATION_CANCEL,
    ];

    public static $startUpdateOperations = [
        CommonModel::OPERATION_BEGIN_UPDATE,
        CommonModel::OPERATION_BEGIN_CLOSE,
        CommonModel::OPERATION_BEGIN_DELETE,
        CommonModel::OPERATION_BEGIN_CANCEL,
    ];



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sys_entity}}';
    }

    public static function getOperationDateLimit(){
        return date(Setup::MYSQL_DATE_FORMAT, strtotime("-1 hours"));
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_code', 'name_en', 'name_ru', 'name_uk'], 'required'],
            [['entity_code', 'name_en', 'name_ru', 'name_uk'], 'string', 'max' => 100],
            [['entity_code'], 'unique'],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('sys', 'ID'),
            'entity_code' => Yii::t('sys', 'Entity Code'),
            'name_en' => Yii::t('sys', 'Name En'),
            'name_ru' => Yii::t('sys', 'Name Ru'),
            'name_uk' => Yii::t('sys', 'Name Uk'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSysTables()
    {
        return $this
            ->hasMany(SysTables::className(), ['id' => 'tables_id'])
            ->viaTable('{{%sys_entity_tables}}', ['entity_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainSysTable()
    {
        return $this
            ->hasMany(SysTables::className(), ['id' => 'tables_id'])
            ->viaTable('{{%sys_entity_tables}}', ['entity_id' => 'id'])
            ->where ('main = 1');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSysHistoryEntityOps()
    {
        return $this->hasMany(SysHistoryEntityOp::className(), ['entity_id' => 'id']);
    }

    public static function saveOperation($entity_code, $entity_real_id, $operation){


        // выход из системы. удаление всех начатых операций
        if ($operation == CommonModel::OPERATION_LOGOUT){

            SysHistoryEntityOp::deleteAll(
                "user_id = ".Yii::$app->user->id." and
                 operation in (". implode(',', self::$startUpdateOperations).")"
            );
        }

        if (!$entity_real_id || !$entity_code)
            return null;

        $entity = self::findOne(['entity_code'=>$entity_code]);
        if (!$entity)
            return null;//Yii::t('app', 'Entity not found, code: ').$entity_code;

        $operation = $operation ?: CommonModel::OPERATION_UPDATE;


        $checkResult = self::checkBeforeSave($entity, $entity_real_id, $operation);
        if ($checkResult)
            return $checkResult;

        $sysOperation = new SysHistoryEntityOp();
        $sysOperation->entity_id = $entity->id;
        $sysOperation->entity_record_id = $entity_real_id;
        $sysOperation->user_id = Yii::$app->user->id;
        $sysOperation->operation = $operation;
        $sysOperation->operation_date = date(Setup::MYSQL_DATE_FORMAT);


        if (!$sysOperation->save()) {
            return Yii::t('app', 'Operation not saved!');
        }


        // если операции обнволения или просмотра - удаляем все остальные операции которые были до просмотра
        if (in_array($operation, self::$updateOperations) || $operation == CommonModel::OPERATION_VIEW){

            SysHistoryEntityOp::deleteAll(
                "entity_id = {$sysOperation->entity_id} and
                 entity_record_id = {$sysOperation->entity_record_id} and
                 user_id = {$sysOperation->user_id} and
                 operation_date < '{$sysOperation->operation_date}' "
            );

        }

        return null;
    }

    private function checkBeforeSave($entity, $entity_real_id, $operation){

        if (!in_array($operation, self::$updateOperations)){
            return null;
        }

        $startSelfOp = SysHistoryEntityOp::find()
            ->where(['entity_id' => $entity->id, 'entity_record_id'=> $entity_real_id, 'user_id'=>Yii::$app->user->id])
            ->orderBy('operation_date desc')
            ->limit(1)
            ->one();

        $startSelfOpDate = $startSelfOp ? $startSelfOp->operation_date : self::getOperationDateLimit();

        $endOthersOp = SysHistoryEntityOp::find()
            ->where(['entity_id' => $entity->id, 'entity_record_id'=> $entity_real_id,])
            ->andWhere("operation_date > '$startSelfOpDate'")
            ->andWhere("operation in (". implode (',', self::$updateOperations).")")
            ->orderBy('operation_date desc')
            ->limit(1)
            ->one();

        if ($endOthersOp) {
            $b = new DateFormatBehavior();
            $user = User::findIdentity($endOthersOp->user_id);
            //$user_name = $user->employee->surnameShort;
            $date = $b->convertFromStoredFormat($endOthersOp->operation_date);
            $operation = Yii::t('operations','operation_'.$endOthersOp->operation);
            $message = Yii::t('app', "attention_1");
            $user_name = BtnCreateTab::createLink($user->employee->surnameShort,
                Yii::t('tab_title', 'employee_full_name').' '.$user->user_id.' '.Yii::t('tab_title', 'view_command'),
                Url::to(['dictionaries/employee/view', 'id'=>$user->employee_id]),
                '');

            return sprintf($message,$user_name,$operation, $date);
        }

        return null;

    }



    /**
     * @param $entity_code
     * @param $entity_real_id
     * @return SysHistoryEntityOp[]
     */
    public static function getEditingUsers($entity_code, $entity_real_id){

        $entity = self::findOne(['entity_code'=>$entity_code]);
        if (!$entity)
            return [];

        $otherUsersOp = SysHistoryEntityOp::find()
            ->where(['entity_id' => $entity->id, 'entity_record_id'=> $entity_real_id])
            ->andWhere("user_id <>". Yii::$app->user->id)
            ->andWhere("operation_date > '". self::getOperationDateLimit(). "'")
            ->andWhere("operation in (". implode(',', self::$startUpdateOperations).")")
            ->orderBy('operation_date desc')
            ->all();

        return $otherUsersOp;
    }

    public function getIdentity(){
        return 0;
    }
}

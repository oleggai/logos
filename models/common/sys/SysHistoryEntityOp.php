<?php

namespace app\models\common\sys;

use app\models\common\CommonModel;
use app\models\dictionaries\access\User;
use Yii;

/**
 * This is the model class for table "{{%sys_history_entity_op}}".
 *
 * @property string $entity_id
 * @property string $entity_record_id
 * @property string $user_id
 * @property integer $operation
 * @property string $operation_date
 *
 * @property SysEntity $entity
 * @property User $user
 */
class SysHistoryEntityOp extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sys_history_entity_op}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_id', 'entity_record_id', 'user_id', 'operation'], 'required'],
            [['entity_id', 'entity_record_id', 'user_id', 'operation'], 'integer'],
            [['operation_date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'entity_id' => Yii::t('sys', 'Entity ID'),
            'entity_record_id' => Yii::t('sys', 'Entity Record ID'),
            'user_id' => Yii::t('sys', 'User ID'),
            'operation' => Yii::t('sys', 'Operation'),
            'operation_date' => Yii::t('sys', 'Operation Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEntity()
    {
        return $this->hasOne(SysEntity::className(), ['id' => 'entity_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getIdentity(){
        return 0;
    }

    public static function primaryKey(){
        return ['entity_id', 'entity_record_id', 'user_id', 'operation', 'operation_date'];
    }
}

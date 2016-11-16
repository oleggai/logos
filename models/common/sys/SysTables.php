<?php

namespace app\models\common\sys;

use app\models\common\CommonModel;
use Yii;

/**
 * This is the model class for table "{{%sys_tables}}".
 *
 * @property string $id
 * @property string $guid
 * @property string $table_name
 *
 * @property SysEntity[] $sysEntities
 */
class SysTables extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sys_tables}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['guid', 'table_name'], 'required'],
            [['guid', 'table_name'], 'string', 'max' => 100],
            [['guid'], 'unique'],
            [['table_name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('sys', 'ID'),
            'guid' => Yii::t('sys', 'Guid'),
            'table_name' => Yii::t('sys', 'Table Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSysEntityTables()
    {
        return $this
            ->hasMany(SysEntity::className(), ['id' => 'entity_id'])
            ->viaTable('{{%sys_entity_tables}}', ['tables_id' => 'id']);
    }
}

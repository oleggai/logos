<?php

namespace app\models\ew;

use Yii;
use app\models\common\CommonModel;
use app\models\dictionaries\access\User;

/**
 * This is the model class for table "{{%rel_ew_ew}}".
 *
 * @property string $id
 * @property string $ew_id_init
 * @property string $ew_id
 * @property string $creator_user_id
 * @property string $date
 *
 * @property ExpressWaybill $ewInit
 * @property ExpressWaybill $ew
 * @property User $creatorUser
 */
class RelEwEw extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%rel_ew_ew}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ew_id_init', 'ew_id'], 'required'],
            [['ew_id_init', 'ew_id', 'creator_user_id'], 'integer'],
            [['date'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ew', 'ID'),
            'ew_id_init' => Yii::t('ew', 'Ew Id Init'),
            'ew_id' => Yii::t('ew', 'Ew ID'),
            'creator_user_id' => Yii::t('ew', 'Creator User ID'),
            'date' => Yii::t('ew', 'Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEwInit()
    {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id_init']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEw()
    {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatorUser()
    {
        return $this->hasOne(User::className(), ['user_id' => 'creator_user_id']);
    }
}

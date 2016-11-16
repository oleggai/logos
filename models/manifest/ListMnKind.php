<?php

namespace app\models\manifest;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%list_mn_kind}}".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property integer $visible
 * @property integer $state
 *
 * @property Manifest[] $manifests
 */
class ListMnKind extends \app\models\common\CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_mn_kind}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['visible', 'state'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
            [['name_ru'], 'unique'],
            [['name_en'], 'unique'],
            [['name_uk'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('manifest', 'ID'),
            'name_en' => Yii::t('manifest', 'Name En'),
            'name_ru' => Yii::t('manifest', 'Name Ru'),
            'name_uk' => Yii::t('manifest', 'Name Uk'),
            'visible' => Yii::t('manifest', 'Visible'),
            'state' => Yii::t('manifest', 'State'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManifests()
    {
        return $this->hasMany(Manifest::className(), ['mn_kind' => 'id']);
    }

    public function toJson() {

        return [
            'id' => $this->id,
            'state' => $this->state,
            'name_ru' => $this->name_ru,
            'name_en' => $this->name_en,
            'name_uk' => $this->name_uk,
            'visibilityText' => $this->visibilityText
        ];
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return null;

        $this->state = \app\models\common\CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyMnKind($params);
    }

    public function copyMnKind($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $type = ListMnKind::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);

        $this->operation = self::OPERATION_NONE;
    }

    public function getGridOperations() {
        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app','Copy'),
        ];
    }

    public function getGridOperationsOptions() {
        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before'=>true, 'tab_name_sufix'=>'copy'],
        ];
    }

}

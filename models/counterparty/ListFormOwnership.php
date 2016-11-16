<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%list_form_ownership}}".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $short_name_en
 * @property string $short_name_ru
 * @property string $short_name_uk
 *
 * @property CounterpartyLegalEntity[] $counterpartyLegalEntities
 */
class ListFormOwnership extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_form_ownership}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_en', 'name_ru', 'name_uk', 'short_name_en', 'short_name_ru', 'short_name_uk', 'visible'], 'required'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 100],
            [['name_en', 'name_ru', 'name_uk'], 'unique'],
            [['short_name_en', 'short_name_ru', 'short_name_uk'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('counterparty', 'ID'),
            'name_en' => Yii::t('counterparty', 'Name En'),
            'name_ru' => Yii::t('counterparty', 'Name Ru'),
            'name_uk' => Yii::t('counterparty', 'Name Uk'),
            'short_name_en' => Yii::t('counterparty', 'Short Name En'),
            'short_name_ru' => Yii::t('counterparty', 'Short Name Ru'),
            'short_name_uk' => Yii::t('counterparty', 'Short Name Uk'),
            'name' => Yii::t('counterparty', 'Form Ownership'),
            'shortName' => Yii::t('counterparty', 'Form Ownership'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyLegalEntities()
    {
        return $this->hasMany(CounterpartyLegalEntity::className(), ['form_ownership' => 'id']);
    }

    /**
     * Возвращает имя соответствующее текущему языку
     * @return string
     */
    public function getName()
    {
        return $this->{"name_".Yii::$app->language};
    }

    /**
     * Возвращает короткое имя соответствующее текущему языку
     * @return string
     */
    public function getShortName()
    {
        return $this->{"short_name_".Yii::$app->language};
    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);
    }

    /**
     * Возвращает данные для грида
     * @return array
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ru' => $this->name_ru,
            'name_uk' => $this->name_uk,
            'short_name_en' => $this->short_name_en,
            'short_name_ru' => $this->short_name_ru,
            'short_name_uk' => $this->short_name_uk,
            'state' => $this->state,
            'visible' => $this->visible,
            'visibilityText' => $this->visibilityText
        ];
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

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyListFormOwnership($params);
    }

    public function copyListFormOwnership($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $type = ListFormOwnership::findOne(['id' => $params['id']]);
            if($type) {
                $this->attributes = $type->getAttributes();
            }
        }
    }
}

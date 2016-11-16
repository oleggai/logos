<?php

namespace app\models\counterparty;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%list_pers_doc_type}}".
 *
 * @property string $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 *
 * @property CounterpartyPersDocs[] $counterpartyPersDocs
 */
class ListPersDocType extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_pers_doc_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['visible', 'state'], 'integer'],
            [['name_en', 'name_ru', 'name_uk'], 'required'],
            [['name_en', 'name_ru', 'name_uk'], 'string', 'max' => 50],
            [['name_en', 'name_ru', 'name_uk'], 'unique']
        ]);
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
            'name' => Yii::t('counterparty', 'Doc Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterpartyPersDocs()
    {
        return $this->hasMany(CounterpartyPersDocs::className(), ['doc_type' => 'id']);
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
     * Получение списка типов документов в виде ассоциативного массива, где ключ - id, значение - название
     * @param bool $empty true = добавлять пустое значение
     * @return array массив типов
     */
    public static function getList($empty = false, $lang=null) {

        if (!$lang)
            $lang = Yii::$app->language;

        $models = ListPersDocType::findAll(['state' => 1]);
        $result = ArrayHelper::map($models, 'id', "name_$lang");

        if ($empty)
            $result = [null => ''] +  $result;
        return  $result;
    }

/*
    public static function getActList($currentIdList, $empty = false) {

        if(!$currentIdList)
            return ListPersDocType::getList($empty, null);

        return  ListPersDocType::getActualList($currentIdList, 'name', $empty);
    }
*/
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
            'state' => $this->state,
            'visible' => $this->visible,
            'visibilityText' => $this->visibilityText
        ];
    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        $this->saveSServiceData($insert);
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового склада
     * @param $params
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;

        if ($params['operation'] != null)
            $this->copyPersDocType($params);
    }

    public function copyPersDocType($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $persDocType = ListPersDocType::findOne(['id' => $params['id']]);
            if($persDocType) {
                $this->attributes = $persDocType->getAttributes();
            }
        }
    }

    public function getGridOperations() {
        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app','Copy'),
        ];
    }

    public function getGridOperationsOptions() {
        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']), 'separator_before'=>true, 'tab_name_sufix'=>'copy'],
        ];
    }

}

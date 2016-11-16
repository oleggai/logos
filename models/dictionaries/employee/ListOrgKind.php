<?php

namespace app\models\dictionaries\employee;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%list_org_kind}}".
 *
 * @property string $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $name_uk
 * @property string $name_short_ru
 * @property string $name_short_en
 * @property string $name_short_uk
 * @property integer $visible
 * @property integer $state
 * @property string $name
 *
 * @property Employee[] $employees
 */
class ListOrgKind extends CommonModel
{
    /**
     * @var string name полное название на текущем языке
     */
    public $name;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_org_kind}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_ru', 'name_en', 'name_uk'], 'required'],
            [['visible', 'state'], 'integer'],
            [['name_ru', 'name_en', 'name_uk'], 'string', 'max' => 255],
            [['name_short_ru', 'name_short_en', 'name_short_uk'], 'string', 'max' => 10],
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
            'id' => Yii::t('orgkind', 'ID'),
            'name_ru' => Yii::t('orgkind', 'Name Ru'),
            'name_en' => Yii::t('orgkind', 'Name En'),
            'name_uk' => Yii::t('orgkind', 'Name Uk'),
            'name_short_ru' => Yii::t('orgkind', 'Name Short Ru'),
            'name_short_en' => Yii::t('orgkind', 'Name Short En'),
            'name_short_uk' => Yii::t('orgkind', 'Name Short Uk'),
            'visible' => Yii::t('orgkind', 'Visible'),
            'state' => Yii::t('orgkind', 'State'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployees()
    {
        return $this->hasMany(Employee::className(), ['org_kind' => 'id']);
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового сотрудника
     * @param $params
     */
    public function generateDefaults($params) {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyOrgKind($params);
    }

    /**
     * Копирование сотрудников
     * @param $params
     */
    public function copyOrgKind($params) {
        if($params['operation'] == self::OPERATION_COPY) {
            $state = ListOrgKind::findOne(['id' => $params['id']]);
            if($state) {
                $this->attributes = $state->getAttributes();
            }
        }
    }


    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ru' => $this->name_ru,
            'name_uk' => $this->name_uk,
            'name_short_en' => $this->name_short_en,
            'name_short_ru' => $this->name_short_ru,
            'name_short_uk' => $this->name_short_uk,
            'visible' => $this->visible,
            'state' => $this->state,
            'visibilityText' => $this->visibilityText
        ];
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);
        $this->saveSServiceData($insert);
    }

    /**
     * Метод получения короткого названия
     * @param string $lang en|ru|uk по умолчанию текущий язык
     * @return string
     */
    public function getShortName($lang = '') {
        if (empty($lang))
            $lang = Yii::$app->language;
        return $this->getAttribute('name_short_' . $lang);
    }

    public function getGridOperations() {

        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app', 'Copy'),
        ];
    }

    public function getGridOperationsOptions() {

        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before' => true, 'tab_name_sufix' => 'copy'],
        ];
    }

}

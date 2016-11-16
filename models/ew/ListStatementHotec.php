<?php
/**
 * Файл класса модели заявлений Hotec
 */

namespace app\models\ew;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\common\CommonModel;

/**
 * Модель списка заявок Hotec
 * @author Гайдаенко Олег
 * @category Hotec
 *
 * @property integer $id
 * @property string $name_en
 * @property string $name_ru
 * @property string $name_uk
 * @property string $name_short_en
 * @property string $name_short_ru
 * @property string $name_short_uk
 * @property string $visible
 */

class ListStatementHotec extends CommonModel {
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%list_statement_hotec}}';
    }

    public function rules() {
        return [
            [['id'], 'integer'],
            [['name_en', 'name_uk', 'name_ru', 'name_short_en', 'name_short_uk', 'name_short_ru'], 'required'],
            [['name_en', 'name_uk', 'name_ru',], 'string', 'max' => 500],
            [['name_en', 'name_uk', 'name_ru',], 'unique'],
            [['state', 'visible'], 'integer']
        ];
    }

    public function attributeLabels() {
        return [
            'name_en' => \Yii::t('ew', 'Name En'),
            'name_ru' => \Yii::t('ew', 'Name Ru'),
            'name_uk' => \Yii::t('ew', 'Name Uk'),
        ];
    }

    public function generateDefaults($params) {

        if ($this->hasErrors())
            return;

        $this->state = CommonModel::STATE_CREATED;
        if ($params['operation'] != null)
            $this->copyHotec($params);
    }

    public function copyHotec($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $hotec = ListStatementHotec::findOne(['id' => $params['id']]);
            if($hotec) {
                $this->attributes = $hotec->getAttributes();
                $this->id = null;
            }
        }
    }

    public static function getList($empty = false, $field = 'name') {
        if($field == 'name') {
            $field=$field.'_'.\Yii::$app->language;
        }
        $arr = ListStatementHotec::find()->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;

    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);
        $this->saveSServiceData($insert);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'id'=>$this->id,
            'name_en'=>$this->name_en,
            'name_uk'=>$this->name_uk,
            'name_ru'=>$this->name_ru,
            'name_short_en' => $this->name_short_en,
            'name_short_ru' => $this->name_short_ru,
            'name_short_uk' => $this->name_short_uk,
            'visible' => $this->visible,
            'state' => $this->state,
            'visibilityText' => $this->visibilityText
        ];

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
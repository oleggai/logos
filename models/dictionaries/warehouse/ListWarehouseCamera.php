<?php

namespace app\models\dictionaries\warehouse;

use app\models\common\DateFormatBehavior;
use Yii;
use app\models\common\CommonModel;

/**
 * Модель для камер подразделения
 * @author Richok FG
 * @category warehouse
 *
 * @property string $id
 * @property string $warehouse
 * @property string $camera_name
 * @property string $camera_model
 * @property string $zone
 * @property string $placement
 * @property string $placement_date
 * @property string $addition_info
 *
 * @property ListWarehouseZone $zoneModel
 * @property ListWarehouse $warehouseModel
 */
class ListWarehouseCamera extends CommonModel
{
    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%list_warehouse_camera}}';
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['camera_name', 'camera_model', 'placement'], 'required'],
            [['warehouse', 'zone'], 'integer'],
            [['placement_date'], 'safe'],
            [['_placement_date'], 'validateDate', ],
            [['camera_name', 'camera_model'], 'string', 'max' => 50],
            [['placement', 'addition_info'], 'string', 'max' => 100]
        ]);
    }

    /**
     * Надписи для полей
     * @return array массив названий полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('warehouse', 'ID'),
            'warehouse' => Yii::t('warehouse', 'Warehouse'),
            'camera_name' => Yii::t('warehouse', 'Camera name'),
            'camera_model' => Yii::t('warehouse', 'Camera model'),
            'zone' => Yii::t('warehouse', 'Zone name'),
            'placement' => Yii::t('warehouse', 'Placement'),
            'placement_date' => Yii::t('warehouse', 'Placement date'),
            '_placement_date' => Yii::t('warehouse', 'Placement date'),
            'addition_info' => Yii::t('warehouse', 'Additional information'),
        ];
    }

    public function validateDate($attribute,$params){
        if (!DateFormatBehavior::validate($this->$attribute))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }

    /**
     * Формирование полей по-умолчанию, перед созданием новой камеры
     */
    public function generateDefaults() {
        if ($this->hasErrors())
            return;
        $this->state = CommonModel::STATE_CREATED;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getZoneModel()
    {
        return $this->hasOne(ListWarehouseZone::className(), ['id' => 'zone']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouseModel()
    {
        return $this->hasOne(ListWarehouse::className(), ['id' => 'warehouse']);
    }

    /**
     * Поведения
     */
    function behaviors()
    {
        return [
            [
                'class' => DateFormatBehavior::className(),
                'attributes' => [
                    '_placement_date' => 'placement_date',
                ]
            ],
        ];
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля => значение
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'camera_name' => $this->camera_name,
            'camera_model' => $this->camera_model,
            'zone' => $this->zone,
            'zone_name' => $this->zoneModel->name,
            'placement' => $this->placement,
            '_placement_date' => $this->_placement_date,
            'addition_info' => $this->addition_info,
        ];
    }
}

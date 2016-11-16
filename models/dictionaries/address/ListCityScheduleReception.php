<?php

namespace app\models\dictionaries\address;

use app\models\common\CommonModel;
use Yii;

/**
 * Справочник населеных пунктов (График приема заявок дискпечерской службой)

 * @property string $id 
 * @property string $city Ссылка на справочник нас.пунктов
 * @property string $dayofweek Ссылка на день недели
 * @property string $schedule_type Ссылка на вид графика
 * @property string $time_begin Начало от
 * @property string $time_end Конец до
 *
 * @property ListCity $cityModel
 * @property ListDayofweek $dayofweekModel
 * @property ListScheduleType $scheduleTypeModel
 */
class ListCityScheduleReception extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_city_schedule_reception}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['dayofweek', 'schedule_type', 'time_begin', 'time_end'], 'required'],
            [['city', 'dayofweek', 'schedule_type'], 'integer'],
            [['time_begin', ], 'validateTimes'],
            ['time_end', 'safe'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'city' => Yii::t('adress', 'City'),
            'dayofweek' => Yii::t('adress', 'Dayofweek'),
            'dayofweekText' => Yii::t('adress', 'Dayofweek'),
            'schedule_type' => Yii::t('adress', 'Schedule Type'),
            'time_begin' => Yii::t('adress', 'Beginning from'),
            'time_end' => Yii::t('adress', 'End to'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCityModel()
    {
        return $this->hasOne(ListCity::className(), ['id' => 'city']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDayofweekModel()
    {
        return $this->hasOne(ListDayofweek::className(), ['id' => 'dayofweek']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScheduleTypeModel()
    {
        return $this->hasOne(ListScheduleType::className(), ['id' => 'schedule_type']);
    }

    public function toJson(){

        return [
            'id'=>$this->id,
            'dayofweek' => $this->dayofweek,
            'dayofweekText'=>$this->dayofweekModel->name,
            'schedule_type' => $this->schedule_type,
            'time_begin' => substr($this->time_begin,0,5),
            'time_end' => substr($this->time_end,0,5),
        ];
    }

    public function validateTimes($attribute,$params){
        if ($this->time_begin > $this->time_end)
            $this->addError($attribute,
                Yii::t('adress', "'{$this->getAttributeLabel('time_begin')}' more than '{$this->getAttributeLabel('time_end')}'"));

    }
}

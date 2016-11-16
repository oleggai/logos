<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;

/**
 * Модель места в накладной
 *
 * @property string $ew_id Ссыдка на накладную
 * @property string $place_number Номер места
 * @property string $place_bc ШК
 * @property string $place_shipment_desc Описание груза
 * @property string $length Длина, см
 * @property string $width Ширина, см
 * @property string $height Высота, см
 * @property string $dimensional_weight Объемный вес, кг
 * @property string $actual_weight Фактический вес, кг
 * @property string $place_pack Упаковка
 * @property string $place_pack_num Номер упаковки
 *
 * @property ExpressWaybill $ew
 */
class EwPlace extends CommonModel
{
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%ew_place}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            [[ 'place_number', 'place_bc'], 'required'],
            [['ew_id', 'place_number'], 'integer'],
            [['length', 'width', 'height', 'dimensional_weight', 'actual_weight'], 'number'],
            [['place_shipment_desc', 'place_pack'], 'string', 'max' => 255],
            [['place_pack_num'], 'string', 'max' => 20],
            [['place_bc'], 'string', 'max' => 30]
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'ew_id' => Yii::t('ew', 'Ew ID'),
            'place_number' => Yii::t('ew', '#'),
            'place_bc' => Yii::t('ew', 'Barcode'),
            'place_shipment_desc' => Yii::t('ew', 'Shipment Desc'),
            'length' => Yii::t('ew', 'Length'),
            'width' => Yii::t('ew', 'Width'),
            'height' => Yii::t('ew', 'Height'),
            'dimensional_weight' => Yii::t('ew', 'Dimensional Weight'),
            'actual_weight' => Yii::t('ew', 'Actual Weight'),
            'place_pack' => Yii::t('ew', 'Pack'),
            'place_pack_num' => Yii::t('ew', 'Pack Num'),
        ];
    }

    /**
     * Метод получения модели накладной
     */
    public function getEw()
    {
        return $this->hasOne(ExpressWaybill::className(), ['id' => 'ew_id']);
    }

    public function toJson(){
        return [
            'id'=>$this->place_bc,
            'ew_id'=>$this->ew_id,
            'place_number'=>$this->place_number,
            'place_bc'=>$this->place_bc,
            'place_shipment_desc'=>$this->place_shipment_desc,
            'length'=>$this->length,
            'width'=>$this->width,
            'height'=>$this->height,
            'dimensional_weight'=>$this->dimensional_weight,
            'actual_weight'=>$this->actual_weight,
            'place_pack'=>$this->place_pack,
            'place_pack_num'=>$this->place_pack_num,

        ];
    }

    public static function sortPlaces($a, $b) {
        if ($a["place_number"] == $b["place_number"]) {
            return 0;
        }
        return ($a["place_number"] < $b["place_number"]) ? -1 : 1;
    }
}

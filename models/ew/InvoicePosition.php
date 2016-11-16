<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use app\models\ew\Units;
use Yii;

/**
 * Модель позиции инвоиса
 *
 * @property string $inv_pos_id
 * @property string $inv_id Ссылка на инвоис
 * @property string $full_desc Описание
 * @property string $customs_goods_code Таможенный код товара
 * @property string $manufacturer_country_code Код страны производителя
 * @property integer $pieces_quantity Количество
 * @property integer $units_of_measurement Код ЕИ
 * @property string $cost_per_piece Стоимость за единицу
 * @property string $total_cost Итоговая цена
 * @property string $material_good Материал товара
 * @property float $pieces_weight
 *
 * @property Invoice $inv Модель инвоиса
 * @property Units $piecesUnits Модель единицы измерения
 */
class InvoicePosition extends CommonModel
{
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%invoice_position}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
//            [['inv_id', 'full_desc', 'customs_goods_code', 'manufacturer_country_code', 'pieces_amount', 'pieces_weight', 'pieces_currency', 'cost_per_piece'], 'required'],
            [['full_desc', 'pieces_quantity', 'units_of_measurement', 'cost_per_piece'], 'required'],
            [['inv_id', 'manufacturer_country_code', 'units_of_measurement'], 'integer'],
            [['cost_per_piece', 'total_cost','pieces_quantity'], 'number'],
            [['full_desc'], 'string', 'max' => 500],
            [['customs_goods_code'], 'string', 'max' => 20],
            [['material_good'], 'string', 'max' => 200],
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'inv_pos_id' => Yii::t('ew', 'ID'),
            'inv_id' => Yii::t('ew', 'Inv ID'),
            'full_desc' => Yii::t('ew', 'Full Desc'),
            'customs_goods_code' => Yii::t('ew', 'Customs Goods Code'),
            'manufacturer_country_code' => Yii::t('ew', 'Manufacturer Country Code'),
            'pieces_quantity' => Yii::t('ew', 'Pieces Quantity'),
            'units_of_measurement' => Yii::t('ew', 'Units Of Measurement'),
            'cost_per_piece' => Yii::t('ew', 'Cost Per Piece'),
            'total_cost' => Yii::t('ew', 'Total Cost'),
            'material_good' => Yii::t('ew', 'Material Item'),
            'customs_goods_code' => Yii::t('ew', 'Customs Goods Code'),
            'statement_notes'=> Yii::t('ew', 'Statement Notes'),
        ];
    }

    /**
     * Метод получения модели инвоиса
     */
    public function getInv()
    {
        return $this->hasOne(Invoice::className(), ['id' => 'inv_id']);
    }

    /**
     * Метод получения модели единицы измерения
     */
    public function getPiecesUnits()
    {
        return $this->hasOne(Units::className(), ['id' => 'units_of_measurement']);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return [
            'inv_pos_id'=>$this->inv_pos_id,
            'full_desc'=>$this->full_desc,
            'material_good' => $this->material_good,
            'customs_goods_code'=>$this->customs_goods_code,
            'manufacturer_country_code'=>$this->manufacturer_country_code,
            'pieces_quantity'=>$this->pieces_quantity,
            'units_of_measurement'=> $this->units_of_measurement,
            'cost_per_piece'=>$this->cost_per_piece,
            'total_cost'=>$this->total_cost,
        ];
    }

}

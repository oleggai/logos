<?php
/**
 * Файл класса модели для связи Иновйса и справочника
 */

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;

/**
 * Класс модели для связи Иновйса и справочника - заявлений НОТЕС (ListStatementNotes)
 * @author Гайдаенко Олег
 * @category Hotec
 * @property $invoice_id Ид инвойса
 * @property $statement_hotec Ид заявки Hotec
 */

class InvoiceStHotec extends CommonModel {

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'statement_notes'=> Yii::t('ew', 'Statement Notes'),
            ]);

    }

    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%invoice_sthotec}}';
    }

    /**
     * Правила для атрибутов модели
     * @return array
     */
    public function rules() {
        return [
            [['statement_hotec'], 'required'],
            [['invoice_id', 'statement_hotec'], 'integer']
        ];
    }

    /**
     * Возвращает данные для грида
     * @return array
     */
    public function toJson() {
        return [
            'id' => $this->id,
            'statement_hotec' => $this->statement_hotec
        ];
    }

    /**
     * Связь со списком заявок Hotec
     * @return \yii\db\ActiveQuery
     */
    public function getListStatementNotes() {
        return $this->hasOne(ListStatementHotec::className(), ['id' => 'statement_hotec']);
    }
}
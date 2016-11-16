<?php

/**
 * Файл класса CpAttachedDoc
 * Связь между Контрагентом и прикрепленными документами
 */

namespace app\models\attached_doc;

use app\models\counterparty\Counterparty;
use Yii;
use app\models\common\CommonModel;

/**
 * Класс CpAttachedDoc
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 *
 * @property integer $cnt_id. Ссылка на Контрагента
 * @property integer $attdoc_id. Ссылка на прикрепленный документ
 * @property Counterparty $counterparty. Обьект Контрагента
 * @property AttachedDoc $attachedDoc. Обьект прикрепленного документа
 */
class CpAttachedDoc extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%counterparty_attachdoc}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['cnt_id', 'attdoc_id'], 'required'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCounterparty() {
        return $this->hasOne(Counterparty::className(), ['id' => 'cnt_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDoc() {
        return $this->hasOne(AttachedDoc::className(), ['id' => 'attdoc_id']);
    }
}
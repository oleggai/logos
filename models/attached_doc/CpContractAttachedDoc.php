<?php

/**
 * Файл класса CpContractAttachedDoc
 * Связь между Контрактом контрагента и прикрепленными документами
 */

namespace app\models\attached_doc;

use Yii;
use app\models\common\CommonModel;

/**
 * Класс CpContractAttachedDoc
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 *
 * @property integer $cntcontr_id. Ссылка на Контракт контрагента
 * @property integer $attdoc_id. Ссылка на прикрепленный документ
 * @property AttachedDoc $attachedDoc. Обьект прикрепленного документа
 */
class CpContractAttachedDoc extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%counterparty_contract_attachdoc}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['cntcontr_id', 'attdoc_id'], 'required'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDoc() {
        return $this->hasOne(AttachedDoc::className(), ['id' => 'attdoc_id']);
    }
}
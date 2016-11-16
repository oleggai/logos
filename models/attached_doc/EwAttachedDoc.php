<?php

/**
 * Файл класса EwAttachedDoc
 * Связь между ЕН и прикрепленными документами
 */

namespace app\models\attached_doc;

use app\models\ew\ExpressWaybill;
use Yii;
use app\models\common\CommonModel;

/**
 * Класс EwAttachedDoc
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 *
 * @property integer $ew_id. Ссылка на ЕН
 * @property integer $attdoc_id. Ссылка на прикрепленный документ
 * @property ExpressWaybill $ew. Обьект ЕН
 * @property AttachedDoc $attachedDoc. Обьект прикрепленного документа
 */
class EwAttachedDoc extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%ew_attachdoc}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['ew_id', 'attdoc_id'], 'required'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEw() {
        return $this->hasOne(\app\models\ew\ExpressWaybill::className(), ['id' => 'ew_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDoc() {
        return $this->hasOne(AttachedDoc::className(), ['id' => 'attdoc_id']);
    }

}

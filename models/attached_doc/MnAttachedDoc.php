<?php

/**
 * Файл класса MnAttachedDoc
 * Связь между Манифестом и прикрепленными документами
 */

namespace app\models\attached_doc;

use app\models\manifest\Manifest;
use Yii;
use app\models\common\CommonModel;

/**
 * Класс MnAttachedDoc
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 *
 * @property integer $mn_id. Ссылка на Манифеста
 * @property integer $attdoc_id. Ссылка на прикрепленный документ
 * @property Manifest $manifest. Обьект Манифеста
 * @property AttachedDoc $attachedDoc. Обьект прикрепленного документа
 */
class MnAttachedDoc extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%mn_attachdoc}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['mn_id', 'attdoc_id'], 'required'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManifest() {
        return $this->hasOne(Manifest::className(), ['id' => 'mn_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDoc() {
        return $this->hasOne(AttachedDoc::className(), ['id' => 'attdoc_id']);
    }
}
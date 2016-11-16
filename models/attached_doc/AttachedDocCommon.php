<?php
/**
 * Файл класса AttachedDoc
 * Прикрепленные документы
 */
namespace app\models\attached_doc;

use app\models\common\CommonModel;

class AttachedDocCommon extends CommonModel {
    /**
     * Метод получения доступных операция
     */
    public function getOperations() {
        // новая запись
        if ($this->isNewRecord)
            return [];

        // состояние удалена или закрыта
        if ($this->state == self::STATE_DELETED)
            return [self::OPERATION_CANCEL => \Yii::t('app', 'Restore')];

        //if ($this->operation == self::OPERATION_VIEW)
        return [
            self::OPERATION_UPDATE => \Yii::t('app', 'Update'),
        ];

        //return [ self::OPERATION_DELETE => Yii::t('app', 'Delete'),];
    }
}
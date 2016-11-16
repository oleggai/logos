<?php

namespace app\models\common\sys;

use app\models\common\CommonModel;
use Yii;

/**
 * Модель описывающая документы ИС
 * @property string $id Идентификатор документа
 * @property string $file_name Имя документа
 * @property string $file_path Путь на сервере к документу
 */
class SysFiles extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sys_files}}';
    }

    public function getAttachedDocFile() {}

    public function toJson() {
        return [
            'id'   => $this->id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path
        ];
    }

}

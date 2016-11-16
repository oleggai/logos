<?php

namespace app\models\common;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Модель для загрузки файла юзер-мануала
 * @author Richok FG
 */

class UploadManual extends Model {
    /**
     * @var UploadedFile
     */
    public $pdfFile;

    public function rules() {
        return [
            ['pdfFile', 'file', 'skipOnEmpty' => false, 'extensions' => 'pdf'],
        ];
    }

    public function attributeLabels() {
        return [
            'pdfFile' => Yii::t('app', 'Manual pdf-file')
        ];
    }

    public function upload() {

        if ($this->validate()) {
            $s = DIRECTORY_SEPARATOR;
            $path = Yii::getAlias('@app') . $s .'own_files' . $s . 'instructions' . $s;
            //if (!is_dir($path))
            //    mkdir($path);
            if ($this->pdfFile->saveAs($path . 'manual.pdf')) {
                return true;
            }
        } else {
            return false;
        }
    }
}
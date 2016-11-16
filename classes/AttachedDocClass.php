<?php

/**
 * Файл класса AttachedDocClass
 * Прикрепленные документы
 */
namespace app\classes;

use app\models\attached_doc\AttachedDocFile;

/**
 * Класс AttachedDocClass
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 */
class AttachedDocClass {

    /**
     * Общий метод для удаления файлов.
     * Используеться при удалении как файлов ПД так и ПД
     * @param array $attachedFileIds. Массив ид прикрепленных файлов
     */
    public static function deleteFiles(array $attachedFileIds) {
        // статистика по удалению, ['idFile' => 'res']
        $arrRes = [];
        foreach($attachedFileIds as $attachedFileId) {
            $attachedDocFileObj = AttachedDocFile::findOne(['id' => $attachedFileId]);
            // Проверяем существование ссылки на удаляемый файл
            $attachedDocFileObjEx = AttachedDocFile::find()->where('attacheddoc_id <> :attacheddoc_id AND files_id = :files_id',
                ['attacheddoc_id' => $attachedDocFileObj->attacheddoc_id, 'files_id' => $attachedDocFileObj->files_id])->one();
            // Если есть, то удаляем только ссылку
            if($attachedDocFileObjEx) {
                if($attachedDocFileObj->delete()) {
                    $arrRes[] = [$attachedDocFileObj->id => 1];
                }
            }
            // Иначе удаляем и ссылку и файл физически
            else {
                $userId = \Yii::$app->user->id;
                $fileId = $attachedDocFileObj->files_id;
                $attachDocFileId = $attachedDocFileObj->id;
                $storageConnector = new StorageConnector();
                // Удаляем ссылку, потом файл
                if($attachedDocFileObj->delete()) {
                    $arrRes[] = $storageConnector->deleteFile($userId, $fileId) ? [$attachDocFileId => 1] : [$attachDocFileId => 0];
                }
            }
        }
    }
}
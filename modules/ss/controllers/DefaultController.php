<?php

namespace app\modules\ss\controllers;

use app\modules\ss\StorageServer;
use yii\web\Controller;

/**
 * Контроллер модуля Storage Server
 * ?r=storage/default/save - сохранение файла
 * ?r=storage/default/get - получение файла
 * ?r=storage/default/delete - удаление файла
 * @package app\modules\ss\controllers
 */
class DefaultController extends Controller
{
    /**
     * @var StorageServer
     */
    public $module;


    /**
     * Сохранение файла
     * @param $user_id
     * @param $file_name
     * @param $file_content
     * @param $entity_code
     * @param $entity_id
     * @return bool|int false или номер файла
     */
    public function actionSave($user_id, $file_name, $file_content, $entity_code, $entity_id){

        return $this->module->saveFile($user_id,$file_name,$file_content,
            ['entity_code'=>$entity_code,'entity_id'=>$entity_id]);
    }

    /**
     * Получение файла
     * @param $user_id
     * @param $file_id
     * @return bool|string false или содержимое файла
     */
    public function actionGet($user_id, $file_id){

        return $this->module->getFile($user_id,$file_id);
    }

    /**
     * Удаление файла
     * @param $user_id
     * @param $file_id
     * @return bool Результат
     */
    public function actionDelete($user_id, $file_id){

        return $this->module->deleteFile($user_id,$file_id);
    }
}

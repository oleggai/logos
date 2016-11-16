<?php
/**
 * Created by PhpStorm.
 * User: goga
 * Date: 20.10.2015
 * Time: 17:40
 */

namespace app\classes;


class StorageConnector implements IStorageConnector
{
    const ACTION_DELETE = 'storage/default/delete';
    const ACTION_GET = 'storage/default/get';
    const ACTION_SAVE = 'storage/default/save';


    /**
     * Метод инициализации
     * @return bool Результат инициализации
     */
    public function init() {
        return true;
    }

    /**
     * Метод удаления файла
     * @param $user_id int Пользователь
     * @param $file_id int Файл
     * @return bool Результат
     */
    public function deleteFile($user_id, $file_id){

        return \Yii::$app->runAction(self::ACTION_DELETE, ['file_id'=>$file_id, 'user_id'=>$user_id]);
    }

    /**
     * Метод получения файла
     * @param $user_id int Пользователь
     * @param $file_id int Файл
     * @return bool Результат
     */
    public function getFile($user_id, $file_id){
        return \Yii::$app->runAction(self::ACTION_GET, ['file_id'=>$file_id, 'user_id'=>$user_id]);
    }

    /**
     * Метод сохранения файла
     * @param $user_id int Пользователь
     * @param $file_name string Имя файла
     * @param $data string Содержимое файла
     * @param $params array Доп. параметры
     * @return bool Результат
     */
    public function saveFile($user_id, $file_name, $data, $params){

        $actionParams = [
            'file_name'=>$file_name,
            'file_content' => $data,
            'user_id'=>$user_id,
        ] + $params;


        return \Yii::$app->runAction(self::ACTION_SAVE, $actionParams);
    }

}
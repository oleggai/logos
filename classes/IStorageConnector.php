<?php
/**
 * Created by PhpStorm.
 * User: goga
 * Date: 20.10.2015
 * Time: 17:36
 */

namespace app\classes;


interface IStorageConnector{


    /**
     * Метод инициализации
     * @return bool Результат инициализации
     */
    public function init();

    /**
     * Метод удаления файла
     * @param $user_id int Пользователь
     * @param $file_id int Файл
     * @return bool Результат
     */
    public function deleteFile($user_id,$file_id);

    /**
     * Метод получения файла
     * @param $user_id int Пользователь
     * @param $file_id int Файл
     * @return bool Результат
     */
    public function getFile($user_id,$file_id);

    /**
     * Метод сохранения файла
     * @param $user_id int Пользователь
     * @param $file_name string Имя файла
     * @param $data string Содержимое файла
     * @param $params array Доп. параметры
     * @return bool Результат
     */
    public function saveFile($user_id,$file_name, $data, $params);

}
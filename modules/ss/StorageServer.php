<?php

/**
 * Модуль работающий не посредственно с файлами
 * Использование:
 * 'modules' => [
 * 'storage' => [
 * 'class' => 'app\modules\ss\StorageServer',
 * ],
 * ],
 * @package app\modules\ss
 * @author Мельник И.А.
 * @category ПД
 */


namespace app\modules\ss;

use app\models\common\sys\SysFiles;
use yii\base\Module;

/**
 * Клас модуля
 * @package app\modules\ss
 */
class StorageServer extends Module
{
    public $controllerNamespace = 'app\modules\ss\controllers';
    public $filesPath = '';

    public static $baseFolder = 'own_files';
    public static $attachedFilesFolder = 'attach_docfiles';

    public function init()
    {
        // инициализация
        parent::init();
        $this->filesPath = $this->getFilesPath();
    }

    /**
     * @return string
     */
    public static function getFilesPath() {
        return StorageServer::$baseFolder.DIRECTORY_SEPARATOR.StorageServer::$attachedFilesFolder;
    }




    /**
     * Метод проверки прав пользователя на доступ к файлу. Если file_id = -1 , то проверка на создание файла
     * @param $user_id int Ид пользователя
     * @param $file_id int Ид файла
     * @return bool Результат проверки.  true - доступ разрешен
     */
    public function getUserFileAccess($user_id, $file_id){
        return true;
    }

    /**
     * Метод получения случайной строки
     * @param int $len Длинна строки
     * @return string Строка
     */
    public function getRandomString($len = 5){

        $random = substr( md5(rand()), 0, $len);
        return $random;
    }

    /**
     * Метод сохранения файла на сервере и в базе данных
     * @param $user_id int Ид пользователя
     * @param $file_name string Имя файла
     * @param $data string Содержимое файла
     * @param $params array Доп. парраметры
     * @return bool|int false или ид записи в таблице файлов
     */
    public function saveFile($user_id, $file_name, $data, $params){

        if (!$this->getUserFileAccess($user_id, -1))
            return false;

        $entityCode = $params['entity_code'];
        $entityId = $params['entity_id'];
        $random = $this->getRandomString();
        $extension = pathinfo($file_name)['extension'];


        // директория для хранения в таблице
        $dirPath = $entityCode . DIRECTORY_SEPARATOR . $entityId;
        // директория для файла
        $dirFullPath =
            \Yii::$app->basePath . DIRECTORY_SEPARATOR .
            $this->filesPath . DIRECTORY_SEPARATOR .
            $dirPath;

        // имя файла
        $fileName = $entityCode . '_' . $entityId . '_' . $random . '.' . $extension;

        // полный путь к файлу
        $filePath = $dirFullPath . DIRECTORY_SEPARATOR . $fileName;

        // создание директории
        if (!is_dir($dirFullPath) && !mkdir( $dirFullPath, 0777 , true))
            return false;

        // создание файла
        $file = fopen($filePath, "w");

        // запись содержимого файла в диск и в базу данных
        if ($file && fwrite($file, $data)){

            fclose($file);

            $sysFile = new SysFiles();
            $sysFile->file_name = $fileName;
            $sysFile->file_path = $dirPath;

            if ($sysFile->save())
                return $sysFile->id;

            unlink($filePath);
        }

        return false;
    }

    /**
     * Метод получения содерижмого файла
     * @param $user_id int Ид пользователя
     * @param $file_id int Ид файла
     * @return bool|string
     */
    public function getFile($user_id, $file_id){

        if (!$this->getUserFileAccess($user_id, $file_id))
            return false;

        $sysFile = SysFiles::findOne($file_id);
        if (!$sysFile)
            return false;

        // полный путь к файлу
        $filePath =
            \Yii::$app->basePath . DIRECTORY_SEPARATOR .
            $this->filesPath . DIRECTORY_SEPARATOR .
            $sysFile->file_path . DIRECTORY_SEPARATOR .
            $sysFile->file_name ;


        // чтение файла
        return file_get_contents ($filePath);
    }

    /**
     * Метод удаления файла
     * @param $user_id int Ид пользователя
     * @param $file_id int Ид файла
     * @return bool Результат
     */
    public function deleteFile($user_id, $file_id){

        if (!$this->getUserFileAccess($user_id, $file_id))
            return false;

        $sysFile = SysFiles::findOne($file_id);
        if (!$sysFile)
            return false;

        // полный путь к файлу
        $filePath =
            \Yii::$app->basePath . DIRECTORY_SEPARATOR .
            $this->filesPath . DIRECTORY_SEPARATOR .
            $sysFile->file_path . DIRECTORY_SEPARATOR .
            $sysFile->file_name ;

        return unlink($filePath) && $sysFile->delete();
    }


}

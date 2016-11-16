<?php
namespace app\classes\common;
/**
 * XlsxFile класс
 * Формирует и отдает excel файлы инвойса
 * Используется API контроллерами и контроллерами для формирования екселя
 */
class XlsxFile {
    /**
     * @var XlsxFile екземпляр класса
     */
    protected static $_instance;
    /**
     * @var string Имя файла-архива
     */
    public static $ZIP_NAME = '';

    /**
     * Конструктор класса XlsxFile
     * проводит инициализацию имени файла-архива
     */
    private function __construct() {
        XlsxFile::$ZIP_NAME = 'InvoicesArch'.time();
    }

    /**
     * Синглтон
     * @return XlsxFile
     */
    public static function getInstance() {
        // проверяем актуальность экземпляра
        if (self::$_instance === null) {
            // создаем новый экземпляр
            self::$_instance = new self();
        }
        // возвращаем созданный или существующий экземпляр
        return self::$_instance;
    }

    /**
     * @param $fileName
     * @param $objPHPExcel
     */
    public function archFile($fileName, $objPHPExcel) {

        $pathFile = XlsxFile::getPathFilesForArch();
        $pathArch = XlsxFile::getPathArchForSave();

        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save($pathFile.$fileName);

        $arch = new \ZipArchive();
        $arch->open($pathArch.XlsxFile::$ZIP_NAME, \ZipArchive::CREATE);
        if($arch->addFile($fileName)) {
            $arch->close();
            unlink($pathFile.$fileName);
        }
    }

    /**
     * Отдает сформированный архив пользователю
     */
    public function loadArchFile() {
        // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
        // если этого не сделать файл будет читаться в память полностью!
        // ob_get_level — Возвращает уровень вложенности механизма буферизации вывода
        if (ob_get_level()) {
            // ob_end_clean — Очищает (стирает) буфер вывода и отключает буферизацию вывода
            ob_end_clean();
        }
        XlsxFile::setHeaderForArch();
        // читаем файл и отправляем его пользователю
        if ($fd = fopen(XlsxFile::getPathArchForSave().XlsxFile::$ZIP_NAME, 'rb')) {
            // Проверяет, достигнут ли конец файла
            while (!feof($fd)) {
                print fread($fd, 1024);
            }
            // Если отдали файл, то удаляем его
            if(fclose($fd)) {
                unlink(XlsxFile::getPathArchForSave().XlsxFile::$ZIP_NAME);
            }
        }
        exit();
    }

    /**
     * Отдает сформированный ексель-файл пользователю
     * @param $fileName
     * @param $objPHPExcel
     * @throws \PHPExcel_Writer_Exception
     */
    public function xlsxFile($fileName, $objPHPExcel) {
        XlsxFile::setHeaderForXlsx($fileName);
        // Выводим содержимое файла
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('php://output');
        exit();
    }

    /**
     * Возвращает путь к создаваемому архиву
     * @return string
     */
    private static function getPathArchForSave() {
        $s = DIRECTORY_SEPARATOR;
        return \Yii::getAlias('@app').$s.'web'.$s;
    }

    /**
     * Возвращает путь к файл-ексель
     * @return string
     */
    private static function getPathFilesForArch() {
        $s = DIRECTORY_SEPARATOR;
        return \Yii::getAlias('@app').$s.'web'.$s;
    }

    /**
     * Установка заголовков для екселя
     * @param $fileName
     */
    private static function setHeaderForXlsx($fileName) {
        header ( "Expires: Mon, 1 Apr 2050 05:00:00 GMT" );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header ( 'Content-Disposition: attachment; filename='.$fileName);
    }

    /**
     * Установка заголовков для архива
     */
    private static function setHeaderForArch() {
    // http headers for zip downloads
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"".XlsxFile::$ZIP_NAME.'.zip'."\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize(XlsxFile::getPathArchForSave().XlsxFile::$ZIP_NAME));
    }

}
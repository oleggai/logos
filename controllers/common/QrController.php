<?php

namespace app\controllers\common;

use app\controllers\CommonController;
use Yii;
use dosamigos\qrcode\QrCode;
use yii\web\Controller;

/**
 * Контроллер для вывода qr-кода
 * @author Richok F.G.
 * @category qr
 */
class QrController extends Controller
{

    /**
     * Вывод qr-кода
     * @param string $text Строка для шифрования
     * @param integer $format Формат результата (1 - png, 2 - jpg, 3 - raw)
     * @return string Результирующая картинка
     */
    public function actionPrint($text, $format = 1) {
        switch ($format) {
            case 1: return QrCode::png($text, false, 0, 5);
            case 2: return QrCode::jpg($text, false, 0, 5);
            case 3: return QrCode::raw($text, false, 0, 5);
        }
        return null;
    }



    /**
     * Сохранения qr-кода в файл
     * @param string $file_id Уникальная часть имени сохраняемого файла
     * @param string $text Строка для шифрования
     * @param integer $format Формат результата (png, jpg, raw)
     * @return string Имя файла qrcod-а
     */
    static function createQrcodeFile($file_id,$text, $format = 'png')
    {
        // создаем временную папку для храненения файлов
        $qrcode_dir='../runtime/qrcodes/';
        if (!file_exists($qrcode_dir)) {
            mkdir($qrcode_dir, 0777, true);
        }

        $qrcode_file_name='qrcode_'.$file_id.'.'.$format;

        $fullfilename=$qrcode_dir.$qrcode_file_name;

        switch ($format) {
            case 'png': QrCode::png($text,$fullfilename, 0, 5);break;
            case 'jpg': QrCode::jpg($text,$fullfilename, 0, 5);break;
            case 'raw': QrCode::raw($text,$fullfilename, 0, 5);break;
        }


        return $fullfilename;

    }

}
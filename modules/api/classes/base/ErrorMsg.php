<?php

/**
 * Класс формирования ошибок для в ответах АПИ
 * формирует код и текст ошибки на основании переданного кода ошибки, который является ключем в ассоциативном массиве
 * массив значений ошибок хранится в отдельном файле ErrorMsgData.php
 *
 * Если текст ошибки обычная строка без параметров
 * ErrorMsg::GetError(410);
 *
 * Если нужно с параметрами
 * ErrorMsg::GetError(410,['3',345]);
 *
 * При этом строка в ErrorMsgData должна быть
 *
 * '410' => 'Not found %s EW in %d lines.',
 * по форматам смотри функцию  vsprintf
 */

namespace app\modules\api\classes\base;

class ErrorMsg {

    /**
     * возвращает пару значений - код ошибки, текст ошибки
     * @param $error_code
     * @return array
     */
    public static function GetError($error_code, $vars = []) {
        $error_for_return = [
            'error_code' => $error_code,
            'error_text' => 'Unknown error!'
        ];

        $errors_arr = include('ErrorMsgData.php');
        if (isset($errors_arr[$error_code]))
            if ($vars != [])
                $error_for_return = [
                    'error_code' => $error_code,
                    'error_text' => vsprintf($errors_arr[$error_code], $vars)
                ];
            else
                $error_for_return = [
                    'error_code' => $error_code,
                    'error_text' => $errors_arr[$error_code]
                ];

        return $error_for_return;
    }

}

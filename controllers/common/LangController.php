<?php

namespace app\controllers\common;

use Yii;
use yii\base\Exception;
use app\controllers\CommonController;

/**
 * Контроллер для перевода и транслитерации
 * @author Tochonyi DM
 * @category language
 */
class LangController extends CommonController
{

    /**
     * Перевод текста сервисом Яндекса
     * @param string $source язык исходного текста ('ru', 'en', 'uk')
     * @param string $target язык переведенного текста
     * @param string $text текст для перевода
     * @return string
     */
    public static function Translate($source, $target, $text)
    {
        try {
            return Yii::$app->translate->translate($source, $target, $text)['text'][0];
        }
        catch (Exception $e)
        {
            return $text;
        }
    }


    /**
     * Action для транслитерации текста
     * @param string $text исходный текст
     * @param string $source язык источника
     * @param string $target язык назначения
     * @return string
     */
    public static function actionTransliterate($text, $source, $target)
    {
        $res = LangController::Transliterate($text, $source, $target);
        return json_encode($res);
    }

    /**
     * Транслитерация текста
     * @param string $text исходный текст
     * @param string $source язык источника
     * @param string $target язык назначения
     * @return string
     */
    public static function Transliterate($text, $source, $target)
    {
        if ($text == '')
            return $text;

        try {
            if ($source == 'en') {
                if ($target == 'ru')
                    return LangController::TranslitEnRu($text);
                if ($target == 'uk')
                    return LangController::TranslitEnUk($text);
            }

            if ($source == 'ru') {
                if ($target == 'en')
                    //return Yii::$app->translit->process($text, '', $source);
                    return LangController::TranslitRuEn($text);
                if ($target == 'uk')
                    return LangController::TranslitRuUk($text);
            }

            if ($source == 'uk') {
                if ($target == 'en')
                    //return Yii::$app->translit->process($text, '', $source);
                    return LangController::TranslitUkEn($text);
                if ($target == 'ru')
                    return LangController::TranslitUkRu($text);
            }

            return $text;
        }
        catch (Exception $e)
        {
            return $text;
        }
    }

    private static $vowelEnS = ['a', 'e', 'i', '0', 'u', 'y'];
    private static $vowelEnB = ['A', 'E', 'I', 'O', 'U', 'Y'];

    private static $eiy_vowelEnS = ['e', 'i', 'y'];
    private static $EIY_vowelEnB = ['E', 'I', 'Y'];

    private static $consonantEnS = ['b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'];
    private static $consonantEnB = ['B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Y', 'Z'];

    private static $vowelRuS = ['а', 'у', 'о', 'ы', 'и', 'э', 'я', 'ю', 'ё', 'е'];
    private static $vowelRuB = ['А', 'У', 'О', 'Ы', 'И', 'Э', 'Я', 'Ю', 'Ё', 'Е'];

    private static $vowelUkS = ['а', 'е', 'є', 'и', 'і', 'ї', 'о', 'у', 'ю', 'я'];
    private static $vowelUkB = ['А', 'Е', 'Є', 'И', 'І', 'Ї', 'О', 'У', 'Ю', 'Я'];


    private static function TranslitUkEn($text)
    {
        //меняем кривой апостроф на нормальный
        $text = str_replace("'",'’',$text);
        $text = str_replace('`','’',$text);
        $text = str_replace('´','’',$text);
        $text = str_replace('ʾ','’',$text);
        $text = str_replace('ʿ','’',$text);

        $text = ' '.$text;

        $text = str_replace(' є',' ye',$text);
        $text = str_replace(' Є',' Ye',$text);

        $text = str_replace('ьє','ьye',$text);
        $text = str_replace('Ьє','Ьye',$text);
        $text = str_replace('ЬЄ','ЬYE',$text);

        $text = str_replace('’є','ye',$text);
        $text = str_replace('’Є','YE',$text);

        foreach(LangController::$vowelUkS as $letter)
            $text = str_replace($letter."є",$letter."ye",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."є",$letter."ye",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."Є",$letter."YE",$text);

        $text = str_replace('є','ie',$text);
        $text = str_replace('Є','Ie',$text);


        $text = str_replace(' ї',' yi',$text);
        $text = str_replace(' Ї',' Yi',$text);

        $text = str_replace('ьї','ьyi',$text);
        $text = str_replace('Ьї','Ьyi',$text);
        $text = str_replace('ЬЇ','ЬYI',$text);

        $text = str_replace('’ї','yi',$text);
        $text = str_replace('’Ї','YI',$text);

        foreach(LangController::$vowelUkS as $letter)
            $text = str_replace($letter."ї",$letter."yi",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."ї",$letter."yi",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."Ї",$letter."YI",$text);

        $text = str_replace('ї','i',$text);
        $text = str_replace('Ї','I',$text);


        $text = str_replace(' й',' y',$text);
        $text = str_replace(' Й',' Y',$text);

        $text = str_replace('ьй','ьy',$text);
        $text = str_replace('Ьй','Ьy',$text);
        $text = str_replace('ЬЙ','ЬY',$text);

        $text = str_replace('’й','y',$text);
        $text = str_replace('’Й','Y',$text);

        foreach(LangController::$vowelUkS as $letter)
            $text = str_replace($letter."й",$letter."y",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."й",$letter."y",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."Й",$letter."Y",$text);

        $text = str_replace('й','i',$text);
        $text = str_replace('Й','I',$text);


        $text = str_replace(' ю',' yu',$text);
        $text = str_replace(' Ю',' Yu',$text);

        $text = str_replace('ью','ьyu',$text);
        $text = str_replace('Ью','Ьyu',$text);
        $text = str_replace('ЬЮ','ЬYU',$text);

        $text = str_replace('’ю','yu',$text);
        $text = str_replace('’Ю','YU',$text);

        foreach(LangController::$vowelUkS as $letter)
            $text = str_replace($letter."ю",$letter."yu",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."ю",$letter."yu",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."Ю",$letter."YU",$text);

        $text = str_replace('ю','iu',$text);
        $text = str_replace('Ю','Iu',$text);


        $text = str_replace(' я',' ya',$text);
        $text = str_replace(' Я',' Ya',$text);

        $text = str_replace('ья','ьya',$text);
        $text = str_replace('Ья','Ьya',$text);
        $text = str_replace('ЬЯ','ЬYA',$text);

        $text = str_replace('’я','ya',$text);
        $text = str_replace('’Я','YA',$text);

        foreach(LangController::$vowelUkS as $letter)
            $text = str_replace($letter."я",$letter."ya",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."я",$letter."ya",$text);
        foreach(LangController::$vowelUkB as $letter)
            $text = str_replace($letter."Я",$letter."YA",$text);

        $text = str_replace('я','ia',$text);
        $text = str_replace('Я','Ia',$text);


        $text = str_replace('а','a',$text);
        $text = str_replace('б','b',$text);
        $text = str_replace('в','v',$text);
        $text = str_replace('г','h',$text);
        $text = str_replace('ґ','g',$text);
        $text = str_replace('д','d',$text);
        $text = str_replace('е','e',$text);
        $text = str_replace('ж','zh',$text);
        $text = str_replace('з','z',$text);
        $text = str_replace('и','y',$text);
        $text = str_replace('і','i',$text);
        $text = str_replace('к','k',$text);
        $text = str_replace('л','l',$text);
        $text = str_replace('м','m',$text);
        $text = str_replace('н','n',$text);
        $text = str_replace('о','o',$text);
        $text = str_replace('п','p',$text);
        $text = str_replace('р','r',$text);
        $text = str_replace('с','s',$text);
        $text = str_replace('т','t',$text);
        $text = str_replace('у','u',$text);
        $text = str_replace('ф','f',$text);
        $text = str_replace('х','kh',$text);
        $text = str_replace('ц','ts',$text);
        $text = str_replace('ч','ch',$text);
        $text = str_replace('ш','sh',$text);
        $text = str_replace('щ','shch',$text);
        $text = str_replace('’','',$text);
        $text = str_replace('ь','’',$text);

        $text = str_replace('А','A',$text);
        $text = str_replace('Б','B',$text);
        $text = str_replace('В','V',$text);
        $text = str_replace('Г','H',$text);
        $text = str_replace('Ґ','G',$text);
        $text = str_replace('Д','D',$text);
        $text = str_replace('Е','E',$text);
        $text = str_replace('Ж','Zh',$text);
        $text = str_replace('З','Z',$text);
        $text = str_replace('И','Y',$text);
        $text = str_replace('І','I',$text);
        $text = str_replace('К','K',$text);
        $text = str_replace('Л','L',$text);
        $text = str_replace('М','M',$text);
        $text = str_replace('Н','N',$text);
        $text = str_replace('О','O',$text);
        $text = str_replace('П','P',$text);
        $text = str_replace('Р','R',$text);
        $text = str_replace('С','S',$text);
        $text = str_replace('Т','T',$text);
        $text = str_replace('У','U',$text);
        $text = str_replace('Ф','F',$text);
        $text = str_replace('Х','Kh',$text);
        $text = str_replace('Ц','Ts',$text);
        $text = str_replace('Ч','Ch',$text);
        $text = str_replace('Ш','Sh',$text);
        $text = str_replace('Щ','Shch',$text);
        $text = str_replace('Ь','’',$text);

        $text = substr($text, 1);
        return $text;
    }

    private static function TranslitRuEn($text)
    {
        $text = ' '.$text;

        $text = str_replace('жи','zhy',$text);
        $text = str_replace('Жи','Zhy',$text);
        $text = str_replace('ЖИ','ZHY',$text);

        $text = str_replace('ши','shy',$text);
        $text = str_replace('Ши','Shy',$text);
        $text = str_replace('ШИ','SHY',$text);

        $text = str_replace('ци','tsy',$text);
        $text = str_replace('Ци','Tsy',$text);
        $text = str_replace('ЦИ','TSY',$text);


        $text = str_replace(' ё',' yo',$text);
        $text = str_replace(' Ё',' Yo',$text);

        $text = str_replace('ьё','ьyo',$text);
        $text = str_replace('Ьё','Ьyo',$text);
        $text = str_replace('ЬЁ','ЬYO',$text);

        $text = str_replace('ъё','ъyo',$text);
        $text = str_replace('Ъё','Ъyo',$text);
        $text = str_replace('ЪЁ','ЪYO',$text);

        foreach(LangController::$vowelRuS as $letter)
            $text = str_replace($letter."ё",$letter."yo",$text);
        foreach(LangController::$vowelRuB as $letter)
            $text = str_replace($letter."ё",$letter."yo",$text);
        foreach(LangController::$vowelRuB as $letter)
            $text = str_replace($letter."Ё",$letter."YO",$text);

        $text = str_replace('ё','io',$text);
        $text = str_replace('Ё','Io',$text);


        $text = str_replace(' й',' y',$text);
        $text = str_replace(' Й',' Y',$text);

        $text = str_replace('ьй','ьy',$text);
        $text = str_replace('Ьй','Ьy',$text);
        $text = str_replace('ЬЙ','ЬY',$text);

        $text = str_replace('ъй','ъy',$text);
        $text = str_replace('Ъй','Ъy',$text);
        $text = str_replace('ЪЙ','ЪY',$text);

        foreach(LangController::$vowelRuS as $letter)
            $text = str_replace($letter."й",$letter."y",$text);
        foreach(LangController::$vowelRuB as $letter)
            $text = str_replace($letter."й",$letter."y",$text);
        foreach(LangController::$vowelRuB as $letter)
            $text = str_replace($letter."Й",$letter."Y",$text);

        $text = str_replace('й','i',$text);
        $text = str_replace('Й','I',$text);


        $text = str_replace(' ю',' yu',$text);
        $text = str_replace(' Ю',' Yu',$text);

        $text = str_replace('ью','ьyu',$text);
        $text = str_replace('Ью','Ьyu',$text);
        $text = str_replace('ЬЮ','ЬYU',$text);

        $text = str_replace('ъю','ъyu',$text);
        $text = str_replace('Ъю','Ъyu',$text);
        $text = str_replace('ЪЮ','ЪYU',$text);

        foreach(LangController::$vowelRuS as $letter)
            $text = str_replace($letter."ю",$letter."yu",$text);
        foreach(LangController::$vowelRuB as $letter)
            $text = str_replace($letter."ю",$letter."yu",$text);
        foreach(LangController::$vowelRuB as $letter)
            $text = str_replace($letter."Ю",$letter."YU",$text);

        $text = str_replace('ю','iu',$text);
        $text = str_replace('Ю','Iu',$text);


        $text = str_replace(' я',' ya',$text);
        $text = str_replace(' Я',' Ya',$text);

        $text = str_replace('ья','ьya',$text);
        $text = str_replace('Ья','Ьya',$text);
        $text = str_replace('ЬЯ','ЬYA',$text);

        $text = str_replace('ъя','ъya',$text);
        $text = str_replace('Ъя','Ъya',$text);
        $text = str_replace('ЪЯ','ЪYA',$text);

        foreach(LangController::$vowelRuS as $letter)
            $text = str_replace($letter."я",$letter."ya",$text);
        foreach(LangController::$vowelRuB as $letter)
            $text = str_replace($letter."я",$letter."ya",$text);
        foreach(LangController::$vowelRuB as $letter)
            $text = str_replace($letter."Я",$letter."YA",$text);

        $text = str_replace('я','ia',$text);
        $text = str_replace('Я','Ia',$text);


        $text = str_replace('а','a',$text);
        $text = str_replace('б','b',$text);
        $text = str_replace('в','v',$text);
        $text = str_replace('г','g',$text);
        $text = str_replace('д','d',$text);
        $text = str_replace('е','e',$text);
        $text = str_replace('ж','zh',$text);
        $text = str_replace('з','z',$text);
        $text = str_replace('и','i',$text);
        $text = str_replace('к','k',$text);
        $text = str_replace('л','l',$text);
        $text = str_replace('м','m',$text);
        $text = str_replace('н','n',$text);
        $text = str_replace('о','o',$text);
        $text = str_replace('п','p',$text);
        $text = str_replace('р','r',$text);
        $text = str_replace('с','s',$text);
        $text = str_replace('т','t',$text);
        $text = str_replace('у','u',$text);
        $text = str_replace('ф','f',$text);
        $text = str_replace('х','kh',$text);
        $text = str_replace('ц','ts',$text);
        $text = str_replace('ч','ch',$text);
        $text = str_replace('ш','sh',$text);
        $text = str_replace('щ','shch',$text);
        $text = str_replace('ь','’',$text);
        $text = str_replace('ы','y',$text);
        $text = str_replace('ъ','’',$text);
        $text = str_replace('э','e',$text);

        $text = str_replace('А','A',$text);
        $text = str_replace('Б','B',$text);
        $text = str_replace('В','V',$text);
        $text = str_replace('Г','G',$text);
        $text = str_replace('Д','D',$text);
        $text = str_replace('Е','E',$text);
        $text = str_replace('Ж','Zh',$text);
        $text = str_replace('З','Z',$text);
        $text = str_replace('И','I',$text);
        $text = str_replace('К','K',$text);
        $text = str_replace('Л','L',$text);
        $text = str_replace('М','M',$text);
        $text = str_replace('Н','N',$text);
        $text = str_replace('О','O',$text);
        $text = str_replace('П','P',$text);
        $text = str_replace('Р','R',$text);
        $text = str_replace('С','S',$text);
        $text = str_replace('Т','T',$text);
        $text = str_replace('У','U',$text);
        $text = str_replace('Ф','F',$text);
        $text = str_replace('Х','Kh',$text);
        $text = str_replace('Ц','Ts',$text);
        $text = str_replace('Ч','Ch',$text);
        $text = str_replace('Ш','Sh',$text);
        $text = str_replace('Щ','Shch',$text);
        $text = str_replace('Ь','’',$text);
        $text = str_replace('Ы','Y',$text);
        $text = str_replace('Ъ','’',$text);
        $text = str_replace('Э','E',$text);

        $text = substr($text, 1);
        return $text;
    }

    private static function TranslitEnRu($text)
    {
        //меняем кривой апостроф на нормальный
        $text = str_replace("'",'’',$text);
        $text = str_replace('`','’',$text);
        $text = str_replace('´','’',$text);
        $text = str_replace('ʾ','’',$text);
        $text = str_replace('ʿ','’',$text);

        $text = $text.' ';

        $text = str_replace("shtch","щ",$text);
        $text = str_replace("Shtch","Щ",$text);
        $text = str_replace("SHTCH","Щ",$text);

        $text = str_replace("shch","щ",$text);
        $text = str_replace("Shch","Щ",$text);
        $text = str_replace("SHCH","Щ",$text);

        $text = str_replace("air","эа",$text);
        $text = str_replace("Air","Эа",$text);
        $text = str_replace("AIR","ЭА",$text);

        $text = str_replace("are","эа",$text);
        $text = str_replace("Are","Эа",$text);
        $text = str_replace("ARE","ЭА",$text);

        $text = str_replace("ear","эа",$text);
        $text = str_replace("Ear","Эа",$text);
        $text = str_replace("EAR","ЭА",$text);

        $text = str_replace("eer","эа",$text);
        $text = str_replace("Eer","Эа",$text);
        $text = str_replace("EER","ЭА",$text);

        $text = str_replace("all","ол",$text);
        $text = str_replace("All","Ол",$text);
        $text = str_replace("ALL","ОЛ",$text);

        $text = str_replace("ere","иэ",$text);
        $text = str_replace("Ere","Иэ",$text);
        $text = str_replace("ERE","ИЭ",$text);

        $text = str_replace("ier","иэ",$text);
        $text = str_replace("Ier","Иэ",$text);
        $text = str_replace("IER","ИЭ",$text);

        $text = str_replace("ath","аз",$text);
        $text = str_replace("Ath","Аз",$text);
        $text = str_replace("ATH","АЗ",$text);

        $text = str_replace("ee","и",$text);
        $text = str_replace("Ee","И",$text);
        $text = str_replace("EE","И",$text);

        $text = str_replace("ea","и",$text);
        $text = str_replace("Ea","И",$text);
        $text = str_replace("EA","И",$text);

        $text = str_replace("ar","ар",$text);
        $text = str_replace("Ar","Ар",$text);
        $text = str_replace("AR","АР",$text);

        $text = str_replace("ur","ер",$text);
        $text = str_replace("Ur","Ер",$text);
        $text = str_replace("UR","ЕР",$text);

        $text = str_replace("ir","ер",$text);
        $text = str_replace("Ir","Ер",$text);
        $text = str_replace("IR","ЕР",$text);

        $text = str_replace("er","ер",$text);
        $text = str_replace("Er","Ер",$text);
        $text = str_replace("ER","ЕР",$text);

        $text = str_replace("oo","у",$text);
        $text = str_replace("Oo","У",$text);
        $text = str_replace("OO","У",$text);

        $text = str_replace("ou","оу",$text);
        $text = str_replace("Ou","Оу",$text);
        $text = str_replace("OU","ОУ",$text);

        $text = str_replace("ai","эй",$text);
        $text = str_replace("Ai","Эй",$text);
        $text = str_replace("AI","ЭЙ",$text);

        $text = str_replace("ei","эй",$text);
        $text = str_replace("Ei","Эй",$text);
        $text = str_replace("EI","ЭЙ",$text);

        $text = str_replace("au","ау",$text);
        $text = str_replace("Au","Ау",$text);
        $text = str_replace("AU","АУ",$text);

        $text = str_replace("ts","ц",$text);
        $text = str_replace("Ts","Ц",$text);
        $text = str_replace("TS","Ц",$text);

        $text = str_replace("th","з",$text);
        $text = str_replace("Th","З",$text);
        $text = str_replace("TH","З",$text);

        $text = str_replace("sh","ш",$text);
        $text = str_replace("Sh","Ш",$text);
        $text = str_replace("SH","Ш",$text);

        $text = str_replace("ch","ч",$text);
        $text = str_replace("Ch","Ч",$text);
        $text = str_replace("CH","Ч",$text);

        $text = str_replace("ya","я",$text);
        $text = str_replace("Ya","Я",$text);
        $text = str_replace("YA","Я",$text);

        $text = str_replace("ye","е",$text);
        $text = str_replace("Ye","Е",$text);
        $text = str_replace("YE","Е",$text);

        $text = str_replace("yi","йи",$text);
        $text = str_replace("Yi","Йи",$text);
        $text = str_replace("YI","ЙИ",$text);

        $text = str_replace("kh","х",$text);
        $text = str_replace("Kh","Х",$text);
        $text = str_replace("KH","Х",$text);

        $text = str_replace("yu","ю",$text);
        $text = str_replace("Yu","Ю",$text);
        $text = str_replace("YU","Ю",$text);

        $text = str_replace("e "," ",$text);
        $text = str_replace("E "," ",$text);

        $text = str_replace("ey ","и ",$text);
        $text = str_replace("Ey ","И ",$text);
        $text = str_replace("EY ","И ",$text);

        foreach(LangController::$vowelEnS as $letter)
            $text = str_replace("q".$letter,"кв".$letter,$text);
        foreach(LangController::$vowelEnS as $letter)
            $text = str_replace("Q".$letter,"Кв".$letter,$text);
        foreach(LangController::$vowelEnB as $letter)
            $text = str_replace("Q".$letter,"КВ".$letter,$text);
        $text = str_replace("q","к",$text);
        $text = str_replace("Q","К",$text);

        foreach(LangController::$eiy_vowelEnS as $letter)
            $text = str_replace("c".$letter,"с".$letter,$text);
        foreach(LangController::$eiy_vowelEnS as $letter)
            $text = str_replace("C".$letter,"С".$letter,$text);
        foreach(LangController::$EIY_vowelEnB as $letter)
            $text = str_replace("C".$letter,"С".$letter,$text);
        $text = str_replace("c","к",$text);
        $text = str_replace("C","К",$text);

        foreach(LangController::$eiy_vowelEnS as $letter)
            $text = str_replace("g".$letter,"дж".$letter,$text);
        foreach(LangController::$eiy_vowelEnS as $letter)
            $text = str_replace("G".$letter,"Дж".$letter,$text);
        foreach(LangController::$EIY_vowelEnB as $letter)
            $text = str_replace("G".$letter,"ДЖ".$letter,$text);
        $text = str_replace("g","г",$text);
        $text = str_replace("G","Г",$text);

        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("u".$letter_c.$letter_v,"ю".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("U".$letter_c.$letter_v,"Ю".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnB as $letter_c)
            foreach(LangController::$vowelEnB as $letter_v)
                $text = str_replace("U".$letter_c.$letter_v,"Ю".$letter_c.$letter_v,$text);
        $text = str_replace("u","а",$text);
        $text = str_replace("U","А",$text);

        foreach(LangController::$vowelEnS as $letter)
            $text = str_replace("y".$letter,"й".$letter,$text);
        foreach(LangController::$vowelEnS as $letter)
            $text = str_replace("Y".$letter,"Й".$letter,$text);
        foreach(LangController::$vowelEnB as $letter)
            $text = str_replace("Y".$letter,"Й".$letter,$text);
        $text = str_replace("y","ы",$text);
        $text = str_replace("Y","Ы",$text);

        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("a".$letter_c.$letter_v,"эй".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("A".$letter_c.$letter_v,"Эй".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnB as $letter_c)
            foreach(LangController::$vowelEnB as $letter_v)
                $text = str_replace("A".$letter_c.$letter_v,"ЭЙ".$letter_c.$letter_v,$text);
        $text = str_replace("a","а",$text);
        $text = str_replace("A","А",$text);

        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("o".$letter_c.$letter_v,"оу".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("O".$letter_c.$letter_v,"Оу".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnB as $letter_c)
            foreach(LangController::$vowelEnB as $letter_v)
                $text = str_replace("O".$letter_c.$letter_v,"ОУ".$letter_c.$letter_v,$text);
        $text = str_replace("o","о",$text);
        $text = str_replace("O","О",$text);


        $text = str_replace("’","ь",$text);
        $text = str_replace("b","б",$text);
        $text = str_replace("d","д",$text);
        $text = str_replace("e","е",$text);
        $text = str_replace("f","ф",$text);
        $text = str_replace("h","х",$text);
        $text = str_replace("i","и",$text);
        $text = str_replace("j","дж",$text);
        $text = str_replace("k","к",$text);
        $text = str_replace("l","л",$text);
        $text = str_replace("m","м",$text);
        $text = str_replace("n","н",$text);
        $text = str_replace("p","п",$text);
        $text = str_replace("r","р",$text);
        $text = str_replace("s","с",$text);
        $text = str_replace("t","т",$text);
        $text = str_replace("v","в",$text);
        $text = str_replace("w","в",$text);
        $text = str_replace("x","кс",$text);
        $text = str_replace("z","з",$text);

        $text = str_replace("B","Б",$text);
        $text = str_replace("D","Д",$text);
        $text = str_replace("E","Е",$text);
        $text = str_replace("F","Ф",$text);
        $text = str_replace("H","Х",$text);
        $text = str_replace("I","И",$text);
        $text = str_replace("J","Дж",$text);
        $text = str_replace("K","К",$text);
        $text = str_replace("L","Л",$text);
        $text = str_replace("M","М",$text);
        $text = str_replace("N","Н",$text);
        $text = str_replace("P","П",$text);
        $text = str_replace("R","Р",$text);
        $text = str_replace("S","С",$text);
        $text = str_replace("T","Т",$text);
        $text = str_replace("V","В",$text);
        $text = str_replace("W","В",$text);
        $text = str_replace("X","Кс",$text);
        $text = str_replace("Z","З",$text);

        $text = substr($text, 0, strlen($text) - 1);
        return $text;
    }

    private static function TranslitEnUk($text)
    {
        //меняем кривой апостроф на нормальный
        $text = str_replace("'",'’',$text);
        $text = str_replace('`','’',$text);
        $text = str_replace('´','’',$text);
        $text = str_replace('ʾ','’',$text);
        $text = str_replace('ʿ','’',$text);

        $text = $text.' ';

        $text = str_replace("shtch","щ",$text);
        $text = str_replace("Shtch","Щ",$text);
        $text = str_replace("SHTCH","Щ",$text);

        $text = str_replace("shch","щ",$text);
        $text = str_replace("Shch","Щ",$text);
        $text = str_replace("SHCH","Щ",$text);

        $text = str_replace("air","еа",$text);
        $text = str_replace("Air","Еа",$text);
        $text = str_replace("AIR","ЕА",$text);

        $text = str_replace("are","еа",$text);
        $text = str_replace("Are","Еа",$text);
        $text = str_replace("ARE","ЕА",$text);

        $text = str_replace("ear","еа",$text);
        $text = str_replace("Ear","Еа",$text);
        $text = str_replace("EAR","ЕА",$text);

        $text = str_replace("eer","еа",$text);
        $text = str_replace("Eer","Еа",$text);
        $text = str_replace("EER","ЕА",$text);

        $text = str_replace("all","ол",$text);
        $text = str_replace("All","Ол",$text);
        $text = str_replace("ALL","ОЛ",$text);

        $text = str_replace("ere","іе",$text);
        $text = str_replace("Ere","Іе",$text);
        $text = str_replace("ERE","ІЕ",$text);

        $text = str_replace("ier","іе",$text);
        $text = str_replace("Ier","Іе",$text);
        $text = str_replace("IER","ІЕ",$text);

        $text = str_replace("ath","аз",$text);
        $text = str_replace("Ath","Аз",$text);
        $text = str_replace("ATH","АЗ",$text);

        $text = str_replace("ee","і",$text);
        $text = str_replace("Ee","І",$text);
        $text = str_replace("EE","І",$text);

        $text = str_replace("ea","і",$text);
        $text = str_replace("Ea","І",$text);
        $text = str_replace("EA","І",$text);

        $text = str_replace("ar","ар",$text);
        $text = str_replace("Ar","Ар",$text);
        $text = str_replace("AR","АР",$text);

        $text = str_replace("ur","ьор",$text);
        $text = str_replace("Ur","Ьор",$text);
        $text = str_replace("UR","ЬОР",$text);

        $text = str_replace("ir","ьор",$text);
        $text = str_replace("Ir","Ьор",$text);
        $text = str_replace("IR","ЬОР",$text);

        $text = str_replace("er","ьор",$text);
        $text = str_replace("Er","Ьор",$text);
        $text = str_replace("ER","ЬОР",$text);

        $text = str_replace("oo","у",$text);
        $text = str_replace("Oo","У",$text);
        $text = str_replace("OO","У",$text);

        $text = str_replace("ou","оу",$text);
        $text = str_replace("Ou","Оу",$text);
        $text = str_replace("OU","ОУ",$text);

        $text = str_replace("ai","ей",$text);
        $text = str_replace("Ai","Ей",$text);
        $text = str_replace("AI","ЕЙ",$text);

        $text = str_replace("ei","ей",$text);
        $text = str_replace("Ei","Ей",$text);
        $text = str_replace("EI","ЕЙ",$text);

        $text = str_replace("au","ау",$text);
        $text = str_replace("Au","Ау",$text);
        $text = str_replace("AU","АУ",$text);

        $text = str_replace("ts","ц",$text);
        $text = str_replace("Ts","Ц",$text);
        $text = str_replace("TS","Ц",$text);

        $text = str_replace("th","з",$text);
        $text = str_replace("Th","З",$text);
        $text = str_replace("TH","З",$text);

        $text = str_replace("sh","ш",$text);
        $text = str_replace("Sh","Ш",$text);
        $text = str_replace("SH","Ш",$text);

        $text = str_replace("ch","ч",$text);
        $text = str_replace("Ch","Ч",$text);
        $text = str_replace("CH","Ч",$text);

        $text = str_replace("ya","я",$text);
        $text = str_replace("Ya","Я",$text);
        $text = str_replace("YA","Я",$text);

        $text = str_replace("ye","є",$text);
        $text = str_replace("Ye","Є",$text);
        $text = str_replace("YE","Є",$text);

        $text = str_replace("yi","ї",$text);
        $text = str_replace("Yi","Ї",$text);
        $text = str_replace("YI","Ї",$text);

        $text = str_replace("kh","х",$text);
        $text = str_replace("Kh","Х",$text);
        $text = str_replace("KH","Х",$text);

        $text = str_replace("yu","ю",$text);
        $text = str_replace("Yu","Ю",$text);
        $text = str_replace("YU","Ю",$text);

        $text = str_replace("e "," ",$text);
        $text = str_replace("E "," ",$text);

        $text = str_replace("ey ","і ",$text);
        $text = str_replace("Ey ","І ",$text);
        $text = str_replace("EY ","І ",$text);


        foreach(LangController::$vowelEnS as $letter)
            $text = str_replace("q".$letter,"кв",$text);
        foreach(LangController::$vowelEnS as $letter)
            $text = str_replace("Q".$letter,"Кв",$text);
        foreach(LangController::$vowelEnB as $letter)
            $text = str_replace("Q".$letter,"КВ",$text);
        $text = str_replace("q","к",$text);
        $text = str_replace("Q","К",$text);

        foreach(LangController::$eiy_vowelEnS as $letter)
            $text = str_replace("c".$letter,"с".$letter,$text);
        foreach(LangController::$eiy_vowelEnS as $letter)
            $text = str_replace("C".$letter,"С".$letter,$text);
        foreach(LangController::$EIY_vowelEnB as $letter)
            $text = str_replace("C".$letter,"С".$letter,$text);
        $text = str_replace("c","к",$text);
        $text = str_replace("C","К",$text);

        foreach(LangController::$eiy_vowelEnS as $letter)
            $text = str_replace("g".$letter,"дж".$letter,$text);
        foreach(LangController::$eiy_vowelEnS as $letter)
            $text = str_replace("G".$letter,"Дж".$letter,$text);
        foreach(LangController::$EIY_vowelEnB as $letter)
            $text = str_replace("G".$letter,"ДЖ".$letter,$text);
        $text = str_replace("g","г",$text);
        $text = str_replace("G","Г",$text);

        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("u".$letter_c.$letter_v,"ю".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("U".$letter_c.$letter_v,"Ю".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnB as $letter_c)
            foreach(LangController::$vowelEnB as $letter_v)
                $text = str_replace("U".$letter_c.$letter_v,"Ю".$letter_c.$letter_v,$text);
        $text = str_replace("u","а",$text);
        $text = str_replace("U","А",$text);

        foreach(LangController::$vowelEnS as $letter)
            $text = str_replace("y".$letter,"й".$letter,$text);
        foreach(LangController::$vowelEnS as $letter)
            $text = str_replace("Y".$letter,"Й".$letter,$text);
        foreach(LangController::$vowelEnB as $letter)
            $text = str_replace("Y".$letter,"Й".$letter,$text);
        $text = str_replace("y","и",$text);
        $text = str_replace("Y","И",$text);

        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("a".$letter_c.$letter_v,"ей".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("A".$letter_c.$letter_v,"Ей".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnB as $letter_c)
            foreach(LangController::$vowelEnB as $letter_v)
                $text = str_replace("A".$letter_c.$letter_v,"ЕЙ".$letter_c.$letter_v,$text);
        $text = str_replace("a","а",$text);
        $text = str_replace("A","А",$text);

        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("o".$letter_c.$letter_v,"оу".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnS as $letter_c)
            foreach(LangController::$vowelEnS as $letter_v)
                $text = str_replace("O".$letter_c.$letter_v,"Оу".$letter_c.$letter_v,$text);
        foreach(LangController::$consonantEnB as $letter_c)
            foreach(LangController::$vowelEnB as $letter_v)
                $text = str_replace("O".$letter_c.$letter_v,"ОУ".$letter_c.$letter_v,$text);
        $text = str_replace("o","о",$text);
        $text = str_replace("O","О",$text);


        $text = str_replace("’","ь",$text);
        $text = str_replace("b","б",$text);
        $text = str_replace("d","д",$text);
        $text = str_replace("e","є",$text);
        $text = str_replace("f","ф",$text);
        $text = str_replace("h","х",$text);
        $text = str_replace("i","і",$text);
        $text = str_replace("j","дж",$text);
        $text = str_replace("k","к",$text);
        $text = str_replace("l","л",$text);
        $text = str_replace("m","м",$text);
        $text = str_replace("n","н",$text);
        $text = str_replace("p","п",$text);
        $text = str_replace("r","р",$text);
        $text = str_replace("s","с",$text);
        $text = str_replace("t","т",$text);
        $text = str_replace("v","в",$text);
        $text = str_replace("w","в",$text);
        $text = str_replace("x","кс",$text);
        $text = str_replace("z","з",$text);

        $text = str_replace("B","Б",$text);
        $text = str_replace("D","Д",$text);
        $text = str_replace("E","Є",$text);
        $text = str_replace("F","Ф",$text);
        $text = str_replace("H","Х",$text);
        $text = str_replace("I","І",$text);
        $text = str_replace("J","Дж",$text);
        $text = str_replace("K","К",$text);
        $text = str_replace("L","Л",$text);
        $text = str_replace("M","М",$text);
        $text = str_replace("N","Н",$text);
        $text = str_replace("P","П",$text);
        $text = str_replace("R","Р",$text);
        $text = str_replace("S","С",$text);
        $text = str_replace("T","Т",$text);
        $text = str_replace("V","В",$text);
        $text = str_replace("W","В",$text);
        $text = str_replace("X","Кс",$text);
        $text = str_replace("Z","З",$text);

        $text = substr($text, 0, strlen($text) - 1);
        return $text;
    }

    private static function TranslitRuUk($text)
    {
        $text = ' '.$text;

        $text = str_replace(' ё',' йо',$text);
        $text = str_replace(' Ё',' Йо',$text);

        $text = str_replace('аё','айо',$text);
        $text = str_replace('Аё','Айо',$text);
        $text = str_replace('АЁ','АЙО',$text);

        $text = str_replace('её','ейо',$text);
        $text = str_replace('Её','Ейо',$text);
        $text = str_replace('ЕЁ','ЕЙО',$text);

        $text = str_replace('иё','ийо',$text);
        $text = str_replace('Иё','Ийо',$text);
        $text = str_replace('ИЁ','ИЙО',$text);

        $text = str_replace('оё','ойо',$text);
        $text = str_replace('Оё','Ойо',$text);
        $text = str_replace('ОЁ','ОЙО',$text);

        $text = str_replace('уё','уйо',$text);
        $text = str_replace('Уё','Уйо',$text);
        $text = str_replace('УЁ','УЙО',$text);

        $text = str_replace('ыё','ыйо',$text);
        $text = str_replace('Ыё','Ыйо',$text);
        $text = str_replace('ЫЁ','ЫЙО',$text);

        $text = str_replace('эё','эйо',$text);
        $text = str_replace('Эё','Эйо',$text);
        $text = str_replace('ЭЁ','ЭЙО',$text);

        $text = str_replace('юё','юйо',$text);
        $text = str_replace('Юё','Юйо',$text);
        $text = str_replace('ЮЁ','ЮЙО',$text);

        $text = str_replace('яё','яйо',$text);
        $text = str_replace('Яё','Яйо',$text);
        $text = str_replace('ЯЁ','ЯЙО',$text);

        $text = str_replace('ьё','ьйо',$text);
        $text = str_replace('ЬЁ','ЬЙО',$text);

        $text = str_replace('ъё','ъйо',$text);
        $text = str_replace('ЪЁ','ЪЙО',$text);

        $text = str_replace('ё','ьо',$text);
        $text = str_replace('Ё','ЬО',$text);

        $text = str_replace('и','і',$text);
        $text = str_replace('И','І',$text);
        $text = str_replace('ы','и',$text);
        $text = str_replace('Ы','И',$text);
        $text = str_replace('э','е',$text);
        $text = str_replace('Э','Е',$text);
        $text = str_replace('е','є',$text);
        $text = str_replace('Е','Є',$text);
        $text = str_replace('ъ','’',$text);

        //выше русская и заменилась на украинскую і, меняем назад
        $text = str_replace('жі','жи',$text);
        $text = str_replace('Жі','Жи',$text);
        $text = str_replace('ЖІ','ЖИ',$text);
        $text = str_replace('ші','ши',$text);
        $text = str_replace('Ші','Ши',$text);
        $text = str_replace('ШІ','ШИ',$text);
        $text = str_replace('ці','ци',$text);
        $text = str_replace('Ці','Ци',$text);
        $text = str_replace('ЦІ','ЦИ',$text);

        $text = substr($text, 1);
        return $text;
    }

    private static function TranslitUkRu($text)
    {
        //меняем кривой апостроф на нормальный
        $text = str_replace("'",'’',$text);
        $text = str_replace('`','’',$text);
        $text = str_replace('´','’',$text);
        $text = str_replace('ʾ','’',$text);
        $text = str_replace('ʿ','’',$text);

        $text = str_replace('и','ы',$text);
        $text = str_replace('И','Ы',$text);
        $text = str_replace('і','и',$text);
        $text = str_replace('І','И',$text);
        $text = str_replace('е','э',$text);
        $text = str_replace('Е','Э',$text);
        $text = str_replace('є','е',$text);
        $text = str_replace('Є','Е',$text);
        $text = str_replace('ї','йи',$text);
        $text = str_replace('Ї','Йи',$text);
        $text = str_replace('ґ','г',$text);
        $text = str_replace('Ґ','Г',$text);
        $text = str_replace('’','ъ',$text);

        //выше украинская и заменилась на русскую Ы, меняем назад
        $text = str_replace('жы','жи',$text);
        $text = str_replace('Жы','Жи',$text);
        $text = str_replace('ЖЫ','ЖИ',$text);
        $text = str_replace('шы','ши',$text);
        $text = str_replace('Шы','Ши',$text);
        $text = str_replace('ШЫ','ШИ',$text);
        $text = str_replace('цы','ци',$text);
        $text = str_replace('Цы','Ци',$text);
        $text = str_replace('ЦЫ','ЦИ',$text);

        return $text;
    }
}
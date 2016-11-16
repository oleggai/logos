<?php

namespace app\classes;

setlocale(LC_CTYPE, "ru_RU.CP1251");


class TCPDF_addons
{


    /**
     * Расчитывает ширину для текста (в пикселях)
     * @param $string
     * @param $fontSize
     * @return mixed
     */
    static function CountWidthForString($string, $fontSize,$trueSize=false)
    {
        $fontSize=$fontSize/1.32815;
        $fontFile = '../web/fonts/arial.ttf';
        $points = imagettfbbox($fontSize, 0, $fontFile, $string);
        return ($trueSize)?($points[4]+abs($points[6])):($points[4]-abs($points[6]));
    }



    /**
     * Расчитывает кол-во строк которые будут при переносе слов в ячейке
     * @param $string
     * @param $width
     * @param int $fontSize
     * @return int
     */
    /*
       static function CalculateCountLines($cell_data, $cell_width, $fontSize)
       {
           $lineCount = 1;
           $strWidth = 0;
           $lenstr=strlen($cell_data);

           $simvoli_perenosa=[' ','-',';'];
           $pre_symb='';$next_symb='';
           $poswrap=0;
           $i=0;
           $positionswrap=[];//позиции для переноса



           while ($i<=$lenstr) {
               $cur_symb=$cell_data[$i]; //получаем текущий символ
               $next_symb=($i+1!=$lenstr)?$cell_data[$i+1]:''; //получаем следующий симовл
               $cur_symb_is_spliter=in_array($cur_symb,$simvoli_perenosa);//текущий символ я вляеться разделителем?
               $next_symb_is_spliter=in_array($next_symb,$simvoli_perenosa);//следующий символ я вляеться разделителем?

               $strWidth+=self::CountWidthForString($cur_symb,$fontSize);//расчитываем общую ширину для текущей строки

               if ($strWidth>$cell_width)//если ширина превысила заданную ширину
               {
                   //если текущий символ или следующий вляеться разделителем
                   if ($cur_symb_is_spliter||$next_symb_is_spliter) {
                       //запоминаем эту позицию для переноса
                       $positionswrap[]=$i;
                       //обнуляем счетчики
                       $strWidth=0;$poswrap=0;$pre_symb='';
                       //перходим к следующей позиции
                       $i++;
                   }
                   else//если текущий символ и следующий не вляеться разделителем
                   {
                       //если был до этого символ разделитель
                       if ($poswrap>0) {
                           //возвращаемся к нему и делаем там перенос
                           $i=$poswrap;$positionswrap[]=$i;$i++;$strWidth=0;$poswrap=0;
                       }
                       else {
                           //грубо разрываем это слово - а что делать?
                           $positionswrap[]=$i;
                           //обнуляем счетчики
                           $strWidth=0;$poswrap=0;
                           //перходим к следующей позиции
                           $i++;
                       }

                   }

               }
               else
               {
                   //запоминаем позицию которую можем использовать для разрыва строки при переносе
                   if ($cur_symb_is_spliter) $poswrap=$i;
                   $i++;
               }




           }

           $text='';
           var_dump($positionswrap);
           $beginpos=0;
           foreach($positionswrap as $k=>$v){
               $text.=substr($cell_data,$beginpos,$v-$beginpos).'</br>';
               $beginpos=$v;
           }
           $text.=substr($cell_data,$beginpos).'</br>';

           $lineCount=count($positionswrap);


           return [$lineCount,$text];
       }

   */



             static function CalculateCountLines($cell_data, $cell_width, $fontSize,$isWidthProcent=false,$showStats=false)
             {
                 if($isWidthProcent)
                     $cell_width= $cell_width*0.01*649;
                 $fullTextWidth=self::CountWidthForString($cell_data,$fontSize);
                 $textWidthNoSpace;
                 $words = explode(" ", $cell_data);

                 foreach($words as $item)
                     $textWidthNoSpace+=self::CountWidthForString($item,$fontSize);

                 $spaceWidths=$fullTextWidth-$textWidthNoSpace;

               //  $space_width=self::CountWidthForString(' ',$fontSize);
               //  $space_width=4;
                 $wordsLength=(count($words)==1)?1:count($words)-1;
                 $space_width=$spaceWidths/$wordsLength;
                 $lineCount = 1;
                 $allWidth = 0;

                 foreach ($words as $word) {
                     $CurWidth=self::CountWidthForString($word,$fontSize)+$space_width;

                     //при достижении заданой ширины или превышении ширины
                     //если не помещается это слово - переносим в следующую строку
                     if ($allWidth+$CurWidth>$cell_width) {
                         $allWidth=$CurWidth;
                         $lineCount++;
                     }
                     //если помещается делаем перенос строки но это слово оставляем в этой строке
                     elseif ($allWidth+$CurWidth==$cell_width){
                         $allWidth=0;
                         $lineCount++;
                     }
                     $allWidth+=$CurWidth;
                 }
                 return $lineCount;
             }

    /**
     * функция расчета для отступов сверху (для строки таблицы) для кажой ячейки
     * @param array $strings содержимое ячеек
     * @param array $widths ширины ячеек (пиксели)
     * @param float $lineHeight
     * @param int $fontSize
     * @param int $padding
     * @param int $isWidthProcents если ширина процентная то происходит конвертирование в пикселы
     * @return array
     */
    static function makePadding(array $cells_data, array $cells_widths, $lineHeight = 1.5, $fontSize=14, $padding = 0,$isWidthProcents=0)
    {

        //результирующий массив отступов
        $lineHeights = [];
        //Конвертирую процентные ширины в пиксельные
        if($isWidthProcents)
        for($i=0;$i<count($cells_widths);$i++)
            $cells_widths[$i]= $cells_widths[$i]*0.01*649;
        //просчитываем кол-во строк для каждой ячейки
        foreach ($cells_data as $k=>$cell_data){
          //  $lineHeights[$k] = self::CalculateCountLines($cell_data, $cells_widths[$k] - $padding * 2, $fontSize);
            $lineHeights[$k] = self::CalculateCountLines($cell_data, $cells_widths[$k] - $padding * 2, $fontSize);
        }
        $topValue = max($lineHeights);
        for ($i = 0; $i < sizeof($lineHeights); $i++) {
            $tmp = (($topValue - $lineHeights[$i]) / 2 * $lineHeight);
            $lineHeights[$i] = '<div style="line-height:'.$tmp.';">&nbsp;</div>';
        //  $lineHeights[$i] = $tmp;
        }
        return $lineHeights;
    }

    static function makeSinglePadding($cell_data,$cells_width,$fontSize){
       $lines= self::CalculateCountLines($cell_data, $cells_width,0, $fontSize);
        return $lines;
     //   return ($lines>1)?0:0.5;
    }
    static function lineMargin($lineMultiplier){
        return '<div style="line-height:'.(1*$lineMultiplier).';">&nbsp;</div>';}

   static function url_origin($s, $use_forwarded_host=false)
    {
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
        $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

}

?>

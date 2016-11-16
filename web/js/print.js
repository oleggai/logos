jQuery(document).ready(function() {

    jQuery('.dinamic_font').each(function ()
    {
        var coeficient=1.5;//ширина одного символа в зависимости от буквы. самая широкая буква "щ" т.к текст из разных букв то увиличиваем шрифт на..
        //делаем поправку погрешности при большем кол-ве символов (еще больше уменьшаем шрифт)
        var dlina_texta=jQuery(this).text().length;
        if (dlina_texta>24) coeficient=1.3;

        if (jQuery(this).hasClass('dinamic_font_biger')) coeficient=4;

        var shirina_bloka=jQuery(this).width();
        var razmer_srifta=(shirina_bloka/dlina_texta)*coeficient;

        var max_font_size=parseInt(jQuery(this).css('font-size'),10);
        if (razmer_srifta<8) {
            if ($(this).hasClass('ews-place-post')) {
                razmer_srifta = 9;
            }
            else {
                razmer_srifta = 8;
            }
        }
        if (razmer_srifta>max_font_size) razmer_srifta=max_font_size;

        jQuery(this).css('font-size',razmer_srifta+'px');
    })



  jQuery('.print-content label').wrap( "<div class='substrate'></div>" );


});
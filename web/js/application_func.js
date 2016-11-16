var current_modal = null;

//вывод модального сообщения
function show_app_alert(title_text, alert_text, alert_btn, alert_type, alert_width)
{
    current_modal = webix.alert({
        type: alert_type,
        title: title_text,
        ok: alert_btn,
        text: alert_text,
        width: alert_width,
    });
}

//вывод модального сообщения с кнопкой отмена (закрывает вкладку)
function show_app_alert_with_close(title_text, alert_text, alert_btn, alert_btn_cancel)
{
    current_modal = webix.confirm({
        width: "500px",
        title: title_text,
        ok: alert_btn,
        cancel: alert_btn_cancel,
        text: alert_text,
        callback: function(result) { //setting callback

            if (!result) {
                close_current_tab();
            }
        }
    });
}

//вывод модального сообщения с кнопкой отмена
function show_app_alert_with_close_return(title_text, alert_text, alert_btn, alert_btn_cancel)
{

    return confirm(alert_text);
    /*
    var out_result = '0';
    current_modal = webix.confirm({
        width: "300px",
        title: title_text,
        ok: alert_btn,
        cancel: alert_btn_cancel,
        text: alert_text,
        callback: function(result) { //setting callback
            out_result = result;
        }
    });

    // todo wait for callback
    return out_result;
    */
}

/**
 * Автоматическое изменение высоты textarea в зависимости от текста
 * По умолчанию поле растянуто на 1 строку, если в нем текста больше чем на одну строку, 
 * оно растягивается пропорционально тексту, но не более чем на 3 строки, 
 * если более – то будет вертикальный скрол.
 * 
 * line-heigth: 16px; // высота текста
 * min-height: 22px; // 16px - текст, отступы сверху и снизу по 2px и по 1px border сверху и снизу. В сумме 22px
 * Каждая дополнительная строка добавляет +16px
 * el.scrollHeight не учитывей border, поэтому при каждой новой строке scrollHeight будет варен 20, 36, 52, дальше включаем скрол
 */
function textareaAutoHeigth(el) {
    $(el).css('height', 'auto');
    // если в textarea меньше трех строк - убираем скрол
    if (el.scrollHeight <= 52) {
        $(el).removeClass('scroll');
    }

    if (el.scrollHeight > $(el).innerHeight()) {
        // если больше 3х строк - добавить скрол
        if (el.scrollHeight > 52) {
            $(el).addClass('scroll'); // класс добавляет скролл и устанавливает фиксированную высоту
        } else {
            var newHeigth = el.scrollHeight + 2; // +2 пикселя - для border-а
            $(el).css('height', newHeigth );
        }
    }
}

$(function() {
    $('.input_index_clone').change(function(){
       // console.log('index_clone');
      //  console.log($(this).val());
        $('#ma_index').val($(this).val()).change();
    });

    //при наведении на пункт меню открывать
    var elem = null;
    $('.navbar-nav>li.dropdown>a').hover(function() {
        /*elem = $(this);
        setTimeout(function() {
            $(elem).click();
        }, 400);*/
    }, function() {
    });
    $('.navbar-nav').hover(function() {
    }, function() {
        elem = $(this);
        setTimeout(function() {
            $(elem).find('li').removeClass('open');
        }, 400);
    });
    
    // Автоматическое изменение высоты textarea в зависимости от текста (после 3х строк отобразить скролл)
    $('.textarea-dynamic-height').each(function(index, el) {
        $(el)
            .on('keyup', function(e) {
                textareaAutoHeigth(this);
            })
            .on('change', function() {
                textareaAutoHeigth(this);
            });
            
        textareaAutoHeigth(el);
    });
    
});

window.onbeforeunload = function (evt) {

    // только для основного окна
    if (this.frameElement != null)
        return;

    var message = before_exit_tabs(null);
    if (message === true)
        return;


    if (typeof evt == "undefined") {
        evt = window.event;
    }
    if (evt) {
        evt.returnValue = message;
    }
    return message;
};

/* Translations
 * Функция для перевода
 */
function tr(identity){
    if(typeof translations ==='undefined' || typeof translations[identity] ==='undefined')
        return identity;
    else
        return translations[identity];
        
}


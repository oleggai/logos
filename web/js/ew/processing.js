var dropdown_mode = $('#processing_mode');
var checkbox_type = $('#processing_input_type');
var div_search = $('#ew_processing_search');
var div_manual = $('#ew_processing_manual');
var div_operation_options = $('#operation_options');
var btn_execute = $('#processing_execute');

var input_ew_manual = $('#ew_manual_input');
var input_s_status_code = $('[name="status_code"]');
var input_s_status = $('[name="status_id"]');
var input_s_date = $('[name="status_date"]');
var input_s_comment = $('[name="status_comment"]');
var input_s_employee = $('[name="status_employee"]');
var input_s_country = $('[name="status_country_not_employee"]');
var input_c_date = $('[name="close_date"]');
var input_c_receiver = $('[name="close_receiver"]');
var input_c_post = $('[name="close_receiver_post"]');
var input_c_doc_type = $('[name="close_receiver_doc_type"]');
var input_c_doc_date = $('[name="close_doc_date"]');
var input_c_serial = $('[name="close_doc_serial"]');
var input_c_num = $('[name="close_doc_num"]');
var input_c_employee = $('[name="close_employee"]');

var default_c_employee = input_c_employee.val();

var ccwe = {
    select_country: $('[name="status_country"]'),
    select_city: $('[name="status_city"]'),
    select_warehouse: $('[name="status_warehouse"]'),
    select_employee: input_s_employee
};

input_c_doc_type.change(function(){

    if(this.value==1){
        input_c_serial.inputmask({mask:"ff"});
        input_c_num.inputmask("999999");
    }
    else
    {
        input_c_serial.off();
        input_c_num.off();
    }
}).change();

input_s_status_code.change(function(){

    for (var key in status_codes) {

        if (status_codes[key] == this.value) {
            input_s_status.val(key).change();
            return;
        }
    }
});

input_s_status.on("select2:select",function(){

    input_s_status_code.val(status_codes[this.value]);
});

checkbox_type.change(function(){

    if (this.checked){

        div_search.hide();
        div_manual.show();
    }
    else{

        div_manual.hide();
        div_search.show();
    }


}).change();

dropdown_mode.change(function(){

    div_operation_options.children("div").hide();

    var div_for_operation = $('#operation_options_'+this.value);
    div_for_operation.show();

    input_c_date.val(new Date().format('d.m.Y H:i:s')).addClass('xc');
    input_s_date.val(new Date().format('d.m.Y H:i:s')).addClass('xc');

    input_ew_manual.val('');
    input_s_status_code.val('').trigger('input');
    input_s_status.val('').change();
    input_s_comment.val('').trigger('input');
    input_s_country.val('').change();
    if (this.value == '206'){
        ccwe_reset();
    }

    input_c_receiver.val('').trigger('input');
    input_c_post.val('');
    input_c_doc_type.val('').change();
    input_c_doc_date.val('').removeClass('xc');
    input_c_serial.val('').trigger('input');
    input_c_num.val('').trigger('input');
    input_c_employee.val(default_c_employee).change();

    refreshEws(true);
}).change();

function getEws(){

    // выбрано ручное добавление
    if (checkbox_type.is(':checked')){
        return ews_array;
    }


    var result = [];

    ew_processing_search.eachRow(
        function (row){
            var item = ew_processing_search.getItem(row);
            result.push(item.ew_num);
        }
    );

    return result;
}

function getErrors(fields) {

    var errors = '';

    fields.forEach(function (element) {
        if (element.val())
            element.parent().removeClass('has-error');
        else {
            element.parent().addClass('has-error');
            errors += '"'+element.parent().find('label').text().slice(0, -1)+'" can not be blank <br>';
        }
    });

    return errors;
}

function getParams(mode){

    var errors = '';
    var result = {};

    if (mode == '206'){// добавление статуса

        valiDate(input_s_date); // иногда автоматическая (on blur) проверка почему то не срабатывает
        errors = getErrors([input_s_date,input_s_status,input_s_country,input_s_employee ]);

        result = {
            status_id:input_s_status.val(),
            status_date:input_s_date.val(),
            status_comment:input_s_comment.val(),
            status_employee:input_s_employee.val(),
            status_country:input_s_country.val()
        };
    }
    else if (mode == '50') { // закрытие

        valiDate(input_c_date); // иногда автоматическая (on blur) проверка почему то не срабатывает
        valiDate(input_c_doc_date); // иногда автоматическая (on blur) проверка почему то не срабатывает
        errors = getErrors([input_c_date,input_c_employee]);

        result = {
            close_date:input_c_date.val(),
            close_receiver:input_c_receiver.val(),
            close_receiver_post:input_c_post.val(),
            close_receiver_doc_type:input_c_doc_type.val(),
            close_doc_date:input_c_doc_date.val(),
            close_doc_serial:input_c_serial.val(),
            close_doc_num:input_c_num.val(),
            close_employee:input_c_employee.val()
        };

    }

    if (!errors) {
        return result;
    }
    else{

        return errors;
    }
}

function refreshEws($clear){

    if (checkbox_type.is(':checked')){

        if ($clear)
            ews_array = [];

        refreshEwNums();
    }
    else{
        ew_processing_search.clearAll();

        if (!$clear)
            ew_processing_search.loadNext(ew_processing_search.config.datafetch, 0, null, ew_processing_search_last_filter_url);
    }
}

btn_execute.click(function(){

    var result = '';

    var processing_mode = dropdown_mode.val();
    var ews_array = getEws();
    var ews_string = ews_array.join();
    var processing_params = getParams(processing_mode);


    if (processing_mode=='0'){
        result = 'Не выбрана операция';
    }
    else if (ews_array.length == 0){
        result = 'Список накладных пуст';
    }
    else if (typeof processing_params  == 'string'){
        result = processing_params;
    }
    else {

        $.ajax({
            type: "POST",
            url: url_processing,
            data: {mode: processing_mode, ews:ews_string, params:processing_params},
            dataType: "json",
            timeout: 30000, // in milliseconds
            success: function(data) {
                show_app_alert('Результат обработки', data, 'Ok', '', '500px');
                refreshEws();
            },
            error: function(request, status, err) {
                show_app_alert('Ошибка', 'Ошибка выполнения операции! Обратитесь к администратору ИС для выяснения причины.', 'Ok');
            }
        });

        result = '';
    }

    if (result){
        show_app_alert('Ошибка', result, 'Ok');
    }

});

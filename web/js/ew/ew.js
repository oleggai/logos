/**
 * Created by goga on 07.04.2015.
 */
var delOnce=true;
function addDays(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

function onOperationChange(value){
    if (value == 50) {// закрытие накладной
        $(".closing_ew_div *").removeAttr('disabled'); //not('#closing_receiver_post_id')
        $(".closing_ew_div *").removeAttr('readonly');
        $("#expresswaybill-_closing_date").val(new Date().format('d.m.Y H:i:s'));
    }
    else {
        $(".closing_ew_div :not(input)").attr("disabled");
        $(".closing_ew_div *").attr('readonly','readonly');

        if (value == 51)
            $("#expresswaybill-_closing_date").val('');
    }
}


/// формирование даты на стороне клиента
if ($("[name='isNewRecord']").val()) {

    var today = new Date().format('d.m.Y H:i:s');
    $("#expresswaybill-_date").val(today);
    $("#expresswaybill-_invoice_date").val(today);
    $("#expresswaybill-_est_delivery_date").val( addDays(new Date(),14).format('d.m.Y'));
}

$.fn.disable = function() {
    return this.each(function() {
        if (typeof this.disabled != "undefined") this.disabled = true;
    });
};

$.fn.enable = function() {
    return this.each(function() {
        if (typeof this.disabled != "undefined") this.disabled = false;
    });
};

$(".closing_ew_div *").disable();

$( "#operation_selector" ).change(function() {
    onOperationChange(this.value);
});

$("#expresswaybill-shipment_type").change(function(){
    if (this.value == 2 && $('#expresswaybill-customs_declaration_cost').val() =='')
        $('#expresswaybill-customs_declaration_cost').val('0.00');
}).change();

$('#expresswaybill-customs_declaration_currency').change(function(){
    $('#expresswaybill-declared_currency').val(this.value);
});

$('#expresswaybill-customs_declaration_cost').change(function(){
    if (grid_invoice_positions.count()==0)
        $('#expresswaybill-invoice_cost').val($('#expresswaybill-customs_declaration_cost').val());


    $('#expresswaybill-declared_cost').val(this.value);
});

function pad (str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}


/**
 * Поиск курса валют по symbol
 * @param parent symbol отцовской валюты
 * @param child symbol дочерней валюты
 * @returns {*}
 */
function getExRate(parent, child){
    var result = NaN;
    var result_reverse = NaN; // обратный курс

    if (parent == child)
        return 1;

    for (var i = 0; i < exchange_rates_json.length; i++) {

        if (exchange_rates_json[i]['currency_child'] == child &&
            exchange_rates_json[i]['currency_parent'] == parent) {

            result = parseFloat(exchange_rates_json[i]['ratio']);
            break;
        }

        if (exchange_rates_json[i]['currency_child'] == parent &&
            exchange_rates_json[i]['currency_parent'] == child) {

            result_reverse = parseFloat(exchange_rates_json[i]['ratio']);
        }
    }

    // прямой не найден, но найден обратный
    if (isNaN(result) && !isNaN(result_reverse) && result_reverse>0)
        result = 1/result_reverse;

    return result;
}

/**
 * Поиск курса валют по id
 * @param parent id отцовской валюты
 * @param child id дочерней валюты
 * @returns {*}
 */
function getExRateId(parent, child){
    var result = NaN;
    var result_reverse = NaN; // обратный курс

    if (parent == child)
        return 1;

    for (var i = 0; i < exchange_rates_json.length; i++) {

        if (exchange_rates_json[i]['id_child'] == child && exchange_rates_json[i]['id_parent'] == parent) {

            result = parseFloat(exchange_rates_json[i]['ratio']);
            break;
        }

        if (exchange_rates_json[i]['id_child'] == parent && exchange_rates_json[i]['id_parent'] == child) {
            result_reverse = parseFloat(exchange_rates_json[i]['ratio']);
        }
    }

    // прямой не найден, но найден обратный
    if (isNaN(result) && !isNaN(result_reverse) && result_reverse > 0)
        result = 1 / result_reverse;

    return result;
}


function calcCargo_est_weight_kg(){

    $('#expresswaybill-cargo_est_weight_kg').val(
        Math.max(
            //$('#expresswaybill-actual_cntrl_weight_kg').val(),
            //$('#expresswaybill-dimen_cntrl_weight_kg').val(),
            $('#expresswaybill-total_dimensional_weight_kg').val(),
            $('#expresswaybill-total_actual_weight_kg').val()
        )
    )
    $('#expresswaybill-cargo_est_weight_kg').change(); // для перерасчёиа стотимости доставки
}

$('#expresswaybill-actual_cntrl_weight_kg').change(calcCargo_est_weight_kg);
$('#expresswaybill-dimen_cntrl_weight_kg').change(calcCargo_est_weight_kg);
$('#expresswaybill-total_dimensional_weight_kg').change(calcCargo_est_weight_kg);
$('#expresswaybill-total_actual_weight_kg').change(calcCargo_est_weight_kg);


var ew_event_input_waiting = $('#ew_event_input_waiting');
var ew_event_input_success = $('#ew_event_input_success');
var ew_event_input_error = $('#ew_event_input_error');

$("#ew_add_event_button").click(function(){

    addEwEvent($("[name=ew_add_event_dropdown]").val());
});

function refreshEwEvents(){

    grid_events.clearAll();
    grid_events.load(grid_events.config.url);
}

function hideMessages() {
    ew_event_input_success.hide();
    ew_event_input_waiting.hide();
    ew_event_input_error.hide();
}

function addEwEvent(new_event){

    hideMessages();

    ew_event_input_waiting.show();
    $.ajax({ url: url_callevent,
        data: 'entity_id='+ew_id+"&event="+new_event,
        type: 'post',
        success: function(output) {

            ew_event_input_waiting.hide();
            var result = output;


            if (result == true) {
                ew_event_input_error.hide();
                ew_event_input_success.show();
                refreshEwEvents();
                return;
            }


            ew_event_input_error.text(result);
            ew_event_input_error.show();
        }
    });
}

$('#expresswaybill-payer_type').change(function () {

    var msg = '';

    //если выбрано третье лицо, то проверяем может ли отправитель или получатель платить третьим лицом
    if (this.value == 3){
        if ($('#sender_thirdparty').val() === '0' && $('#receiver_thirdparty').val() === '0')
           msg = 'третье лицо недопутимо';
    }

        //проверки можно ли платить по безналу
    if ($("#expresswaybill-payer_payment_type").val() == 2){
        if (this.value == 1  && $('#sender_clearing').val() === '0') //отправитель
            msg = 'отправитель не платит безналом';
        if (this.value == 2 && $('#receiver_clearing').val() === '0') //получатель
            msg = 'получатель не платит безналом';
        if (this.value == 3 && $('#payer_third_party_clearing').val() === '0') //третье лицо
            msg = 'третье лицо не платит безналом';
    }

    if (msg){
        show_app_alert('Ошибка', msg, 'Ok');
        this.value = '';
    }

    var third_party = $("#payer_third_party_id");
    var third_party_btn = $("#third_party_btn");
    //с закладки cost
    $('#expresswaybill-int_delivery_payer').val(this.value);
    var cost_third_party = $("#cost_third_party_id");
    var cost_third_party_btn = $("#cost_third_party_btn");

    if (this.value != payer_type_thirdparty) {
        third_party.val('').change();
        third_party_btn.hide();
        cost_third_party.val('').change();
        cost_third_party_btn.hide();
    }
    else {
        third_party_btn.show();
        cost_third_party_btn.show();
    }
}).change();

$("#expresswaybill-payer_payment_type").change(function(){

    //проверки можно ли платить по безналу
    if (this.value == 2){
        var msg = '';
        if ($('#expresswaybill-payer_type').val() == 1  && $('#sender_clearing').val() === '0') //отправитель
            msg = 'отправитель не платит безналом';
        if ($('#expresswaybill-payer_type').val() == 2 && $('#receiver_clearing').val() === '0') //получатель
            msg = 'получатель не платит безналом';
        if ($('#expresswaybill-payer_type').val() == 3 && $('#payer_third_party_clearing').val() === '0') //третье лицо
            msg = 'третье лицо не платит безналом';
        if (msg){
            show_app_alert('Ошибка', msg, 'Ok');
            this.value = '';
        }
    }

    $("#expresswaybill-int_delivery_payment_type").val(this.value);
});

$( document ).ready(function() {


        //смена типа документа физ лица
        var ew_first_load = true;
        $("#expresswaybill-closing_receiver_doc_type").change(function() {


            if(this.value==1){
                $('#expresswaybill-closing_receiver_doc_serial_num').inputmask({
                    mask: "ff",
                    definitions: {
                        'f': {
                            validator: '[АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя]',
                            cardinality: '1',
                            casing: 'upper'
                        }
                    }
                });
                $('#expresswaybill-closing_doc_num').inputmask("999999");
            }
            else
            {
                $('#expresswaybill-closing_receiver_doc_serial_num').off();
                $('#expresswaybill-closing_doc_num').off();
            }

            if (!ew_first_load){
                $('#expresswaybill-closing_doc_num').val('').trigger('input');
                $('#expresswaybill-closing_receiver_doc_serial_num').val('').trigger('input');
            }
            else
                ew_first_load = false;

        }).change();


    onOperationChange($( "#operation_selector").val());
});

//отправитель, получатель и третье лицо (контрагенты)--------------------------------------------------------------------------------
function FillFromCounterparty(data, isFirs, CounterpartyId, CounterpartyType, CounterpartyName, ContactpersId, PhonenumId, EmailId, AddressId, Clearing, Thirdparty, Color) {
    if (data == null) {
        ClearCounterparty(CounterpartyId, CounterpartyType, CounterpartyName, ContactpersId, PhonenumId, EmailId, AddressId, Clearing, Thirdparty, Color);
        return;
    }

    CounterpartyId.val(data['counterparty_id']).trigger('input').trigger('kek');
    CounterpartyType.val(data['person_type']).trigger('kek');
    CounterpartyName.val(data['counterparty_name']).trigger('input').trigger('kek');
    if(Clearing) Clearing.val(data['clearing']);
    if(Thirdparty) Thirdparty.val(data['clearing']);
    if(Color) Color.val(data['row_color']).change();;
    if (!isFirs) { //если это не вычитка из модели при редактировании, то вычитываем и основные сущности
        ContactpersId.val(data['counterparty_primary_pers_id']).change();
        PhonenumId.val(data['counterparty_primary_phone_id']).change();
        EmailId.val(data['counterparty_primary_email_id']).change();
        AddressId.val(data['counterparty_primary_adress_id']).change();
    }
}

function ClearCounterparty(CounterpartyId, CounterpartyType, CounterpartyName, ContactpersId, PhonenumId, EmailId, AddressId, Clearing, Thirdparty, Color){
    CounterpartyId.val("").trigger('input');
    CounterpartyType.val("");
    CounterpartyName.val("").trigger('input');
    ContactpersId.val("").change();
    PhonenumId.val("").change();
    EmailId.val("").change();
    AddressId.val("").change();
    if(Clearing) Clearing.val("");
    if(Thirdparty) Thirdparty.val("");
    if(Color) Color.val("").change();
}

function FillFromAddress(data, Country, Region, City, Postcode, Address, Kind) {
    if (data == null) {
        ClearAddress(Country, Region, City, Postcode, Address, Kind);
        return;
    }

    Country.val(data['country_name']).trigger('kek');
    Region.val(data['region_name']).trigger('kek');
    City.val(data['city_name']).trigger('kek');
    Postcode.val(data['index']).trigger('kek');
    Address.val(data['address_name']).trigger('kek');
    if (Kind) Kind.val(data['adress_kind']).change();
}

function ClearAddress(Country, Region, City, Postcode, Address, Kind){
    Country.val("");
    Region.val("");
    City.val("");
    Postcode.val("");
    Address.val("");
    if(Kind) Kind.val("");
}

//отправитель---------------------------
var senderId = $('#sender_id');
var senderContactpersId = $('#sender_cp_contactpers_id');
var senderPhonenumId = $('#sender_cp_phonenum_id');
var senderEmailId = $('#sender_cp_email_id');
var senderAddressId = $('#sender_cp_address_id');
var senderClearing = $('#sender_clearing');
var senderThirdparty = $('#sender_thirdparty');
var senderColor = $('#sender_color');

var senderCounterpartyId = $('#sender_counterparty_id');
var senderCounterpartyType = $('#sender_counterparty_type');
var senderCounterpartyName = $('#sender_counterparty_name');
var senderContactpers = $('#sender_cp_contactpers');
var senderPhonenum = $('#sender_cp_phonenum');
var senderEmail = $('#sender_cp_email');

var senderCountry = $('#sender_country');
var senderRegion = $('#sender_region');
var senderCity = $('#sender_city');
var senderPostcode = $('#sender_postcode');
var senderAddress = $('#sender_address');
var senderAddressKind = $('#sender_cp_address_kind');
/*
if (disableEdit) {
    senderCounterpartyId.removeClass("clearable");
    senderCounterpartyName.removeClass("clearable");
}
*/
senderColor.change(function() {
    if (disableEdit)
        return;
    if (this.value)
        senderCounterpartyId.css("background-color", this.value);
    else
        senderCounterpartyId.css("background-color", '#FFFFFF');
}).change();

senderCounterpartyId.keydown(function (e) {
    if (e.which == 13) {
        senderCounterpartyId.blur();
        e.preventDefault();
        return false;
    }
});

var isFirstSender = false;

senderId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_counterparty, { id:this.value},
            function(data) {

                var msg = '';
                //если выбрано третье лицо, то проверяем может ли отправитель или получатель платить третьим лицом
                if ($('#expresswaybill-payer_type').val() == 3){
                    if (data['thirdparty'] === 0 && $('#receiver_thirdparty').val() === '0')
                        msg = 'неоходима возможность оплаты третьим лицом';
                }

                //проверки можно ли платить по безналу
                if ($("#expresswaybill-payer_payment_type").val() == 2){
                    if ($('#expresswaybill-payer_type').val() == 1  && data['clearing'] === 0) //отправитель
                        msg = 'необходима возможность оплаты безналом';
                }

                if (msg){
                    show_app_alert('Ошибка', msg, 'Ok');
                    senderId.val('').change();
                    return;
                }

                FillFromCounterparty(data, isFirstSender, senderCounterpartyId, senderCounterpartyType, senderCounterpartyName,
                    senderContactpersId, senderPhonenumId, senderEmailId, senderAddressId, senderClearing, senderThirdparty, senderColor);
                isFirstSender = false;
            }
        );
    }
    else {
        ClearCounterparty(senderCounterpartyId, senderCounterpartyType, senderCounterpartyName, senderContactpersId,
           senderPhonenumId, senderEmailId, senderAddressId, senderClearing, senderThirdparty, senderColor);
        isFirstSender = false;
    }
});

senderCounterpartyId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_counterparty_id, {counterparty_id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data);
                    this_item.change();
                };
            }(senderId)
            )
        );
    }
    else {
        senderId.val("").change();
    }
});

senderCounterpartyName.change(function(){
    if (!this.value || this.value == "")
        senderId.val("").change().trigger('kek');
});

senderContactpersId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_contactpers, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['display_name']).trigger('kek');
                };
            }(senderContactpers)
            )
        );
    }
    else {
        senderContactpers.val("");
    }
});

senderPhonenumId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_phone, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['display_phone']).trigger('kek');
                };
            }(senderPhonenum)
            )
        );
    }
    else {
        senderPhonenum.val("");
    }
});

senderEmailId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_email, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['email']).trigger('kek');
                };
            }(senderEmail)
            )
        );
    }
    else {
        senderEmail.val("");
    }
});

senderAddressId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_address, { id:this.value},
            function(data) {
                FillFromAddress(data, senderCountry, senderRegion, senderCity, senderPostcode, senderAddress, senderAddressKind);
            }
        );
    }
    else {
        ClearAddress(senderCountry, senderRegion, senderCity, senderPostcode, senderAddress, senderAddressKind);
    }
});

//получатель----------------------------
var receiverId = $('#receiver_id');
var receiverContactpersId = $('#receiver_cp_contactpers_id');
var receiverPhonenumId = $('#receiver_cp_phonenum_id');
var receiverEmailId = $('#receiver_cp_email_id');
var receiverAddressId = $('#receiver_cp_address_id');
var receiverClearing = $('#receiver_clearing');
var receiverThirdparty = $('#receiver_thirdparty');
var receiverColor = $('#receiver_color');

var receiverCounterpartyId = $('#receiver_counterparty_id');
var receiverCounterpartyType = $('#receiver_counterparty_type');
var receiverCounterpartyName = $('#receiver_counterparty_name');
var receiverContactpers = $('#receiver_cp_contactpers');
var receiverPhonenum = $('#receiver_cp_phonenum');
var receiverEmail = $('#receiver_cp_email');

var receiverCountry = $('#receiver_country');
var receiverRegion = $('#receiver_region');
var receiverCity = $('#receiver_city');
var receiverPostcode = $('#receiver_postcode');
var receiverAddress = $('#receiver_address');
var receiverAddressKind = $('#receiver_cp_address_kind');

if (disableEdit) {
    receiverCounterpartyId.removeClass("clearable");
    receiverCounterpartyName.removeClass("clearable");
}

receiverColor.change(function() {
    if (disableEdit)
        return;
    if (this.value)
        receiverCounterpartyId.css("background-color", this.value);
    else
        receiverCounterpartyId.css("background-color", '#FFFFFF');
}).change();


receiverCounterpartyId.keydown(function (e) {
    if (e.which == 13) {
        receiverCounterpartyId.blur();
      //  e.preventDefault();
        return false;
    }
});

var isFirstReceiver = false;

receiverId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_counterparty, { id:this.value},
            function(data) {

                var msg = '';
                //если выбрано третье лицо, то проверяем может ли отправитель или получатель платить третьим лицом
                if ($('#expresswaybill-payer_type').val() == 3){
                    if (data['thirdparty'] === 0 && $('#sender_thirdparty').val() === '0')
                        msg = 'неоходима возможность оплаты третьим лицом';
                }

                //проверки можно ли платить по безналу
                if ($("#expresswaybill-payer_payment_type").val() == 2){
                    if ($('#expresswaybill-payer_type').val() == 2  && data['clearing'] === 0) //получатель
                        msg = 'необходима возможность оплаты безналом';
                }

                if (msg){
                    show_app_alert('Ошибка', msg, 'Ok');
                    receiverId.val('').change();
                    return;
                }

                FillFromCounterparty(data, isFirstReceiver, receiverCounterpartyId, receiverCounterpartyType, receiverCounterpartyName,
                    receiverContactpersId, receiverPhonenumId, receiverEmailId, receiverAddressId, receiverClearing, receiverThirdparty, receiverColor);
                isFirstReceiver = false;
            }
        );
    }
    else {
        ClearCounterparty(receiverCounterpartyId, receiverCounterpartyType, receiverCounterpartyName, receiverContactpersId,
            receiverPhonenumId, receiverEmailId, receiverAddressId, receiverClearing, receiverThirdparty, receiverColor);
        isFirstReceiver = false;
    }
});

receiverCounterpartyId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_counterparty_id, {counterparty_id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data);
                    this_item.change();
                };
            }(receiverId)
            )
        );
    }
    else {
        receiverId.val("").change();
    }
});

receiverCounterpartyName.change(function(){
    if (!this.value || this.value == "")
        receiverId.val("").change();
});

receiverContactpersId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_contactpers, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['display_name']).trigger('kek');
                };
            }(receiverContactpers)
            )
        );
    }
    else {
        receiverContactpers.val("");
    }
});

receiverPhonenumId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_phone, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['display_phone']).trigger('kek');
                };
            }(receiverPhonenum)
            )
        );
    }
    else {
        receiverPhonenum.val("");
    }
});

receiverEmailId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_email, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['email']).trigger('kek');
                };
            }(receiverEmail)
            )
        );
    }
    else {
        receiverEmail.val("");
    }
});

receiverAddressId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_address, { id:this.value},
            function(data) {
                FillFromAddress(data, receiverCountry, receiverRegion, receiverCity, receiverPostcode, receiverAddress, receiverAddressKind);
            }
        );
    }
    else {
        ClearAddress(receiverCountry, receiverRegion, receiverCity, receiverPostcode, receiverAddress, receiverAddressKind);
    }
});

//автозаполнение типа услуги по типу адреса отправителя и получателя
var service_type = $('#service_type');

receiverAddressKind.change(function(){
    if (!senderAddressKind.val())
        return;
    if (!receiverAddressKind.val())
        return;

    if (senderAddressKind.val() == 1) //двери
    {
        if (receiverAddressKind.val() == 1) //двери
            service_type.val(4).change();
        if (receiverAddressKind.val() == 2) //склад
            service_type.val(5).change();
    }
    if (senderAddressKind.val() == 2) //склад
    {
        if (receiverAddressKind.val() == 1) //двери
            service_type.val(3).change();
        if (receiverAddressKind.val() == 2) //склад
            service_type.val(2).change();
    }
});


//КА в инвойсе---------------------------
var invoiceCpId = $('#invoice_cp_id');
var invoiceContactpersId = $('#invoice_cp_contactpers_id');
var invoicePhonenumId = $('#invoice_cp_phonenum_id');
var invoiceEmailId = $('#invoice_cp_email_id');
var invoiceAddressId = $('#invoice_cp_address_id');

var invoiceCounterpartyId = $('#invoice_counterparty_id');
var invoiceCounterpartyType = $('#invoice_counterparty_type');
var invoiceCounterpartyName = $('#invoice_counterparty_name');
var invoiceContactpers = $('#invoice_cp_contactpers');
var invoicePhonenum = $('#invoice_cp_phonenum');
var invoiceEmail = $('#invoice_cp_email');

var invoiceCountry = $('#invoice_country');
var invoiceRegion = $('#invoice_region');
var invoiceCity = $('#invoice_city');
var invoicePostcode = $('#invoice_postcode');
var invoiceAddress = $('#invoice_address');

if (disableEdit) {
    invoiceCounterpartyId.removeClass("clearable");
    invoiceCounterpartyName.removeClass("clearable");
}

invoiceCounterpartyId.keydown(function (e) {
    if (e.which == 13) {
        invoiceCounterpartyId.blur();
       // e.preventDefault();
        return false;
    }
});

var isFirstInvoice = true;

invoiceCpId.change(function(){

    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_counterparty, { id:this.value},
            function(data) {
                FillFromCounterparty(data, isFirstInvoice, invoiceCounterpartyId, invoiceCounterpartyType, invoiceCounterpartyName,
                    invoiceContactpersId, invoicePhonenumId, invoiceEmailId, invoiceAddressId);
                isFirstInvoice = false;
            }
        );
    }
    else {
        ClearCounterparty(invoiceCounterpartyId, invoiceCounterpartyType, invoiceCounterpartyName, invoiceContactpersId,
            invoicePhonenumId, invoiceEmailId, invoiceAddressId);
        isFirstInvoice = false;
        if(delOnce)
        checkInvoiceTab();

    }
    delOnce=false;
}).change();

invoiceCounterpartyId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_counterparty_id, {counterparty_id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data);
                    this_item.change();
                };
            }(invoiceCpId)
            )
        );
    }
    else {
        invoiceCpId.val("").change();
    }
});

invoiceCounterpartyName.change(function(){
    if (!this.value || this.value == "")
        invoiceCpId.val("").change();
});

invoiceContactpersId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_contactpers, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['display_name']).trigger('kek');
                };
            }(invoiceContactpers)
            )
        );
    }
    else {
        invoiceContactpers.val("");
    }
}).change();

invoicePhonenumId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_phone, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['display_phone']).trigger('kek');
                };
            }(invoicePhonenum)
            )
        );
    }
    else {
        invoicePhonenum.val("");
    }
}).change();

invoiceEmailId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_email, {id:this.value},
            (function(this_item) {
                return function(data) {
                    this_item.val(data['email']).trigger('kek');
                };
            }(invoiceEmail)
            )
        );
    }
    else {
        invoiceEmail.val("");
    }
}).change();

invoiceAddressId.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_address, { id:this.value},
            function(data) {
                FillFromAddress(data, invoiceCountry, invoiceRegion, invoiceCity, invoicePostcode, invoiceAddress, null);
            }
        );
    }
    else {
        ClearAddress(invoiceCountry, invoiceRegion, invoiceCity, invoicePostcode, invoiceAddress);
    }
}).change();

//третье лицо---------------------------
function payerThirdPartyIdChange (){
    var payerThirdPartyId = $('#payer_third_party_id');
    var payerThirdPartyCounterpartyName = $('#payer_third_party_counterparty_name');
    var payerThirdPartyClearing = $('#payer_third_party_clearing');

    if (payerThirdPartyId.val() && payerThirdPartyId.val() != "") {
        jQuery.getJSON(url_get_counterparty, { id: payerThirdPartyId.val()},
            (function(item1, item2) {
                return function(data) {

                    var msg = '';
                    //проверки можно ли платить по безналу
                    if ($("#expresswaybill-payer_payment_type").val() == 2){
                        if ($('#expresswaybill-payer_type').val() == 3  && data['clearing'] === 0) //третье лицо
                            msg = 'необходима возможность оплаты безналом';
                    }

                    if (msg){
                        show_app_alert('Ошибка', msg, 'Ok');
                        $('#payer_third_party_id').val('');
                        return;
                    }

                    item1.val(data['counterparty_name']);
                    item2.val(data['clearing']);
                };
            }(payerThirdPartyCounterpartyName, payerThirdPartyClearing)
            )
        );
    }
    else {
        payerThirdPartyCounterpartyName.val("").trigger('input');
        payerThirdPartyClearing.val("");
    }
}

$('#payer_third_party_id').change(function(){
    payerThirdPartyIdChange();
    $('#cost_third_party_id').val(this.value);
    costThirdPartyIdChange();
});


//События открытия и закрытия подменю в принте
if(document.getElementsByClassName("next-ul-dropdown-menu dropdown-submenu")[0]!=undefined)
document.getElementsByClassName("next-ul-dropdown-menu dropdown-submenu")[0].getElementsByTagName("a")[0].addEventListener('keydown',function(){
    if(event.keyCode==37){
        openCurrentEl(".dropdown-menu.dropdown-enchanced-left:eq(0)");
        $(".dropdown-menu.dropdown-enchanced-left:eq(0) a:eq(0)").focus();
    }
    else if(event.keyCode==39)
        $(".dropdown-menu.dropdown-enchanced-left:eq(0)").css("display","none");
},true);
if(document.getElementsByClassName("next-ul-dropdown-menu dropdown-submenu")[1]!=undefined)
document.getElementsByClassName("next-ul-dropdown-menu dropdown-submenu")[1].getElementsByTagName("a")[0].addEventListener('keydown',function(){
    if(event.keyCode==37){
        openCurrentEl(".dropdown-menu.dropdown-enchanced-left:eq(1)");
        $(".dropdown-menu.dropdown-enchanced-left:eq(1) a:eq(0)").focus();}
    else if(event.keyCode==39)
        $(".dropdown-menu.dropdown-enchanced-left:eq(1)").css("display","none");
},true);

$(function() {
    if ($("#expresswaybill-state").val() == '2' || $('.operation_selector').val() == 50) {
        $(".showOnClose").css('display', 'block');
        $('.moveOnCloseTarget').html($(".moveOnClose").html());
        $(".moveOnClose").html('');
    }


    grid_places.attachEvent('onDataUpdate', function (id, data) {
        checkGridValues(data, [
            'actual_weight',
            'dimensional_weight',
            'height',
            'length',
            'width',
        ], 'grid_places');

        checkGridTextVal(data, [
            'place_shipment_desc',
            'place_pack_num',
        ], 'grid_places');

    });


    grid_invoice_positions.attachEvent('onDataUpdate', function (id, data) {
       // console.log(data);
        checkGridValues(data, [
            'cost_per_piece',
            'pieces_quantity',
            'total_cost',
        ], 'grid_invoice_positions');

        checkGridTextVal(data, [
            'customs_goods_code',
            'full_desc',
            'material_good',
        ], 'grid_invoice_positions');

    });

    grid_addservice.attachEvent('onDataUpdate', function (id, data) {
        checkGridValues(data, [
            'service_cost',
            'service_cost_uah',
        ], 'grid_addservice');

    });

/*
    grid_orders.attachEvent('onDataUpdate', function (id, data) {
        checkGridValues(data, [
            'wb_order_num',
        ], 'grid_orders');
    });
*/
    grid_addservice.attachEvent('onDataUpdate', function (id, data) {
        checkGridTextVal(data, [
            'service_name',
        ], 'grid_addservice');
    });

//События открытия, закрытия и перемещения в подменю в подменю в меню в принте
    //down 40 up 38
    //37 left 39 right
    if (document.getElementsByClassName("dropdown-menu dropdown-enchanced-left")[0] != undefined) {
        var els = document.getElementsByClassName("dropdown-menu dropdown-enchanced-left")[0].getElementsByTagName("li");

        for (var i = 0; i < els.length; i++)
            els[i].addEventListener('keydown', function () {
                if (event.keyCode == 37) {
                    openCurrentEl(".dropdown-menu.dropdown-enchanced-left:eq(0)");

                }
                else if (event.keyCode == 39) {
                    $(".dropdown-menu.dropdown-enchanced-left:eq(0)").css("display", "none");
                    $('.next-ul-dropdown-menu.dropdown-submenu:eq(0) a:eq(0)').focus();
                }
                else if (event.keyCode == 40) {
                    $(this).next().find('a').focus();
                }
                else if (event.keyCode == 38) {
                    $(this).prev().find('a').focus();
                }
            }, true);
    }
    if (document.getElementsByClassName("dropdown-menu dropdown-enchanced-left")[0] != undefined) {


        var els2 = document.getElementsByClassName("dropdown-menu dropdown-enchanced-left")[1].getElementsByTagName("li");
        for (var i = 0; i < els2.length; i++)
            els2[i].addEventListener('keydown', function () {

                if (event.keyCode == 37) {
                    openCurrentEl(".dropdown-menu.dropdown-enchanced-left:eq(1)");

                }
                else if (event.keyCode == 39) {
                    $(".dropdown-menu.dropdown-enchanced-left:eq(1)").css("display", "none");
                    $('.next-ul-dropdown-menu.dropdown-submenu:eq(1) a:eq(0)').focus();
                }
                else if (event.keyCode == 40) {
                    $(this).next().find('a').focus();
                }
                else if (event.keyCode == 38) {
                    $(this).prev().find('a').focus();
                }

            }, true);
    }


});


$('#expresswaybill-closing_receiver_post').addClass('clearablecombo xc');

grid_orders.config.editable=($('#operation_selector').val()=="")?false:true;

EWerasingFields=[
    '#sender_counterparty_id', '#sender_counterparty_type', '#sender_counterparty_name',
    '#sender_cp_contactpers', '#sender_cp_phonenum', '#sender_cp_email',
    '#sender_country', '#sender_region', '#sender_city',
    '#sender_postcode', '#sender_address',

    '#receiver_counterparty_id', '#receiver_counterparty_type', '#receiver_counterparty_name',
    '#receiver_cp_contactpers', '#receiver_cp_phonenum', '#receiver_cp_email',
    '#receiver_country', '#receiver_region', '#receiver_city',
    '#receiver_postcode', '#receiver_address',

    '#invoice_counterparty_id', '#invoice_counterparty_type', '#invoice_counterparty_name',
    '#invoice_cp_contactpers','#invoice_cp_phonenum', '#invoice_cp_email',
    '#invoice_country' , '#invoice_region',  '#invoice_city',
    '#invoice_postcode','#invoice_address',
];



function senderAllErase(){
if($(event.target).attr('id')=='sender_counterparty_id'||
        $(event.target).attr('id')=='sender_counterparty_type'||
        $(event.target).attr('id')=='sender_counterparty_name')
        senderEraser(0,11);
else if($(event.target).attr('id')=='sender_cp_contactpers')
        senderEraser(3,6);
else if($(event.target).attr('id')=='sender_country'||
        $(event.target).attr('id')=='sender_region'||
        $(event.target).attr('id')=='sender_city'||
        $(event.target).attr('id')=='sender_postcode'||
        $(event.target).attr('id')=='sender_address')
          senderEraser(6,11);

else if($(event.target).attr('id')=='receiver_counterparty_id'||
        $(event.target).attr('id')=='receiver_counterparty_type'||
        $(event.target).attr('id')=='receiver_counterparty_name')
        senderEraser(11,22);
else if($(event.target).attr('id')=='receiver_cp_contactpers')
        senderEraser(14,17);
else if($(event.target).attr('id')=='receiver_country'||
        $(event.target).attr('id')=='receiver_region'||
        $(event.target).attr('id')=='receiver_city'||
        $(event.target).attr('id')=='receiver_postcode'||
        $(event.target).attr('id')=='receiver_address')
        senderEraser(17,22);

else if($(event.target).attr('id')=='invoice_counterparty_id'||
    $(event.target).attr('id')=='invoice_counterparty_type'||
    $(event.target).attr('id')=='invoice_counterparty_name')
    senderEraser(22,33);
else if($(event.target).attr('id')=='invoice_cp_contactpers')
    senderEraser(25,28);
else if($(event.target).attr('id')=='invoice_country'||
    $(event.target).attr('id')=='invoice_region'||
    $(event.target).attr('id')=='invoice_city'||
    $(event.target).attr('id')=='invoice_postcode'||
    $(event.target).attr('id')=='invoice_address')
    senderEraser(28,33);
}

    function senderEraser(from,to){
        for(var i=from;i<to;i++)
        $(EWerasingFields[i]).val('').removeClass('erCx').change();
    }

if($('#operation_selector').val()==2)
$('.eraseCombo').addClass('erCx');

 $('.eraseCombo.erCx').on('kek',function(){
    // console.log( $(this).attr('id'));
 if($(this).val()!='')
 $(this).addClass('erCx');
     else{
     $(this).removeClass('onErCx');
     $(this).removeClass('erCx');
 }
 });

var accepted=true;

function eraseChange(){
    if(this.value!=""){
        $(this).addClass('onErCx');
        $(this).addClass('erCx');
    }
      //  console.log($(this).attr('id'));

}

$('.eraseCombo.erCx').on('mousemove',function(){
    if(accepted&&event.clientX!=undefined){
       // console.log('moved');
        if(this.offsetWidth-18 < event.clientX-this.getBoundingClientRect().left)
            $(this).addClass('onErCx');
        else
            $(this).removeClass('onErCx');
        accepted=false;
        setTimeout(function(){accepted=true;},100);
    }
});

$('.eraseCombo').on('change',function(){$(this).trigger('kek');})

function updateAllCrests(half){
    if(half)
    for(var i=0;i<11;i++)
   $(EWerasingFields[i]).trigger('kek');
}

$('html').on('click','.onErCx',function(){$(this).val('').trigger('kek'); senderAllErase();});

grid_related_entities.attachEvent('onItemDblClick', function() {

    var item_id = grid_related_entities.getSelectedId();
    var item = grid_related_entities.getItem(item_id);
    if (!item)
        return;

    var thisIframeId = window.frameElement.getAttribute("id");
    var tabName = ((item['doc_type_num'] == 1) ? 'MN ' : 'EW ') + item['doc_num'] + ' view';
    var url = url_prefix + '?r=' + ((item['doc_type_num'] == 1) ? 'manifest/manifest/view' : 'ew/express-waybill/view') + '&id=' + item['id'];
    var uniqueTabId = ((item['doc_type_num'] == 1) ? 'manifestview' : 'expresswaybillview') + item['id'];
    parent.application_create_new_tab(tabName, url, uniqueTabId, 'false', thisIframeId, 'false');

});

$("#receiver_data_copy").click(function(){
    $('#closing_sending_receiver').val($('#receiver_cp_contactpers').val());
    $('#closing_receiver_post_id').val($('#receiver_cp_job_position_ide').val()).attr('readonly','readonly');
});

$( "#closing_sending_receiver" ).change(function() {
    $('#closing_receiver_post_id').val(null).removeAttr('readonly');
});
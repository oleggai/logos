/**
 * Created by goga on 10.04.2015.
 */

var in_int_delivery_cost_full_uah1 = $('#int_delivery_cost_full_uah1');
var in_int_delivery_cost_full1 = $('#int_delivery_cost_full1');
var in_int_delivery_cost_full_uah2 = $('#int_delivery_cost_full_uah2');
var in_int_delivery_cost_full2 = $('#int_delivery_cost_full2');
var in_int_delivery_cost_css_uah = $('#expresswaybill-int_delivery_cost_css_uah');
var in_int_delivery_cost_css_usd = $('#expresswaybill-int_delivery_cost_css_usd');
var in_clearance_cost = $('#expresswaybill-clearance_cost');
var in_customs_clearance_charge = $('#expresswaybill-customs_clearance_charge');
var in_total_pay_cost_uah = $('#expresswaybill-total_pay_cost_uah');
var in_third_party_ccs = $('#expresswaybill-third_party_ccs');
var in_int_delivery_full_currency2 = $('#int_delivery_full_currency2');
var in_int_delivery_full_currency1 = $('#int_delivery_full_currency1');

var in_shipment_type = $('#expresswaybill-shipment_type');
var in_cargo_est_weight_kg = $('#expresswaybill-cargo_est_weight_kg');
var in_customs_declaration_cost = $('#expresswaybill-customs_declaration_cost');
var in_customs_declaration_currency = $('#expresswaybill-customs_declaration_currency');
var in_declared_cost = $('#expresswaybill-declared_cost');
var in_date = $('#expresswaybill-_date');
var in_delivery_type = $('#expresswaybill-delivery_type');
var in_shipment_format = $('#expresswaybill-shipment_format');
var in_service_type = $('#service_type');
var in_sender_counterparty_id = $('#sender_counterparty_id');
var in_receiver_counterparty_id = $('#receiver_counterparty_id');

// Запрос АПИ расчёта цены
function get_cost_full(clear_currency) {
    if (clear_currency)
        currency = '';
    else
        currency = in_int_delivery_full_currency1.val();
    var data = {
        appKey: "internal-app-key",
        apiKey: "e5a303bc-ec5c-40b5-b504-1bcbfdd29f87",
        modelName: "ewCost",
        calledMethod: "calculate",
        methodProperties: {
            0: {
                currency: currency,
                cust_declare_currency: in_customs_declaration_currency.val(),
                sender_cp_address_id: $('#sender_cp_address_id').val(),
                receiver_cp_address_id: $('#receiver_cp_address_id').val(),
                cargo_type_id: in_shipment_type.val(),
                calc_weight: in_cargo_est_weight_kg.val(),
                cust_declare_cost: in_customs_declaration_cost.val(),
                cost_premium_cust_declare: in_declared_cost.val(),
                date_action: in_date.val(),
                service_type: in_service_type.val(),
                delivery_type: in_delivery_type.val(),
                shipment_format: in_shipment_format.val()
            }
        }};
    var map = data.methodProperties[0];
    for (var x in map) {
        if (!map[x] || map[x] === 'undefined') {
            if (x != 'currency') {
                text = "<span style='color:red'>" + tr('Not set') + ": " + tr(x) + "</span><br>";
                $('#tariff_flash').html(text);
                return false;
            }
        }
    }
    $.ajax({
        type: "POST",
        url: "index.php?r=api/json",
        dataType: "json",
        async: true,
        data: JSON.stringify(data),
        success: function (data) {
            var text = '';
            for (e in data.errors)
                text += "<span style='color:red'>" + data.errors[e]['error_msg'] + "</span><br>";
            $('#tariff_flash').html(text);
            if (data.success === 'false')
                return false;
            console.dir(data);
            in_int_delivery_cost_full1.val(data.data.cost);
            in_int_delivery_cost_full2.val(data.data.cost);
            if (clear_currency)
                in_int_delivery_full_currency1.val(data.data.currency_id);
            if (clear_currency)
                in_int_delivery_full_currency2.val(data.data.currency_id);
            in_int_delivery_cost_full_uah1.val(data.data.cost_uah);
            in_int_delivery_cost_full_uah2.val(data.data.cost_uah);
            for (i in data.info)
                text += data.info[i]['info_msg'] + '<br>'
            $('#tariff_flash').html(text);
            return true;
        }
    });
}

// Полная, грн
function calc_cost_full_uah() {

    var rate = getExRateId(in_int_delivery_full_currency2.val(), "3");
    if (isNaN(rate))
        return;
    var float_val = parseFloat(in_int_delivery_cost_full2.val());
    if (!isNaN(float_val)) {
        in_int_delivery_cost_full_uah2.val((float_val * rate).toFixed(2));
    }
}

// До ЦСС, доллар
function calc_cost_css_usd() {

    var rate = getExRateId(in_int_delivery_full_currency2.val(), "3");
    if (isNaN(rate))
        return;
    var float_val_css = parseFloat(in_int_delivery_cost_css_uah.val());
    var float_val_full = parseFloat(in_int_delivery_cost_full2.val());
    if (!isNaN(float_val_css) && !isNaN(float_val_full) && rate != 0) {
        in_int_delivery_cost_css_usd.val((float_val_full - float_val_css / rate).toFixed(2));
    }
}

// Итого стоимость к оплате, грн
function calc_total_pay_cost_uah() {

    var float_val_full = parseFloat(in_int_delivery_cost_full_uah2.val());
    var float_val_clearance = parseFloat(in_clearance_cost.val());
    var float_val_clearance_charge = parseFloat(in_customs_clearance_charge.val());
    if (!isNaN(float_val_full) && !isNaN(float_val_clearance) && !isNaN(float_val_clearance_charge)) {
        in_total_pay_cost_uah.val((float_val_full + float_val_clearance + float_val_clearance_charge).toFixed(2));
    }
}

function set_label_full_usd() {
    in_int_delivery_full_currency1.val(in_int_delivery_full_currency2.val());
    in_int_delivery_cost_full1.val(in_int_delivery_cost_full2.val());
    in_int_delivery_cost_full_uah1.val(in_int_delivery_cost_full_uah2.val());
}

function set_label_full_uah() {
    in_int_delivery_cost_full_uah1.val(in_int_delivery_cost_full_uah2.val());
}

$('#expresswaybill-int_delivery_payer').change(function () {
    var cost_third_party = $("#cost_third_party_id");
    var cost_third_party_btn = $("#cost_third_party_btn");
    //с основной закладки
    $('#expresswaybill-payer_type').val(this.value).change();
    var third_party = $("#payer_third_party_id");
    var third_party_btn = $("#third_party_btn");
    if (this.value != payer_type_thirdparty) {
        cost_third_party.val('').change();
        cost_third_party_btn.hide();
        third_party.val('').change();
        third_party_btn.hide();
    }
    else {
        cost_third_party_btn.show();
        third_party_btn.show();
    }
}).change();

$("#expresswaybill-int_delivery_payment_type").change(function(){
    $("#expresswaybill-payer_payment_type").val(this.value).change();
});

$('#expresswaybill-clearance_payer').change(function () {

    var msg = '';

    //если выбрано третье лицо, то проверяем может ли отправитель или получатель платить третьим лицом
    if (this.value == 3){
        if ($('#sender_thirdparty').val() === '0' && $('#receiver_thirdparty').val() === '0')
            msg = 'третье лицо недопутимо';
    }

    //проверки можно ли платить по безналу
    if ($("#expresswaybill-clearance_payment_type").val() == 2){
        if (this.value == 1  && $('#sender_clearing').val() === '0') //отправитель
            msg = 'отправитель не платит безналом';
        if (this.value == 2 && $('#receiver_clearing').val() === '0') //получатель
            msg = 'получатель не платит безналом';
        if (this.value == 3 && $('#cost_third_party_ccs_clearing').val() === '0') //третье лицо
            msg = 'третье лицо не платит безналом';
    }

    if (msg){
        show_app_alert('Ошибка', msg, 'Ok');
        this.value = '';
    }

    var third_party = $("#cost_third_party_ccs_id");
    var third_party_btn = $("#cost_third_party_ccs_btn");
    if (this.value != payer_type_thirdparty) {
        third_party.val('').change();
        third_party_btn.hide();
    }
    else {
        third_party_btn.show();
    }
}).change();

$("#expresswaybill-clearance_payment_type").change(function(){

    //проверки можно ли платить по безналу
    if (this.value == 2){
        var msg = '';
        if ($('#expresswaybill-clearance_payer').val() == 1  && $('#sender_clearing').val() === '0') //отправитель
            msg = 'отправитель не платит безналом';
        if ($('#expresswaybill-clearance_payer').val() == 2 && $('#receiver_clearing').val() === '0') //получатель
            msg = 'получатель не платит безналом';
        if ($('#expresswaybill-clearance_payer').val() == 3 && $('#cost_third_party_ccs_clearing').val() === '0') //третье лицо
            msg = 'третье лицо не платит безналом';
        if (msg){
            show_app_alert('Ошибка', msg, 'Ok');
            this.value = '';
        }
    }
});


in_int_delivery_full_currency2.change(function () {
    calc_cost_full_uah();
    calc_cost_css_usd();
    set_label_full_usd();
});
in_int_delivery_cost_full2.change(function () {
    calc_cost_full_uah();
    calc_cost_css_usd();
    set_label_full_usd();
});
in_int_delivery_full_currency1.change(function () {
    get_cost_full(false);
    in_int_delivery_full_currency2.val(in_int_delivery_full_currency1.val());
    calc_cost_full_uah();
    calc_cost_css_usd();
    set_label_full_usd();
});
in_int_delivery_cost_full_uah2.change(set_label_full_uah);
in_int_delivery_cost_css_uah.change(calc_cost_css_usd);
in_int_delivery_cost_full_uah2.change(calc_total_pay_cost_uah);
in_clearance_cost.change(calc_total_pay_cost_uah);
in_customs_clearance_charge.change(calc_total_pay_cost_uah);

in_int_delivery_cost_full1.change(function () {
    in_int_delivery_cost_full2.val(in_int_delivery_cost_full1.val()).change();
});
in_int_delivery_cost_full_uah1.change(function () {
    in_int_delivery_cost_full_uah2.val(in_int_delivery_cost_full_uah1.val()).change();
});

grid_addservice.attachEvent('onAfterEditStop', function (state, editor) {
    var item = this.getItem(editor.row);
    item['third_party_clearing'] = item['third_party_id'];

    if (editor.column == 'payer' || editor.column == 'form_pay' || editor.column == 'third_party_id') {
        var payer = item['payer'];
        var payment_type = item['form_pay'];
        var third_party = item['third_party_id'];
        var clearing = this.getText(editor.row, "third_party_clearing");
        if (third_party && !clearing)
            clearing = 0;

        var msg = '';

        //если выбрано третье лицо, то проверяем может ли отправитель или получатель платить третьим лицом
        if (payer == payer_type_thirdparty){
            if ($('#sender_thirdparty').val() === '0' && $('#receiver_thirdparty').val() === '0')
                msg = 'третье лицо недопутимо';
        }

        //проверки можно ли платить по безналу
        if (payment_type == 2){
            if (payer == 1  && $('#sender_clearing').val() === '0') //отправитель
                msg = 'отправитель не платит безналом';
            if (payer == 2 && $('#receiver_clearing').val() === '0') //получатель
                msg = 'получатель не платит безналом';
            if (payer == 3 && clearing === 0) //третье лицо
                msg = 'третье лицо не платит безналом';
        }

        if (msg){
            show_app_alert('Ошибка', msg, 'Ok');
            if (editor.column == 'payer')
                item['payer'] = '';
            else if (editor.column == 'form_pay')
                item['form_pay'] = '';
            else {
                item['third_party_id'] = '';
                item['third_party_clearing'] = '';
            }

        }

        if (payer != payer_type_thirdparty) {
            item['third_party_id'] = '';
            item['third_party_clearing'] = '';
        }
    }



});
grid_addservice.attachEvent('onBeforeEditStart', function (id) {
    var item = this.getItem(id.row);
    var payer = item['payer'];
    if (id.column == 'third_party_id' && payer != payer_type_thirdparty) {
        return false;
    }
});

$('.tariff_reload').click(function () {
    get_cost_full(true);
});
in_customs_declaration_currency.change(function () {
    get_cost_full(in_int_delivery_full_currency1.val() === '');
});
in_customs_declaration_cost.change(function () {
    get_cost_full(in_int_delivery_full_currency1.val() === '');
});

//третье лицо---------------------------
function costThirdPartyIdChange () {
    var costThirdPartyId = $('#cost_third_party_id');
    var costThirdPartyCounterpartyName = $('#cost_third_party_counterparty_name');
    var costThirdPartyClearing = $('#cost_third_party_clearing');

    if (costThirdPartyId.val() && costThirdPartyId.val() != "") {
        jQuery.getJSON(url_get_counterparty, {id: costThirdPartyId.val()},
            (function (item1, item2) {
                return function (data) {
                    //проверки можно ли платить по безналу
                    if ($("#expresswaybill-int_delivery_payment_type").val() == 2){
                        if ($('#expresswaybill-int_delivery_payer').val() == 3  && data['clearing'] === 0) //третье лицо
                            $('#cost_third_party_id').val('');
                        return;
                    }

                    item1.val(data['counterparty_name']);
                    item2.val(data['clearing']);
                };
            }(costThirdPartyCounterpartyName, costThirdPartyClearing)
            )
        );
    }
    else {
        costThirdPartyCounterpartyName.val("").trigger('input');
        costThirdPartyClearing.val("");
    }
}

$('#cost_third_party_id').change(function () {
    costThirdPartyIdChange();
    $('#payer_third_party_id').val(this.value);
    payerThirdPartyIdChange();
});

var costThirdPartyCcsId = $('#cost_third_party_ccs_id');
var costThirdPartyCcsCounterpartyName = $('#cost_third_party_ccs_counterparty_name');
var costThirdPartyCcsClearing = $('#cost_third_party_ccs_clearing');

costThirdPartyCcsId.change(function () {
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_counterparty, {id: this.value},
        (function (item1, item2) {
            return function (data) {

                var msg = '';
                //проверки можно ли платить по безналу
                if ($("#expresswaybill-clearance_payment_type").val() == 2){
                    if ($('#expresswaybill-clearance_payer').val() == 3  && data['clearing'] === 0) //третье лицо
                        msg = 'необходима возможность оплаты безналом';
                }

                if (msg){
                    show_app_alert('Ошибка', msg, 'Ok');
                    $('#cost_third_party_ccs_id').val('');
                    return;
                }

                item1.val(data['counterparty_name']);
                item2.val(data['clearing']);
            };
        }(costThirdPartyCcsCounterpartyName, costThirdPartyCcsClearing)
        )
        );
    }
    else {
        costThirdPartyCcsCounterpartyName.val("").trigger('input');
    }
});
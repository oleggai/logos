/**
 * Created by Hopr on 06.07.2015.
 */

//id контрагента
var cpId = $('#cp_id');

var privat_pers_form = $('#privat_pers_form');
var legal_entity_form = $('#legal_entity_form');
var subsidiary = $('#subsidiary');
var person_type = $('#person_type');
var bookkeeping_tab_header = $('#bookkeeping_tab_header');
var marketing_tab_header = $('#marketing_tab_header');

var pers_doc_type = $('#pers_doc_type');
var pers_doc_serial_num = $('#counterparty-counterpartypersdocs-doc_serial_num');
var pers_doc_num = $('#counterparty-counterpartypersdocs-doc_num');

//смена типа контрагента
person_type.change(function () {
    if (this.value == 1) {
        privat_pers_form.show();
        legal_entity_form.hide();
        subsidiary.hide();
        marketing_tab_header.hide();
        bookkeeping_tab_header.hide();
    }
    else {
        if (this.value == 2) {
            legal_entity_form.show();
            subsidiary.show();
            marketing_tab_header.show();
            bookkeeping_tab_header.show();
            privat_pers_form.hide();
        }
        else {
            legal_entity_form.hide();
            subsidiary.hide();
            marketing_tab_header.hide();
            bookkeeping_tab_header.hide();
            privat_pers_form.hide();
        }
    }
}).change();

//поля КА
var txtCPNameEn = $('#CPNameEn');
var txtCPNameUk = $('#CPNameUk');
var txtCPNameRu = $('#CPNameRu');

//поля физ лица
var txtCounterpartySurnameEn = $('#counterpartySurnameEn');
var txtCounterpartyNameEn = $('#counterpartyNameEn');
var txtCounterpartySecondnameEn = $('#counterpartySecondnameEn');
var txtCounterpartyFullNameEn = $('#counterpartyFullNameEn');
var txtCounterpartyShortNameEn = $('#counterpartyShortNameEn');
var txtCounterpartyManualNameEn = $('#counterpartyManualNameEn');
var txtCounterpartyDisplayNameEn = $('#counterpartyDisplayNameEn');

var txtCounterpartySurnameUk = $('#counterpartySurnameUk');
var txtCounterpartyNameUk = $('#counterpartyNameUk');
var txtCounterpartySecondnameUk = $('#counterpartySecondnameUk');
var txtCounterpartyFullNameUk = $('#counterpartyFullNameUk');
var txtCounterpartyShortNameUk = $('#counterpartyShortNameUk');
var txtCounterpartyManualNameUk = $('#counterpartyManualNameUk');
var txtCounterpartyDisplayNameUk = $('#counterpartyDisplayNameUk');

var txtCounterpartySurnameRu = $('#counterpartySurnameRu');
var txtCounterpartyNameRu = $('#counterpartyNameRu');
var txtCounterpartySecondnameRu = $('#counterpartySecondnameRu');
var txtCounterpartyFullNameRu = $('#counterpartyFullNameRu');
var txtCounterpartyShortNameRu = $('#counterpartyShortNameRu');
var txtCounterpartyManualNameRu = $('#counterpartyManualNameRu');
var txtCounterpartyDisplayNameRu = $('#counterpartyDisplayNameRu');

//поля юр лица
var txtCounterpartyLEFullNameEN = $('#counterpartyLEFullNameEN');
var txtCounterpartyLEShortNameEN = $('#counterpartyLEShortNameEN');
var txtCounterpartyLEManualNameEN = $('#counterpartyLEManualNameEN');
var txtCounterpartyLEDisplayNameEN = $('#counterpartyLEDisplayNameEN');

var txtCounterpartyLEFullNameUK = $('#counterpartyLEFullNameUK');
var txtCounterpartyLEShortNameUK = $('#counterpartyLEShortNameUK');
var txtCounterpartyLEManualNameUK = $('#counterpartyLEManualNameUK');
var txtCounterpartyLEDisplayNameUK = $('#counterpartyLEDisplayNameUK');

var txtCounterpartyLEFullNameRU = $('#counterpartyLEFullNameRU');
var txtCounterpartyLEShortNameRU = $('#counterpartyLEShortNameRU');
var txtCounterpartyLEManualNameRU = $('#counterpartyLEManualNameRU');
var txtCounterpartyLEDisplayNameRU = $('#counterpartyLEDisplayNameRU');

//автозаполнение полей названия КА
txtCounterpartyLEDisplayNameEN.change(function() {
    txtCPNameEn.val(this.value);
});

txtCounterpartyLEDisplayNameUK.change(function() {
    txtCPNameUk.val(this.value);
});

txtCounterpartyLEDisplayNameRU.change(function() {
    txtCPNameRu.val(this.value);
});

txtCounterpartyDisplayNameEn.change(function() {
    txtCPNameEn.val(this.value);
});

txtCounterpartyDisplayNameUk.change(function() {
    txtCPNameUk.val(this.value);
});

txtCounterpartyDisplayNameRu.change(function() {
    txtCPNameRu.val(this.value);
});

//автозаполнение полей юр лиц
txtCounterpartyLEFullNameEN.change(function() {
    if (txtCounterpartyLEShortNameEN.val() == '')
        txtCounterpartyLEShortNameEN.val(txtCounterpartyLEFullNameEN.val());
    if (txtCounterpartyLEFullNameEN.val() == '')
        txtCounterpartyLEDisplayNameEN.val(txtCounterpartyLEManualNameEN.val());
    else
        txtCounterpartyLEDisplayNameEN.val(txtCounterpartyLEFullNameEN.val());
    txtCounterpartyLEDisplayNameEN.change();
});

txtCounterpartyLEFullNameUK.change(function() {
    if (txtCounterpartyLEShortNameUK.val() == '')
        txtCounterpartyLEShortNameUK.val(txtCounterpartyLEFullNameUK.val());
    if (txtCounterpartyLEFullNameUK.val() == '')
        txtCounterpartyLEDisplayNameUK.val(txtCounterpartyLEManualNameUK.val());
    else
        txtCounterpartyLEDisplayNameUK.val(txtCounterpartyLEFullNameUK.val());
    txtCounterpartyLEDisplayNameUK.change();
});

txtCounterpartyLEFullNameRU.change(function() {
    if (txtCounterpartyLEShortNameRU.val() == '')
        txtCounterpartyLEShortNameRU.val(txtCounterpartyLEFullNameRU.val());
    if (txtCounterpartyLEFullNameRU.val() == '')
        txtCounterpartyLEDisplayNameRU.val(txtCounterpartyLEManualNameRU.val());
    else
        txtCounterpartyLEDisplayNameRU.val(txtCounterpartyLEFullNameRU.val());
    txtCounterpartyLEDisplayNameRU.change();
});

//---
txtCounterpartyLEManualNameEN.change(function() {
    txtCounterpartyLEFullNameEN.change();
});

txtCounterpartyLEManualNameUK.change(function() {
    txtCounterpartyLEFullNameUK.change();
});

txtCounterpartyLEManualNameRU.change(function() {
    txtCounterpartyLEFullNameRU.change();
});

//автозаполнение полей физ лица
function formatFullName(surname, name, secondName) {
    if (surname == '' || name == '')
        return '';

    if (secondName == '')
        return surname + ' ' + name;

    return surname + ' ' + name + ' ' + secondName;
}

function formatShortName(surname, name, secondName) {
    if (surname == '' || name == '')
        return '';

    if (secondName == '')
        return surname + ' ' + name.substring(0, 1) + '. ';

    return surname + ' ' + name.substring(0, 1) + '. ' + secondName.substring(0, 1) + '.';
}

txtCounterpartySurnameEn.change(function() {
    txtCounterpartyFullNameEn.val(formatFullName(txtCounterpartySurnameEn.val(), txtCounterpartyNameEn.val(),
        txtCounterpartySecondnameEn.val()));
    txtCounterpartyShortNameEn.val(formatShortName(txtCounterpartySurnameEn.val(), txtCounterpartyNameEn.val(),
        txtCounterpartySecondnameEn.val()));
    txtCounterpartyFullNameEn.change();
});
txtCounterpartyNameEn.change(function() { txtCounterpartySurnameEn.change(); });
txtCounterpartySecondnameEn.change(function() { txtCounterpartySurnameEn.change(); });
txtCounterpartyFullNameEn.change(function() {
    if (txtCounterpartyFullNameEn.val() == '')
        txtCounterpartyDisplayNameEn.val(txtCounterpartyManualNameEn.val());
    else
        txtCounterpartyDisplayNameEn.val(txtCounterpartyFullNameEn.val());
    txtCounterpartyDisplayNameEn.change();
});
txtCounterpartyManualNameEn.change(function() {
    txtCounterpartyFullNameEn.change();
});

txtCounterpartySurnameUk.change(function() {
    txtCounterpartyFullNameUk.val(formatFullName(txtCounterpartySurnameUk.val(), txtCounterpartyNameUk.val(),
        txtCounterpartySecondnameUk.val()));
    txtCounterpartyShortNameUk.val(formatShortName(txtCounterpartySurnameUk.val(), txtCounterpartyNameUk.val(),
        txtCounterpartySecondnameUk.val()));
    txtCounterpartyFullNameUk.change();
});
txtCounterpartyNameUk.change(function() { txtCounterpartySurnameUk.change(); });
txtCounterpartySecondnameUk.change(function() { txtCounterpartySurnameUk.change(); });
txtCounterpartyFullNameUk.change(function() {
    if (txtCounterpartyFullNameUk.val() == '')
        txtCounterpartyDisplayNameUk.val(txtCounterpartyManualNameUk.val());
    else
        txtCounterpartyDisplayNameUk.val(txtCounterpartyFullNameUk.val());
    txtCounterpartyDisplayNameUk.change();
});
txtCounterpartyManualNameUk.change(function() {
    txtCounterpartyFullNameUk.change();
});

txtCounterpartySurnameRu.change(function() {
    txtCounterpartyFullNameRu.val(formatFullName(txtCounterpartySurnameRu.val(), txtCounterpartyNameRu.val(),
        txtCounterpartySecondnameRu.val()));
    txtCounterpartyShortNameRu.val(formatShortName(txtCounterpartySurnameRu.val(), txtCounterpartyNameRu.val(),
        txtCounterpartySecondnameRu.val()));
    txtCounterpartyFullNameRu.change();
});
txtCounterpartyNameRu.change(function() { txtCounterpartySurnameRu.change(); });
txtCounterpartySecondnameRu.change(function() { txtCounterpartySurnameRu.change(); });
txtCounterpartyFullNameRu.change(function() {
    if (txtCounterpartyFullNameRu.val() == '')
        txtCounterpartyDisplayNameRu.val(txtCounterpartyManualNameRu.val());
    else
        txtCounterpartyDisplayNameRu.val(txtCounterpartyFullNameRu.val());
    txtCounterpartyDisplayNameRu.change();
});
txtCounterpartyManualNameRu.change(function() {
    txtCounterpartyFullNameRu.change();
});

//транслитерация
function Transliterate(element, txt, src, tar){
    jQuery.getJSON(url_get_transliterate, { text:txt, source:src, target:tar},
        (function(this_item) {
            return function(data) {
                this_item.val(data);
                if (data)
                    this_item.change();
            };
        }(element)
        )
    );
}

//транслитерация для юр лица
var legalEntityTransliterateButton = $('#legal_entity_transliterate_button');
var counterpartyLegalEntityENTab = $('#counterparty_legal_entity_en_tab');
var counterpartyLegalEntityUKTab = $('#counterparty_legal_entity_uk_tab');
var counterpartyLegalEntityRUTab = $('#counterparty_legal_entity_ru_tab');

if (read_only)
    legalEntityTransliterateButton.hide();

legalEntityTransliterateButton.click(function() {
    if (counterpartyLegalEntityENTab.hasClass('active')) {
        if (txtCounterpartyLEFullNameUK.val() == '')
            Transliterate(txtCounterpartyLEFullNameUK, txtCounterpartyLEFullNameEN.val(), 'en', 'uk');
        if (txtCounterpartyLEFullNameRU.val() == '')
            Transliterate(txtCounterpartyLEFullNameRU, txtCounterpartyLEFullNameEN.val(), 'en', 'ru');

        if (txtCounterpartyLEShortNameUK.val() == '')
            Transliterate(txtCounterpartyLEShortNameUK, txtCounterpartyLEShortNameEN.val(), 'en', 'uk');
        if (txtCounterpartyLEShortNameRU.val() == '')
            Transliterate(txtCounterpartyLEShortNameRU, txtCounterpartyLEShortNameEN.val(), 'en', 'ru');

        if (txtCounterpartyLEManualNameUK.val() == '')
            Transliterate(txtCounterpartyLEManualNameUK, txtCounterpartyLEManualNameEN.val(), 'en', 'uk');
        if (txtCounterpartyLEManualNameRU.val() == '')
            Transliterate(txtCounterpartyLEManualNameRU, txtCounterpartyLEManualNameEN.val(), 'en', 'ru');
    }

    if (counterpartyLegalEntityUKTab.hasClass('active')) {
        if (txtCounterpartyLEFullNameEN.val() == '')
            Transliterate(txtCounterpartyLEFullNameEN, txtCounterpartyLEFullNameUK.val(), 'uk', 'en');
        if (txtCounterpartyLEFullNameRU.val() == '')
            Transliterate(txtCounterpartyLEFullNameRU, txtCounterpartyLEFullNameUK.val(), 'uk', 'ru');

        if (txtCounterpartyLEShortNameEN.val() == '')
            Transliterate(txtCounterpartyLEShortNameEN, txtCounterpartyLEShortNameUK.val(), 'uk', 'en');
        if (txtCounterpartyLEShortNameRU.val() == '')
            Transliterate(txtCounterpartyLEShortNameRU, txtCounterpartyLEShortNameUK.val(), 'uk', 'ru');

        if (txtCounterpartyLEManualNameEN.val() == '')
            Transliterate(txtCounterpartyLEManualNameEN, txtCounterpartyLEManualNameUK.val(), 'uk', 'en');
        if (txtCounterpartyLEManualNameRU.val() == '')
            Transliterate(txtCounterpartyLEManualNameRU, txtCounterpartyLEManualNameUK.val(), 'uk', 'ru');
    }

    if (counterpartyLegalEntityRUTab.hasClass('active')) {
        if (txtCounterpartyLEFullNameEN.val() == '')
            Transliterate(txtCounterpartyLEFullNameEN, txtCounterpartyLEFullNameRU.val(), 'ru', 'en');
        if (txtCounterpartyLEFullNameUK.val() == '')
            Transliterate(txtCounterpartyLEFullNameUK, txtCounterpartyLEFullNameRU.val(), 'ru', 'uk');

        if (txtCounterpartyLEShortNameEN.val() == '')
            Transliterate(txtCounterpartyLEShortNameEN, txtCounterpartyLEShortNameRU.val(), 'ru', 'en');
        if (txtCounterpartyLEShortNameUK.val() == '')
            Transliterate(txtCounterpartyLEShortNameUK, txtCounterpartyLEShortNameRU.val(), 'ru', 'uk');

        if (txtCounterpartyLEManualNameEN.val() == '')
            Transliterate(txtCounterpartyLEManualNameEN, txtCounterpartyLEManualNameRU.val(), 'ru', 'en');
        if (txtCounterpartyLEManualNameUK.val() == '')
            Transliterate(txtCounterpartyLEManualNameUK, txtCounterpartyLEManualNameRU.val(), 'ru', 'uk');
    }
});

//транслитерация для физ лица
var privatPersTransliterateButton = $('#privat_pers_transliterate_button');
var counterpartyPrivatPersENTab = $('#counterparty_privat_pers_en_tab');
var counterpartyPrivatPersUKTab = $('#counterparty_privat_pers_uk_tab');
var counterpartyPrivatPersRUTab = $('#counterparty_privat_pers_ru_tab');

if (read_only)
    privatPersTransliterateButton.hide();

privatPersTransliterateButton.click(function() {
    if (counterpartyPrivatPersENTab.hasClass('active')) {
        if (txtCounterpartySurnameUk.val() == '')
            Transliterate(txtCounterpartySurnameUk, txtCounterpartySurnameEn.val(), 'en', 'uk');
        if (txtCounterpartySurnameRu.val() == '')
            Transliterate(txtCounterpartySurnameRu, txtCounterpartySurnameEn.val(), 'en', 'ru');

        if (txtCounterpartyNameUk.val() == '')
            Transliterate(txtCounterpartyNameUk, txtCounterpartyNameEn.val(), 'en', 'uk');
        if (txtCounterpartyNameRu.val() == '')
            Transliterate(txtCounterpartyNameRu, txtCounterpartyNameEn.val(), 'en', 'ru');

        if (txtCounterpartySecondnameUk.val() == '')
            Transliterate(txtCounterpartySecondnameUk, txtCounterpartySecondnameEn.val(), 'en', 'uk');
        if (txtCounterpartySecondnameRu.val() == '')
            Transliterate(txtCounterpartySecondnameRu, txtCounterpartySecondnameEn.val(), 'en', 'ru');

        if (txtCounterpartyShortNameUk.val() == '')
            Transliterate(txtCounterpartyShortNameUk, txtCounterpartyShortNameEn.val(), 'en', 'uk');
        if (txtCounterpartyShortNameRu.val() == '')
            Transliterate(txtCounterpartyShortNameRu, txtCounterpartyShortNameEn.val(), 'en', 'ru');

        if (txtCounterpartyFullNameUk.val() == '')
            Transliterate(txtCounterpartyFullNameUk, txtCounterpartyFullNameEn.val(), 'en', 'uk');
        if (txtCounterpartyFullNameRu.val() == '')
            Transliterate(txtCounterpartyFullNameRu, txtCounterpartyFullNameEn.val(), 'en', 'ru');

        if (txtCounterpartyManualNameUk.val() == '')
            Transliterate(txtCounterpartyManualNameUk, txtCounterpartyManualNameEn.val(), 'en', 'uk');
        if (txtCounterpartyManualNameRu.val() == '')
            Transliterate(txtCounterpartyManualNameRu, txtCounterpartyManualNameEn.val(), 'en', 'ru');
/*
        if (txtCounterpartyDisplayNameUk.val() == '')
            Transliterate(txtCounterpartyDisplayNameUk, txtCounterpartyDisplayNameEn.val(), 'en', 'uk');
        if (txtCounterpartyDisplayNameRu.val() == '')
            Transliterate(txtCounterpartyDisplayNameRu, txtCounterpartyDisplayNameEn.val(), 'en', 'ru');
*/
    }
    if (counterpartyPrivatPersUKTab.hasClass('active')) {
        if (txtCounterpartySurnameEn.val() == '')
            Transliterate(txtCounterpartySurnameEn, txtCounterpartySurnameUk.val(), 'uk', 'en');
        if (txtCounterpartySurnameRu.val() == '')
            Transliterate(txtCounterpartySurnameRu, txtCounterpartySurnameUk.val(), 'uk', 'ru');

        if (txtCounterpartyNameEn.val() == '')
            Transliterate(txtCounterpartyNameEn, txtCounterpartyNameUk.val(), 'uk', 'en');
        if (txtCounterpartyNameRu.val() == '')
            Transliterate(txtCounterpartyNameRu, txtCounterpartyNameUk.val(), 'uk', 'ru');

        if (txtCounterpartySecondnameEn.val() == '')
            Transliterate(txtCounterpartySecondnameEn, txtCounterpartySecondnameUk.val(), 'uk', 'en');
        if (txtCounterpartySecondnameRu.val() == '')
            Transliterate(txtCounterpartySecondnameRu, txtCounterpartySecondnameUk.val(), 'uk', 'ru');

        if (txtCounterpartyShortNameEn.val() == '')
            Transliterate(txtCounterpartyShortNameEn, txtCounterpartyShortNameUk.val(), 'uk', 'en');
        if (txtCounterpartyShortNameRu.val() == '')
            Transliterate(txtCounterpartyShortNameRu, txtCounterpartyShortNameUk.val(), 'uk', 'ru');

        if (txtCounterpartyFullNameEn.val() == '')
            Transliterate(txtCounterpartyFullNameEn, txtCounterpartyFullNameUk.val(), 'uk', 'en');
        if (txtCounterpartyFullNameRu.val() == '')
            Transliterate(txtCounterpartyFullNameRu, txtCounterpartyFullNameUk.val(), 'uk', 'ru');

        if (txtCounterpartyManualNameEn.val() == '')
            Transliterate(txtCounterpartyManualNameEn, txtCounterpartyManualNameUk.val(), 'uk', 'en');
        if (txtCounterpartyManualNameRu.val() == '')
            Transliterate(txtCounterpartyManualNameRu, txtCounterpartyManualNameUk.val(), 'uk', 'ru');
/*
        if (txtCounterpartyDisplayNameEn.val() == '')
            Transliterate(txtCounterpartyDisplayNameEn, txtCounterpartyDisplayNameUk.val(), 'uk', 'en');
        if (txtCounterpartyDisplayNameRu.val() == '')
            Transliterate(txtCounterpartyDisplayNameRu, txtCounterpartyDisplayNameUk.val(), 'uk', 'ru');
*/
    }
    if (counterpartyPrivatPersRUTab.hasClass('active')) {
        if (txtCounterpartySurnameEn.val() == '')
            Transliterate(txtCounterpartySurnameEn, txtCounterpartySurnameRu.val(), 'ru', 'en');
        if (txtCounterpartySurnameUk.val() == '')
            Transliterate(txtCounterpartySurnameUk, txtCounterpartySurnameRu.val(), 'ru', 'uk');

        if (txtCounterpartyNameEn.val() == '')
            Transliterate(txtCounterpartyNameEn, txtCounterpartyNameRu.val(), 'ru', 'en');
        if (txtCounterpartyNameUk.val() == '')
            Transliterate(txtCounterpartyNameUk, txtCounterpartyNameRu.val(), 'ru', 'uk');

        if (txtCounterpartySecondnameEn.val() == '')
            Transliterate(txtCounterpartySecondnameEn, txtCounterpartySecondnameRu.val(), 'ru', 'en');
        if (txtCounterpartySecondnameUk.val() == '')
            Transliterate(txtCounterpartySecondnameUk, txtCounterpartySecondnameRu.val(), 'ru', 'uk');

        if (txtCounterpartyShortNameEn.val() == '')
            Transliterate(txtCounterpartyShortNameEn, txtCounterpartyShortNameRu.val(), 'ru', 'en');
        if (txtCounterpartyShortNameUk.val() == '')
            Transliterate(txtCounterpartyShortNameUk, txtCounterpartyShortNameRu.val(), 'ru', 'uk');

        if (txtCounterpartyFullNameEn.val() == '')
            Transliterate(txtCounterpartyFullNameEn, txtCounterpartyFullNameRu.val(), 'ru', 'en');
        if (txtCounterpartyFullNameUk.val() == '')
            Transliterate(txtCounterpartyFullNameUk, txtCounterpartyFullNameRu.val(), 'ru', 'uk');

        if (txtCounterpartyManualNameEn.val() == '')
            Transliterate(txtCounterpartyManualNameEn, txtCounterpartyManualNameRu.val(), 'ru', 'en');
        if (txtCounterpartyManualNameUk.val() == '')
            Transliterate(txtCounterpartyManualNameUk, txtCounterpartyManualNameRu.val(), 'ru', 'uk');

/*
        if (txtCounterpartyDisplayNameEn.val() == '')
            Transliterate(txtCounterpartyDisplayNameEn, txtCounterpartyDisplayNameRu.val(), 'ru', 'en');
        if (txtCounterpartyDisplayNameUk.val() == '')
            Transliterate(txtCounterpartyDisplayNameUk, txtCounterpartyDisplayNameRu.val(), 'ru', 'uk');
*/
    }
});

//страна-город-подразделение
var select_country = $('[name="Counterparty[country_fixation]"]');
var select_cities = $('[name="Counterparty[city_fixation]"]');
var select_warehouse = $('[name="Counterparty[warehouse_fixation]"]');

function clearSelect(select){

    var options = select.data('select2').options.options;
    select.html('');
    options.data = [];
    select.select2(options).change();
}

function loadCities(country_ref){

    jQuery.getJSON(url_get_cities, { country:country_ref}, function(data) {

        var cities = select_cities;
        var options = cities.data('select2').options.options;
        cities.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            cities.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        cities.select2(options).change();
    });
}

function loadWarehouses(city_ref){

    jQuery.getJSON(url_get_warehouses, { city:city_ref}, function(data) {

        var warehouses = select_warehouse;
        var options = warehouses.data('select2').options.options;
        warehouses.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            warehouses.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        warehouses.select2(options).change();
    });
}

select_country.change(function(){
    if (this.value)
        loadCities(this.value);
    else
        clearSelect(select_cities);
});

select_cities.change(function(){
    if (this.value)
        loadWarehouses(this.value);
    else
        clearSelect(select_warehouse);
});

var cross=", url(data:image/gif;base64,R0lGODlhBwAHAIAAAP///5KSkiH5BAAAAAAALAAAAAAHAAcAAAIMTICmsGrIXnLxuDMLADs=) no-repeat right 18px center";
var personalImg="/web/pictures/Icons/Counterparty/leg_pers.png";

$(function(){
    checkPerson();
    $('#person_type').change(checkPerson);
    $('#person_type').click(checkPerson);
    $('#person_type').change(function(){
        grid_counterparty_subsidiary.refresh();
    });
 });



function checkPerson(){
    checkPersonType();
    var temp=  ($('#operation_selector').val()!=100&&$('#operation_selector').val()!=2);

    if(temp||$('#person_type').val()=="")
    {  cross="";
    }
    else
        cross=", url(data:image/gif;base64,R0lGODlhBwAHAIAAAP///5KSkiH5BAAAAAAALAAAAAAHAAcAAAIMTICmsGrIXnLxuDMLADs=) no-repeat right 18px center";
    $('#person_type').css("background"," url('"+personalImg+"') no-repeat"+cross);

   if($('.btn.btn-xs.btn-default.save-btn').css('display')!=undefined||$('#operation_selector').val()==2)
        $('#person_type').css("background-color","#fff");
    else
       $('#person_type').css("background-color","#eee");
}

function checkPersonType(){
    if($('#person_type').val()==1)
        personalImg=getLocation("pictures/Icons/Counterparty/Contact_persons.png");
    else  if($('#person_type').val()==2)
        personalImg=getLocation("pictures/Icons/Counterparty/leg_pers.png");
    else
        personalImg="";
}

$('#warning_yes_button').click(function() {
    $('#show_cp_warnings').modal('hide');
    $('#forceSave').val(1);
    if ($('#update_entity_btn').length > 0)
        $('#update_entity_btn').click();
    else
        $('#create_entity_btn').click();
});

$('#warning_no_button').click(function() {
    $('#show_cp_warnings').modal('hide');
});




$(document).ready(function(){

    //смена типа документа физ лица
    var pers_doc_type_first_load = true;

    pers_doc_type.change(function() {


        if(this.value==1){
            pers_doc_serial_num.inputmask({
                mask: "ff",
                definitions: {
                    'f': {
                        validator: '[АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя]',
                        cardinality: '1',
                        casing: 'upper'
                    }
                }
            });
            pers_doc_num.inputmask("999999");
        }
        else
        {
            pers_doc_serial_num.off();
            pers_doc_num.off();
        }

        if (!pers_doc_type_first_load){
            pers_doc_serial_num.val('').trigger('input');
            pers_doc_num.val('').trigger('input');
        }
        else
            pers_doc_type_first_load = false;
        
    }).change();

});
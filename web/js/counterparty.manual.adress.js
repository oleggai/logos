/**
 * Created by Hopr on 31.07.2015.
 */

//транслитерация
function Transliterate(element, txt, src, tar){
    jQuery.getJSON(url_get_transliterate, { text:txt, source:src, target:tar},
        (function(this_item) {
            return function(data) {
                this_item.val(data);
                //this_item.change();
            };
        }(element)
        )
    );
}

var adressTransliterateButton = $('#adress_transliterate_button');
var adressENTab = $('#adress_form_en_tab');
var adressUKTab = $('#adress_form_uk_tab');
var adressRUTab = $('#adress_form_ru_tab');

var adressCountryEn = $('#ma_country_en');
var adressRegionEn = $('#ma_region_en');
var adressCityEn = $('#ma_city_en');
var adressAddressEn = $('#ma_address_en');

var adressCountryUk = $('#ma_country_uk');
var adressRegionUk = $('#ma_region_uk');
var adressCityUk = $('#ma_city_uk');
var adressAddressUk = $('#ma_address_uk');

var adressCountryRu = $('#ma_country_ru');
var adressRegionRu = $('#ma_region_ru');
var adressCityRu = $('#ma_city_ru');
var adressAddressRu = $('#ma_address_ru');

var adressIndex = $('#ma_index');
var adressAddInfo = $('#ma_add_info');

var adressFullEn = $('#adress_full_en');
var adressFullUk = $('#adress_full_uk');
var adressFullRu = $('#adress_full_ru');

//var use_manual_address_level_0_en = $('#use_manual_address_level_0_en');
var use_manual_address_level_10_en = $('#use_manual_address_level_10_en');
var use_manual_address_level_20_en = $('#use_manual_address_level_20_en');
var use_manual_address_level_30_en = $('#use_manual_address_level_30_en');
//var use_manual_address_level_0_uk = $('#use_manual_address_level_0_uk');
var use_manual_address_level_10_uk = $('#use_manual_address_level_10_uk');
var use_manual_address_level_20_uk = $('#use_manual_address_level_20_uk');
var use_manual_address_level_30_uk = $('#use_manual_address_level_30_uk');
//var use_manual_address_level_0_ru = $('#use_manual_address_level_0_ru');
var use_manual_address_level_10_ru = $('#use_manual_address_level_10_ru');
var use_manual_address_level_20_ru = $('#use_manual_address_level_20_ru');
var use_manual_address_level_30_ru = $('#use_manual_address_level_30_ru');

var div_manual_address_level_0 = $('.manual_address_level_0');
var div_manual_address_level_10 = $('.manual_address_level_10');
var div_manual_address_level_20 = $('.manual_address_level_20');
var div_manual_address_level_30 = $('.manual_address_level_30');

var div_address_level_0 = $('.address_level_0');
var div_address_level_10 = $('.address_level_10');
var div_address_level_20 = $('.address_level_20');
var div_address_level_30 = $('.address_level_30');

var select_country_en = $('#counterpartymanualadress-country_id_en');
var select_country_ru = $('#counterpartymanualadress-country_id_ru');
var select_country_uk = $('#counterpartymanualadress-country_id_uk');
var select_region1_en = $('#counterpartymanualadress-region_lvl1_id_en');
var select_region1_ru = $('#counterpartymanualadress-region_lvl1_id_ru');
var select_region1_uk = $('#counterpartymanualadress-region_lvl1_id_uk');
var select_region2_en = $('#counterpartymanualadress-region_lvl2_id_en');
var select_region2_ru = $('#counterpartymanualadress-region_lvl2_id_ru');
var select_region2_uk = $('#counterpartymanualadress-region_lvl2_id_uk');
var select_city_en = $('#counterpartymanualadress-city_id_en');
var select_city_ru = $('#counterpartymanualadress-city_id_ru');
var select_city_uk = $('#counterpartymanualadress-city_id_uk');
var select_street_en = $('#counterpartymanualadress-street_id_en');
var select_street_ru = $('#counterpartymanualadress-street_id_ru');
var select_street_uk = $('#counterpartymanualadress-street_id_uk');
var select_building1_en = $('#counterpartymanualadress-buildingtype_level1_en');
var select_building1_ru = $('#counterpartymanualadress-buildingtype_level1_ru');
var select_building1_uk = $('#counterpartymanualadress-buildingtype_level1_uk');
var select_building2_en = $('#counterpartymanualadress-buildingtype_level2_en');
var select_building2_ru = $('#counterpartymanualadress-buildingtype_level2_ru');
var select_building2_uk = $('#counterpartymanualadress-buildingtype_level2_uk');
var select_building3_en = $('#counterpartymanualadress-buildingtype_level3_en');
var select_building3_ru = $('#counterpartymanualadress-buildingtype_level3_ru');
var select_building3_uk = $('#counterpartymanualadress-buildingtype_level3_uk');
var text_bulding1_en = $('#counterpartymanualadress-number_level1_en');
var text_bulding1_ru = $('#counterpartymanualadress-number_level1_ru');
var text_bulding1_uk = $('#counterpartymanualadress-number_level1_uk');
var text_bulding2_en = $('#counterpartymanualadress-number_level2_en');
var text_bulding2_ru = $('#counterpartymanualadress-number_level2_ru');
var text_bulding2_uk = $('#counterpartymanualadress-number_level2_uk');
var text_bulding3_en = $('#counterpartymanualadress-number_level3_en');
var text_bulding3_ru = $('#counterpartymanualadress-number_level3_ru');
var text_bulding3_uk = $('#counterpartymanualadress-number_level3_uk');

var text_region1_type_en = $('[name="region1_type_en"]');
var text_region2_type_en = $('[name="region2_type_en"]');
var text_city_type_en = $('[name="city_type_en"]');
var text_street_type_en = $('[name="street_type_en"]');
var text_region1_type_ru = $('[name="region1_type_ru"]');
var text_region2_type_ru = $('[name="region2_type_ru"]');
var text_city_type_ru = $('[name="city_type_ru"]');
var text_street_type_ru = $('[name="street_type_ru"]');
var text_region1_type_uk = $('[name="region1_type_uk"]');
var text_region2_type_uk = $('[name="region2_type_uk"]');
var text_city_type_uk = $('[name="city_type_uk"]');
var text_street_type_uk = $('[name="street_type_uk"]');


var fixed_region2 = null;

if (read_only)
    adressTransliterateButton.hide();

adressTransliterateButton.click(function() {
    if (adressENTab.hasClass('active')) {
        if (adressCountryUk.val() == '')
            Transliterate(adressCountryUk, adressCountryEn.val(), 'en', 'uk');
        if (adressCountryRu.val() == '')
            Transliterate(adressCountryRu, adressCountryEn.val(), 'en', 'ru');

        if (adressRegionUk.val() == '')
            Transliterate(adressRegionUk, adressRegionEn.val(), 'en', 'uk');
        if (adressRegionRu.val() == '')
            Transliterate(adressRegionRu, adressRegionEn.val(), 'en', 'ru');

        if (adressCityUk.val() == '')
            Transliterate(adressCityUk, adressCityEn.val(), 'en', 'uk');
        if (adressCityRu.val() == '')
            Transliterate(adressCityRu, adressCityEn.val(), 'en', 'ru');

        if (adressAddressUk.val() == '')
            Transliterate(adressAddressUk, adressAddressEn.val(), 'en', 'uk');
        if (adressAddressRu.val() == '')
            Transliterate(adressAddressRu, adressAddressEn.val(), 'en', 'ru');
    }
    if (adressUKTab.hasClass('active')) {
        if (adressCountryEn.val() == '')
            Transliterate(adressCountryEn, adressCountryUk.val(), 'uk', 'en');
        if (adressCountryRu.val() == '')
            Transliterate(adressCountryRu, adressCountryUk.val(), 'uk', 'ru');

        if (adressRegionEn.val() == '')
            Transliterate(adressRegionEn, adressRegionUk.val(), 'uk', 'en');
        if (adressRegionRu.val() == '')
            Transliterate(adressRegionRu, adressRegionUk.val(), 'uk', 'ru');

        if (adressCityEn.val() == '')
            Transliterate(adressCityEn, adressCityUk.val(), 'uk', 'en');
        if (adressCityRu.val() == '')
            Transliterate(adressCityRu, adressCityUk.val(), 'uk', 'ru');

        if (adressAddressEn.val() == '')
            Transliterate(adressAddressEn, adressAddressUk.val(), 'uk', 'en');
        if (adressAddressRu.val() == '')
            Transliterate(adressAddressRu, adressAddressUk.val(), 'uk', 'ru');
    }
    if (adressRUTab.hasClass('active')) {
        if (adressCountryEn.val() == '')
            Transliterate(adressCountryEn, adressCountryRu.val(), 'ru', 'en');
        if (adressCountryUk.val() == '')
            Transliterate(adressCountryUk, adressCountryRu.val(), 'ru', 'uk');

        if (adressRegionEn.val() == '')
            Transliterate(adressRegionEn, adressRegionRu.val(), 'ru', 'en');
        if (adressRegionUk.val() == '')
            Transliterate(adressRegionUk, adressRegionRu.val(), 'ru', 'uk');

        if (adressCityEn.val() == '')
            Transliterate(adressCityEn, adressCityRu.val(), 'ru', 'en');
        if (adressCityUk.val() == '')
            Transliterate(adressCityUk, adressCityRu.val(), 'ru', 'uk');

        if (adressAddressEn.val() == '')
            Transliterate(adressAddressEn, adressAddressRu.val(), 'ru', 'en');
        if (adressAddressUk.val() == '')
            Transliterate(adressAddressUk, adressAddressRu.val(), 'ru', 'uk');
    }
});

//механизм адресов
var cityFromWarehouse = null;

function showHideDivLevel30() {
    if (use_manual_address_level_30_en.prop( "checked" )) {
        div_address_level_30.hide();
        div_manual_address_level_30.show();
    }
    else {
        div_address_level_30.show();
        div_manual_address_level_30.hide();
    }
}

function showHideDivLevel20() {
    if (use_manual_address_level_20_en.prop( "checked" )) {
        div_address_level_20.hide();
        div_manual_address_level_20.show();
    }
    else {
        div_address_level_20.show();
        div_manual_address_level_20.hide();
    }
}

function showHideDivLevel10() {
    if (use_manual_address_level_10_en.prop( "checked" )) {
        div_address_level_10.hide();
        div_manual_address_level_10.show();
    }
    else {
        div_address_level_10.show();
        div_manual_address_level_10.hide();
    }
}

function showHideDivLevel0() {
    //if (use_manual_address_level_0_en.prop( "checked" )) {
    if (false){
        div_address_level_0.hide();
        div_manual_address_level_0.show();
    }
    else {
        div_address_level_0.show();
        div_manual_address_level_0.hide();
    }
}

showHideDivLevel0();showHideDivLevel10();showHideDivLevel20();showHideDivLevel30();

use_manual_address_level_30_en.change(function() {

    showHideDivLevel30();
    use_manual_address_level_30_ru.prop('checked', this.checked);
    use_manual_address_level_30_uk.prop('checked', this.checked);

    refreshAdressFullEn();
    refreshAdressFullUk();
    refreshAdressFullRu();
});

use_manual_address_level_30_ru.change(function (){use_manual_address_level_30_en.prop('checked', this.checked).change();});
use_manual_address_level_30_uk.change(function (){use_manual_address_level_30_en.prop('checked', this.checked).change();});


use_manual_address_level_20_en.change(function() {

    showHideDivLevel20();

    use_manual_address_level_30_en.prop('checked', this.checked).change();

    use_manual_address_level_20_ru.prop('checked', this.checked);
    use_manual_address_level_20_uk.prop('checked', this.checked);

    refreshAdressFullEn();
    refreshAdressFullUk();
    refreshAdressFullRu();
});

use_manual_address_level_20_ru.change(function (){use_manual_address_level_20_en.prop('checked', this.checked).change();});
use_manual_address_level_20_uk.change(function (){use_manual_address_level_20_en.prop('checked', this.checked).change();});


use_manual_address_level_10_en.change(function() {

    showHideDivLevel10();

    use_manual_address_level_20_en.prop('checked', this.checked).change();

    use_manual_address_level_10_ru.prop('checked', this.checked);
    use_manual_address_level_10_uk.prop('checked', this.checked);

    refreshAdressFullEn();
    refreshAdressFullUk();
    refreshAdressFullRu();
});

use_manual_address_level_10_ru.change(function (){use_manual_address_level_10_en.prop('checked', this.checked).change();});
use_manual_address_level_10_uk.change(function (){use_manual_address_level_10_en.prop('checked', this.checked).change();});

/*
use_manual_address_level_0_en.change(function() {

    showHideDivLevel0();

    use_manual_address_level_10_en.prop('checked', this.checked).change();

    use_manual_address_level_0_ru.prop('checked', this.checked);
    use_manual_address_level_0_uk.prop('checked', this.checked);
});

use_manual_address_level_0_ru.change(function (){use_manual_address_level_0_en.prop('checked', this.checked).change();});
use_manual_address_level_0_uk.change(function (){use_manual_address_level_0_en.prop('checked', this.checked).change();});
*/

function syncCountry(value,clear){
    var v = clear ? null : value.val();

    if (!value.is(select_country_en)) select_country_en.val(v).change();
    if (!value.is(select_country_uk)) select_country_uk.val(v).change();
    if (!value.is(select_country_ru)) select_country_ru.val(v).change();
}

function syncRegion1(value,clear){
    var v = clear ? null : value.val();

    if (!value.is(select_region1_en)) select_region1_en.val(v).change();
    if (!value.is(select_region1_uk)) select_region1_uk.val(v).change();
    if (!value.is(select_region1_ru)) select_region1_ru.val(v).change();
}

function syncRegion2(value,clear){
    var v = clear ? null : value.val();

    if (!value.is(select_region2_en)) select_region2_en.val(v).change();
    if (!value.is(select_region2_uk)) select_region2_uk.val(v).change();
    if (!value.is(select_region2_ru)) select_region2_ru.val(v).change();

    loadCitiesFromRegion(select_city_en,v,'en');
    loadCitiesFromRegion(select_city_ru,v,'ru');
    loadCitiesFromRegion(select_city_uk,v,'uk');
}

function syncCity(value,clear){
    var v = clear ? null : value.val();

    if (!value.is(select_city_en)) select_city_en.val(v).change();
    if (!value.is(select_city_uk)) select_city_uk.val(v).change();
    if (!value.is(select_city_ru)) select_city_ru.val(v).change();

    loadCityRegions(v);
}

function syncStreet(value, clear){
    var v = clear ? null : value.val();

    if (!value.is(select_street_en)) select_street_en.val(v).change();
    if (!value.is(select_street_uk)) select_street_uk.val(v).change();
    if (!value.is(select_street_ru)) select_street_ru.val(v).change();
}

function syncBuilding1(value){
    select_building1_en.val(value);
    select_building1_uk.val(value);
    select_building1_ru.val(value);
}

function syncBuilding2(value){
    select_building2_en.val(value);
    select_building2_uk.val(value);
    select_building2_ru.val(value);
}

function syncBuilding3(value){
    select_building3_en.val(value);
    select_building3_uk.val(value);
    select_building3_ru.val(value);
}

function syncBuildingNum1(value){
    text_bulding1_en.val(value);
    text_bulding1_uk.val(value);
    text_bulding1_ru.val(value);
}

function syncBuildingNum2(value){
    text_bulding2_en.val(value);
    text_bulding2_uk.val(value);
    text_bulding2_ru.val(value);
}

function syncBuildingNum3(value){
    text_bulding3_en.val(value);
    text_bulding3_uk.val(value);
    text_bulding3_ru.val(value);
}

select_country_en.on("select2:select",function(){syncCountry(select_country_en);});
select_country_ru.on("select2:select",function(){syncCountry(select_country_ru);});
select_country_uk.on("select2:select",function(){syncCountry(select_country_uk);});
select_country_en.on("select2:unselect",function(){syncCountry(select_country_en,true)});
select_country_ru.on("select2:unselect",function(){syncCountry(select_country_ru,true);});
select_country_uk.on("select2:unselect",function(){syncCountry(select_country_uk,true);});
select_country_en.change(function(){

    loadRegions1(select_region1_en,this.value,'en');
    loadRegions1(select_region1_ru,this.value,'ru');
    loadRegions1(select_region1_uk,this.value,'uk');

    loadCities(select_city_en,this.value,'en');
    loadCities(select_city_ru,this.value,'ru');
    loadCities(select_city_uk,this.value,'uk');

    refreshAdressFullEn();
    refreshAdressFullUk();
    refreshAdressFullRu();
});

select_region1_en.on("select2:select",function(){syncRegion1(select_region1_en);});
select_region1_ru.on("select2:select",function(){syncRegion1(select_region1_ru);});
select_region1_uk.on("select2:select",function(){syncRegion1(select_region1_uk);});
select_region1_en.on("select2:unselect",function(){syncRegion1(select_region1_en,true);});
select_region1_ru.on("select2:unselect",function(){syncRegion1(select_region1_ru,true);});
select_region1_uk.on("select2:unselect",function(){syncRegion1(select_region1_uk,true);});
select_region1_en.change( function(){
    loadRegions2(select_region2_en,this.value,'en');
    loadRegions2(select_region2_ru,this.value,'ru');
    loadRegions2(select_region2_uk,this.value,'uk');

    if (!this.value){
        loadCities(select_city_en,select_country_en.val(),'en');
        loadCities(select_city_ru,select_country_en.val(),'ru');
        loadCities(select_city_uk,select_country_en.val(),'uk');
    }

    loadRegionType(this.value, text_region1_type_en, 'en');
    loadRegionType(this.value, text_region1_type_ru, 'ru');
    loadRegionType(this.value, text_region1_type_uk, 'uk');

    refreshAdressFullEn();
    refreshAdressFullUk();
    refreshAdressFullRu();
});


select_region2_en.on("select2:select",function(){syncRegion2(select_region2_en);});
select_region2_ru.on("select2:select",function(){syncRegion2(select_region2_ru);});
select_region2_uk.on("select2:select",function(){syncRegion2(select_region2_uk);});
select_region2_en.on("select2:unselect",function(){syncRegion2(select_region2_en,true);});
select_region2_ru.on("select2:unselect",function(){syncRegion2(select_region2_ru,true);});
select_region2_uk.on("select2:unselect",function(){syncRegion2(select_region2_uk,true);});
select_region2_en.change( function(){

    /*
    loadCitiesFromRegion(select_city_en,this.value,'en');
    loadCitiesFromRegion(select_city_ru,this.value,'ru');
    loadCitiesFromRegion(select_city_uk,this.value,'uk');
    */

    loadRegionType(this.value, text_region2_type_en, 'en');
    loadRegionType(this.value, text_region2_type_ru, 'ru');
    loadRegionType(this.value, text_region2_type_uk, 'uk');

    refreshAdressFullEn();
    refreshAdressFullUk();
    refreshAdressFullRu();
});

select_city_en.on("select2:select",function(){syncCity(select_city_en);});
select_city_ru.on("select2:select",function(){syncCity(select_city_ru);});
select_city_uk.on("select2:select",function(){syncCity(select_city_uk);});
select_city_en.on("select2:unselect",function(){syncCity(select_city_en,true);});
select_city_ru.on("select2:unselect",function(){syncCity(select_city_ru,true);});
select_city_uk.on("select2:unselect",function(){syncCity(select_city_uk,true);});
select_city_en.change(function(){
    loadStreets(select_street_en,this.value,'en');
    loadStreets(select_street_ru,this.value,'ru');
    loadStreets(select_street_uk,this.value,'uk');

    loadCityType(this.value, text_city_type_en, 'en');
    loadCityType(this.value, text_city_type_ru, 'ru');
    loadCityType(this.value, text_city_type_uk, 'uk');

    refreshAdressFullEn();
    refreshAdressFullUk();
    refreshAdressFullRu();
});

select_street_en.on("select2:select",function(){syncStreet(select_street_en);});
select_street_ru.on("select2:select",function(){syncStreet(select_street_ru);});
select_street_uk.on("select2:select",function(){syncStreet(select_street_uk);});
select_street_en.on("select2:unselect",function(){syncStreet(select_street_en,true);});
select_street_ru.on("select2:unselect",function(){syncStreet(select_street_ru,true);});
select_street_uk.on("select2:unselect",function(){syncStreet(select_street_uk,true);});
select_street_en.change(function(){
    loadStreetType(this.value, text_street_type_en, 'en');
    loadStreetType(this.value, text_street_type_ru, 'ru');
    loadStreetType(this.value, text_street_type_uk, 'uk');

    refreshAdressFullEn();
    refreshAdressFullUk();
    refreshAdressFullRu();
});

select_building1_en.change(function(){syncBuilding1(this.value); refreshAdressFullEn();});
select_building2_en.change(function(){syncBuilding2(this.value); refreshAdressFullEn();});
select_building3_en.change(function(){syncBuilding3(this.value); refreshAdressFullEn();});
select_building1_ru.change(function(){syncBuilding1(this.value); refreshAdressFullRu();});
select_building2_ru.change(function(){syncBuilding2(this.value); refreshAdressFullRu();});
select_building3_ru.change(function(){syncBuilding3(this.value); refreshAdressFullRu();});
select_building1_uk.change(function(){syncBuilding1(this.value); refreshAdressFullUk();});
select_building2_uk.change(function(){syncBuilding2(this.value); refreshAdressFullUk();});
select_building3_uk.change(function(){syncBuilding3(this.value); refreshAdressFullUk();});


text_bulding1_en.change(function(){syncBuildingNum1(this.value); refreshAdressFullEn();});
text_bulding2_en.change(function(){syncBuildingNum2(this.value); refreshAdressFullEn();});
text_bulding3_en.change(function(){syncBuildingNum3(this.value); refreshAdressFullEn();});
text_bulding1_ru.change(function(){syncBuildingNum1(this.value); refreshAdressFullRu();});
text_bulding2_ru.change(function(){syncBuildingNum2(this.value); refreshAdressFullRu();});
text_bulding3_ru.change(function(){syncBuildingNum3(this.value); refreshAdressFullRu();});
text_bulding1_uk.change(function(){syncBuildingNum1(this.value); refreshAdressFullUk();});
text_bulding2_uk.change(function(){syncBuildingNum2(this.value); refreshAdressFullUk();});
text_bulding3_uk.change(function(){syncBuildingNum3(this.value); refreshAdressFullUk();});

function clearSelect(select){

    var options = select.data('select2').options.options;
    select.html('');
    options.data = [];
    select.select2(options).change();
}

function loadCityRegions(country_ref) {

    if (!country_ref) {
        fixed_region2 = null;
        select_region1_en.val(null).change();
        syncRegion1(select_region1_en, true);
        return;
    }

    jQuery.getJSON(url_get_city_address, { city_id: country_ref }, function(data) {

        select_region1_en.val(data['region1']).change();
        syncRegion1(select_region1_en);
        fixed_region2 = data['region2'];
    });
}

function loadRegions1(elem, country_ref, lang_ref) {

    if (!country_ref) {
        clearSelect(elem);
        return;
    }

    jQuery.getJSON(url_get_regions, { country: country_ref,lang: lang_ref }, (function(elem) { return function(data) {

        var regions2 = elem;
        var options = regions2.data('select2').options.options;
        regions2.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            regions2.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        regions2.select2(options).change();

    }}(elem)));
}


function loadRegions2(elem,region_ref,lang_ref){

    if (!region_ref) {
        clearSelect(elem);
        return;
    }

    jQuery.getJSON(url_get_regions, { region :region_ref, lang: lang_ref}, (function(elem) { return function(data) {

        var regions2 = elem;
        var options = regions2.data('select2').options.options;
        regions2.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            regions2.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        if  (fixed_region2){
            regions2.select2(options).val(fixed_region2).change();
        }
        else
            regions2.select2(options).change();

    }}(elem)));
}

function loadCities(elem, country_ref, lang_ref){

    if (!country_ref) {
        clearSelect(elem);
        return;
    }

    jQuery.getJSON(url_get_cities, { country:country_ref, lang: lang_ref}, (function(elem) { return function(data) {

        var cities = elem;
        var options = cities.data('select2').options.options;
        cities.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            cities.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;

        if (cityFromWarehouse) {
            cities.val(cityFromWarehouse).change();
            syncCity(cities);
        }
        else
            cities.select2(options).change();

    }}(elem)));
}

function loadCitiesFromRegion(elem, region_ref, lang_ref){

    if (!region_ref) {
        clearSelect(elem);
        return;
    }

    jQuery.getJSON(url_get_cities, { region:region_ref, lang: lang_ref}, (function(elem) { return function(data) {

        var cities = elem;
        var options = cities.data('select2').options.options;
        cities.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            cities.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        cities.select2(options).change();

    }}(elem)));
}

function loadStreets(elem, city_ref, lang_ref){

    if (!city_ref) {
        clearSelect(elem);
        return;
    }

    jQuery.getJSON(url_get_streets, { city:city_ref, lang: lang_ref}, (function(elem) { return function(data) {

        var cities = elem;
        var options = cities.data('select2').options.options;
        cities.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            cities.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        cities.select2(options).change();

    }}(elem)));
}

function loadRegionType(region_ref, element,lang_ref){

    if (!region_ref)
        element.val('');
    else
        jQuery.getJSON(url_get_region_type, { region:region_ref,lang: lang_ref},
            (function(this_item) {return function(data) {
                    this_item.val(data);
                };}(element))
        );
}

function loadCityType(cityId, element,lang_ref) {
    if (!cityId)
        element.val('');
    else
        jQuery.getJSON(url_get_city_type, { city_id: cityId ,lang: lang_ref},
            (function(this_item) {return function(data) {
                    this_item.val(data['city_type_name']);
                };}(element))
        );
}

function loadStreetType(streetId,element,lang_ref) {
    if (!streetId)
        element.val('');
    else
        jQuery.getJSON(url_get_street_type, { street: streetId, lang: lang_ref },
            (function(this_item) {return function(data) {
                    this_item.val(data);
                    refreshAdressFullEn();
                    refreshAdressFullUk();
                    refreshAdressFullRu();
                };}(element))
        );
}

adressRegionEn.change(function(){refreshAdressFullEn();});
adressCityEn.change(function(){refreshAdressFullEn();});
adressAddressEn.change(function(){refreshAdressFullEn();});
adressRegionUk.change(function(){refreshAdressFullUk();});
adressCityUk.change(function(){refreshAdressFullUk();});
adressAddressUk.change(function(){refreshAdressFullUk();});
adressRegionRu.change(function(){refreshAdressFullRu();});
adressCityRu.change(function(){refreshAdressFullRu();});
adressAddressRu.change(function(){refreshAdressFullRu();});

adressIndex.change(function(){refreshAdressFullEn(); refreshAdressFullUk(); refreshAdressFullRu();$('.input_index_clone').val($(this).val());}).change();
adressAddInfo.change(function(){refreshAdressFullEn(); refreshAdressFullUk(); refreshAdressFullRu();});

//автозаполнение полного адреса
function getAdressFull(country, index,
                       use_manual_address_level_10, region1, region2, ma_region,
                       use_manual_address_level_20, city, ma_city,
                       use_manual_address_level_30, streetType, street,
                       buildingtypeLevel1, buildingtypeLevel1_name,
                       buildingtypeLevel2, buildingtypeLevel2_name,
                       buildingtypeLevel3, buildingtypeLevel3_name,
                       ma_adress, addition_info){

    var result = '';

    // страна
    result = country;

    //индекс
    result += index == '' ? '' : ', ' + index;

    // регионы
    if (!use_manual_address_level_10) {
        result += region1 == '' ? '' : ', ' + region1;
        result += region2 == '' ? '' : ', ' + region2;
    }
    else
        result += ma_region == '' ? '' : ', ' + ma_region;


    // город
    if (!use_manual_address_level_20)
        result += city == '' ? '' : ', ' + city;
    else
        result += ma_city == '' ? '' : ', ' + ma_city;


    // улица дом квартира и тд
    if (!use_manual_address_level_30) {
        result += street == '' ? '' : ', ' + streetType + ' ' + street;
        result += buildingtypeLevel1_name == '' ? '' : ', ' + buildingtypeLevel1 + ' ' + buildingtypeLevel1_name;
        result += buildingtypeLevel2_name == '' ? '' : ', ' + buildingtypeLevel2 + ' ' + buildingtypeLevel2_name;
        result += buildingtypeLevel3_name == '' ? '' : ', ' + buildingtypeLevel3 + ' ' + buildingtypeLevel3_name;

    }
    else
        result += ma_adress == '' ? '' : ', ' + ma_adress;


    result += addition_info == '' ? '' : ', ' + addition_info;

    return result;
}

function refreshAdressFullEn(){

    adressFullEn.val(getAdressFull(select_country_en.find('option:selected').text(), adressIndex.val(),
        use_manual_address_level_10_en.prop( "checked" ), select_region1_en.find('option:selected').text(),
        select_region2_en.find('option:selected').text(), adressRegionEn.val(),
        use_manual_address_level_20_en.prop( "checked" ), select_city_en.find('option:selected').text(), adressCityEn.val(),
        use_manual_address_level_30_en.prop( "checked" ), text_street_type_en.val(), select_street_en.find('option:selected').text(),
        select_building1_en.find('option:selected').text(), text_bulding1_en.val(),
        select_building2_en.find('option:selected').text(), text_bulding2_en.val(),
        select_building3_en.find('option:selected').text(), text_bulding3_en.val(),
        adressAddressEn.val(), adressAddInfo.val()
    ));

}

function refreshAdressFullUk(){

    adressFullUk.val(getAdressFull(select_country_uk.find('option:selected').text(), adressIndex.val(),
        use_manual_address_level_10_uk.prop( "checked" ), select_region1_uk.find('option:selected').text(),
        select_region2_uk.find('option:selected').text(), adressRegionUk.val(),
        use_manual_address_level_20_uk.prop( "checked" ), select_city_uk.find('option:selected').text(), adressCityUk.val(),
        use_manual_address_level_30_uk.prop( "checked" ), text_street_type_uk.val(), select_street_uk.find('option:selected').text(),
        select_building1_uk.find('option:selected').text(), text_bulding1_uk.val(),
        select_building2_uk.find('option:selected').text(), text_bulding2_uk.val(),
        select_building3_uk.find('option:selected').text(), text_bulding3_uk.val(),
        adressAddressUk.val(), adressAddInfo.val()
    ));

}

function refreshAdressFullRu(){

    adressFullRu.val(getAdressFull(select_country_ru.find('option:selected').text(), adressIndex.val(),
        use_manual_address_level_10_ru.prop( "checked" ), select_region1_ru.find('option:selected').text(),
        select_region2_ru.find('option:selected').text(), adressRegionRu.val(),
        use_manual_address_level_20_ru.prop( "checked" ), select_city_ru.find('option:selected').text(), adressCityRu.val(),
        use_manual_address_level_30_ru.prop( "checked" ), text_street_type_ru.val(), select_street_ru.find('option:selected').text(),
        select_building1_ru.find('option:selected').text(), text_bulding1_ru.val(),
        select_building2_ru.find('option:selected').text(), text_bulding2_ru.val(),
        select_building3_ru.find('option:selected').text(), text_bulding3_ru.val(),
        adressAddressRu.val(), adressAddInfo.val()
    ));

}


//заполнение адреса с выбранного склада
var ma_address_kind = $('#ma_address_kind');
var ma_address_type = $('#ma_address_type');
var warehouse_id = $('#warehouse_id');

function FillFromWarehouse(data) {
    if (data == null)
        return;

    cityFromWarehouse = data['city'];
    select_country_en.val(data['country']).change();
    syncCountry(select_country_en);

    adressAddressEn.val(data['name_en']);
    adressAddressUk.val(data['name_uk']);
    adressAddressRu.val(data['name_ru']);

    adressIndex.val(data['index']).change();
}

ma_address_kind.change(function() {
    var warehouse_btn = $('#warehouse_btn');
    var warehouse_btn_sel = $('#select_warehouse_btn_select');
    if (this.value == 2) //склад
    {
        warehouse_btn.show();
        ma_address_type.val(1);
        ma_address_type.prop("disabled", true);

        use_manual_address_level_10_en.prop("checked", false).change();
        use_manual_address_level_10_en.prop("disabled", true);
        use_manual_address_level_10_uk.prop("disabled", true);
        use_manual_address_level_10_ru.prop("disabled", true);
        use_manual_address_level_20_en.prop("checked", false).change();
        use_manual_address_level_20_en.prop("disabled", true);
        use_manual_address_level_20_uk.prop("disabled", true);
        use_manual_address_level_20_ru.prop("disabled", true);
        use_manual_address_level_30_en.prop("checked", true).change();
        use_manual_address_level_30_en.prop("disabled", true);
        use_manual_address_level_30_uk.prop("disabled", true);
        use_manual_address_level_30_ru.prop("disabled", true);

        select_country_en.prop("disabled", true);
        select_region1_en.prop("disabled", true);
        select_region2_en.prop("disabled", true);
        select_city_en.prop("disabled", true);
        adressAddressEn.prop("disabled", true);

        select_country_uk.prop("disabled", true);
        select_region1_uk.prop("disabled", true);
        select_region2_uk.prop("disabled", true);
        select_city_uk.prop("disabled", true);
        adressAddressUk.prop("disabled", true);

        select_country_ru.prop("disabled", true);
        select_region1_ru.prop("disabled", true);
        select_region2_ru.prop("disabled", true);
        select_city_ru.prop("disabled", true);
        adressAddressRu.prop("disabled", true);

        adressIndex.prop("disabled", read_only);

        if (!warehouse_id.val())
            warehouse_btn_sel.click();
    }
    else {
        warehouse_id.val("").change();
        warehouse_btn.hide();
        cityFromWarehouse = null;
        ma_address_type.prop("disabled", read_only);

        use_manual_address_level_10_en.prop("disabled", read_only);
        use_manual_address_level_10_uk.prop("disabled", read_only);
        use_manual_address_level_10_ru.prop("disabled", read_only);
        use_manual_address_level_20_en.prop("disabled", read_only);
        use_manual_address_level_20_uk.prop("disabled", read_only);
        use_manual_address_level_20_ru.prop("disabled", read_only);
        use_manual_address_level_30_en.prop("disabled", read_only);
        use_manual_address_level_30_uk.prop("disabled", read_only);
        use_manual_address_level_30_ru.prop("disabled", read_only);

        select_country_en.prop("disabled", read_only);
        select_region1_en.prop("disabled", read_only);
        select_region2_en.prop("disabled", read_only);
        select_city_en.prop("disabled", read_only);
        adressAddressEn.prop("disabled", read_only);

        select_country_uk.prop("disabled", read_only);
        select_region1_uk.prop("disabled", read_only);
        select_region2_uk.prop("disabled", read_only);
        select_city_uk.prop("disabled", read_only);
        adressAddressUk.prop("disabled", read_only);

        select_country_ru.prop("disabled", read_only);
        select_region1_ru.prop("disabled", read_only);
        select_region2_ru.prop("disabled", read_only);
        select_city_ru.prop("disabled", read_only);
        adressAddressRu.prop("disabled", read_only);

        adressIndex.prop("disabled", read_only);
    }
}).change();

warehouse_id.change(function(){
    if (this.value && this.value != "") {
        jQuery.getJSON(url_get_warehouse, { id:this.value},
            function(data) {
                FillFromWarehouse(data);
            }
        );
    }
});

$('Form').submit(function() {

    ma_address_type.prop("disabled", false);

    use_manual_address_level_10_en.prop("disabled", false);
    use_manual_address_level_10_uk.prop("disabled", false);
    use_manual_address_level_10_ru.prop("disabled", false);
    use_manual_address_level_20_en.prop("disabled", false);
    use_manual_address_level_20_uk.prop("disabled", false);
    use_manual_address_level_20_ru.prop("disabled", false);
    use_manual_address_level_30_en.prop("disabled", false);
    use_manual_address_level_30_uk.prop("disabled", false);
    use_manual_address_level_30_ru.prop("disabled", false);

    select_country_en.prop("disabled", false);
    select_region1_en.prop("disabled", false);
    select_region2_en.prop("disabled", false);
    select_city_en.prop("disabled", false);
    adressAddressEn.prop("disabled", false);

    select_country_uk.prop("disabled", false);
    select_region1_uk.prop("disabled", false);
    select_region2_uk.prop("disabled", false);
    select_city_uk.prop("disabled", false);
    adressAddressUk.prop("disabled", false);

    select_country_ru.prop("disabled", false);
    select_region1_ru.prop("disabled", false);
    select_region2_ru.prop("disabled", false);
    select_city_ru.prop("disabled", false);
    adressAddressRu.prop("disabled", false);

    adressIndex.prop("disabled", false);
});

/**
 * JS функции для работы с формой строений
 * @author Richok FG
 */

var ddlCountry = $('[name="select_country"]');
var ddlRegion1 = $('[name="select_region1"]');
var ddlRegion2 = $('[name="select_region2"]');
var txtRegionType1 = $('#region1_type');
var txtRegionType2 = $('#region2_type');

var ddlCity = $('[name="select_city"]');
var txtCityType = $('#city_type_name');

// в зависимости от справочника (строения и подразделения)
var ddlStreet = $('[name="' + entityName + '[street]"]');
var txtStreetType = $('#street_type_name');

var ddlBuildingType1 = $('#building_type1');
var txtNumber1 = $('#number1');
var ddlBuildingType2 = $('#building_type2');
var txtNumber2 = $('#number2');
var ddlBuildingType3 = $('#building_type3');
var txtNumber3 = $('#number3');

var hidBuildingType1 = $('#hid_buildingtype1');
var hidBuildingType2 = $('#hid_buildingtype2');
var hidBuildingType3 = $('#hid_buildingtype3');

function setLvl2(block) {
    if (block) {
        ddlBuildingType2[0].selectedIndex = 0;
        hidBuildingType2.val(null);
        txtNumber2.val('');
    }
    ddlBuildingType2.attr('disabled', block);
    txtNumber2.attr('readonly', !ddlBuildingType2.val());
}

function setLvl3(block) {
    if (block) {
        ddlBuildingType3[0].selectedIndex = 0;
        hidBuildingType3.val(null);
        txtNumber3.val('');
    }
    ddlBuildingType3.attr('disabled', block);
    txtNumber3.attr('readonly', !ddlBuildingType3.val());
}

// если форма не заблокирована, назначить события для блокирования контролов типов номеров и номеров строений
if (!isDisableEdit) {
    ddlBuildingType1.change(function () {
        setLvl2(ddlBuildingType1.val() == false);
        hidBuildingType1.val(ddlBuildingType1.val());
        txtNumber1.attr('readonly', !ddlBuildingType1.val());
        ddlBuildingType2.change();
    }).change();

    ddlBuildingType2.change(function () {
        setLvl3(ddlBuildingType2.val() == false);
        hidBuildingType2.val(ddlBuildingType2.val());
        txtNumber2.attr('readonly', !ddlBuildingType2.val());
        if (!ddlBuildingType2.val()) {
            txtNumber2.val('');
        }
    }).change();

    ddlBuildingType3.change(function() {
        hidBuildingType3.val(ddlBuildingType3.val());
        txtNumber3.attr('readonly', !ddlBuildingType3.val());
        if (!ddlBuildingType3.val()) {
            txtNumber3.val('');
        }
    });
}

function clearSelect(select){

    var options = select.data('select2').options.options;
    select.html('');
    options.data = [];
    select.select2(options).change();
}

function loadRegions1(countryId) {

    jQuery.getJSON(url_get_regions, { country: countryId }, function(data) {

        var options = ddlRegion1.data('select2').options.options;
        ddlRegion1.html('');

        var items = [];
        for (var i = 0; i < data.length; i++) {
            items.push({ "id": data[i]['id'], "text": data[i]['txt']} );
            ddlRegion1.append($('<option>', { val: data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        ddlRegion1.select2(options).change();
    });
}

function loadRegions2(regionId) {

    jQuery.getJSON(url_get_regions, { region: regionId }, function(data) {

        var options = ddlRegion2.data('select2').options.options;
        ddlRegion2.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'], "text": data[i]['txt'] });
            ddlRegion2.append($('<option>', { val: data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        ddlRegion2.select2(options);
    });
}

function loadCities(regionId) {

    jQuery.getJSON(url_get_cities, { region: regionId }, function(data) {

        var options = ddlCity.data('select2').options.options;
        ddlCity.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'], "text": data[i]['txt'] });
            ddlCity.append($('<option>', { val: data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        ddlCity.select2(options);
    });
}

function loadStreets(cityId) {

    jQuery.getJSON(url_get_streets, { city: cityId }, function(data) {

        var options = ddlStreet.data('select2').options.options;
        ddlStreet.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'], "text": data[i]['txt'] });
            ddlStreet.append($('<option>', { val: data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        ddlStreet.select2(options);
    });
}

function loadRegionType(regionId, element) {

    if (!regionId)
        element.val('');
    else
        jQuery.getJSON(url_get_region_type, { region: regionId }, (function(this_item) {
                return function(data) {
                    this_item.val(data);
                };
            }(element))
        );
}

function loadCityType(cityId) {
    if (!cityId)
        txtCityType.val('');
    else
        jQuery.getJSON(url_get_city_type, { city_id: cityId }, function(data) {
            txtCityType.val(data['city_type_name']);
        });
}

function loadStreetType(streetId) {
    if (!streetId)
        txtStreetType.val('');
    else
        jQuery.getJSON(url_get_street_type, { street: streetId }, function(data) {
            txtStreetType.val(data);
        });
}

ddlCountry.change(function() {
    if (this.value)
        loadRegions1(this.value);
    else
        clearSelect(ddlRegion1);
});

ddlRegion1.change(function() {

    if (this.value)
        loadRegions2(this.value);
    else
        clearSelect(ddlRegion2);
    ddlRegion2.change();

    loadRegionType(this.value, txtRegionType1);
});

ddlRegion2.change(function() {
    if (this.value)
        loadCities(this.value);
    else
        clearSelect(ddlCity);

    loadRegionType(this.value, txtRegionType2);
});

ddlCity.change(function() {
    if (this.value)
        loadStreets(this.value);
    else
        clearSelect(ddlStreet);

    loadCityType(this.value);
    loadStreetType();
});

ddlStreet.change(function() {
    loadStreetType(this.value);
});

/**
 * JS функции для работы с формой подразделений
 * @author Richok FG
 */

var ddlCityGeneral = $('[name="ListWarehouse[city]"]');
var txtRegion2 = $('#txt_region2');
var txtRegion1 = $('#txt_region1');
var txtCountry = $('#txt_country');

var txtAddrEn = $('#txt_addr_en');
var txtAddrRu = $('#txt_addr_ru');
var txtAddrUk = $('#txt_addr_uk');
var txtAddrShortEn = $('#txt_addr_short_en');
var txtAddrShortRu = $('#txt_addr_short_ru');
var txtAddrShortUk = $('#txt_addr_short_uk');

function clearCityRelatedData() {
    txtRegion1.val('');
    txtRegion2.val('');
    txtCountry.val('');
}

function loadCityRelatedData(cityId) {
    jQuery.getJSON(url_get_city_related_data, { city_id: cityId }, function(data) {
        txtRegion1.val(data['region1_type_name'] + ' ' + data['region1_name']);
        txtRegion2.val(data['region2_type_name'] + ' ' + data['region2_name']);
        txtCountry.val(data['country_name']);
    });
}

ddlCityGeneral.change(function() {
    if (this.value) {
        loadCityRelatedData(this.value);
    }
    else {
        clearCityRelatedData();
    }
});

grid_schedule.attachEvent('onAfterEditStop',function(state, editor) {

    var item = this.getItem(editor.row);

    // проверка правильности введенного времени
    if (state.value.localeCompare('24:00') > 0)
        item[editor.column] = '24:00';

    // автозаполение расписания
    if (item['dayofweek'] != 6 && item['dayofweek'] != 7) {
        grid_schedule.eachRow(
            function (row) {
                var row = this.getItem(row);
                if (row['schedule_type'] == item['schedule_type'] && row['dayofweek'] != 6 && row['dayofweek'] != 7) {

                    if (row['time_begin'] == '00:00')
                        row['time_begin'] = item['time_begin'];

                    if (row['time_end'] == '00:00')
                        row['time_end'] = item['time_end'];
                }
            }
        )
    }
});

function formatShortAddress(streetType, streetName, buildingType1, number1, buildingType2, number2, buildingType3, number3) {

    var addr = '';
    if (streetName)
        addr += streetType + ' ' + streetName;
    if (buildingType1)
        addr += ' ' + buildingType1 + ' ' + number1;
    if (buildingType2)
        addr += ' ' + buildingType2 + ' ' + number2;
    if (buildingType3)
        addr += ' ' + buildingType3 + ' ' + number3;

    return addr;
}

function formatFullAddress(country, index, region1, regionType1, region2, regionType2, city, cityType, shortAddress) {

    var addr = country;
    if (index)
        addr += ', ' + index;
    if (region1)
        addr += ', ' + region1 + ' ' + regionType1;
    if (region2)
        addr += ', ' + region2 + ' ' + regionType2;
    if (city)
        addr += ', ' + cityType + ' ' + city;
    if (shortAddress)
        addr += ', ' + shortAddress;

    return addr;
}

function setAddresses() {
    jQuery.getJSON(url_get_building_related_data,
        {
            street: ddlStreet.val(),
            buildingtype1: (ddlBuildingType1.val()) ? ddlBuildingType1.val() : 0,
            buildingtype2: (ddlBuildingType2.val()) ? ddlBuildingType2.val() : 0,
            buildingtype3: (ddlBuildingType3.val()) ? ddlBuildingType3.val() : 0
        }, function (data) {

            txtAddrShortEn.val(formatShortAddress(data['street_type_en'], data['street_en'],
                data['buildingtype1_en'], txtNumber1.val(),
                data['buildingtype2_en'], txtNumber2.val(),
                data['buildingtype3_en'], txtNumber3.val()));

            txtAddrShortUk.val(formatShortAddress(data['street_type_uk'], data['street_uk'],
                data['buildingtype1_uk'], txtNumber1.val(),
                data['buildingtype2_uk'], txtNumber2.val(),
                data['buildingtype3_uk'], txtNumber3.val()));

            txtAddrShortRu.val(formatShortAddress(data['street_type_ru'], data['street_ru'],
                data['buildingtype1_ru'], txtNumber1.val(),
                data['buildingtype2_ru'], txtNumber2.val(),
                data['buildingtype3_ru'], txtNumber3.val()));

            txtAddrEn.val(formatFullAddress(data['country_en'], '',
                data['region1_en'], data['region1_type_en'],
                data['region2_en'], data['region2_type_en'],
                data['city_en'], data['city_type_en'],
                txtAddrShortEn.val()));

            txtAddrUk.val(formatFullAddress(data['country_uk'], '',
                data['region1_uk'], data['region1_type_uk'],
                data['region2_uk'], data['region2_type_uk'],
                data['city_uk'], data['city_type_uk'],
                txtAddrShortUk.val()));

            txtAddrRu.val(formatFullAddress(data['country_ru'], '',
                data['region1_ru'], data['region1_type_ru'],
                data['region2_ru'], data['region2_type_ru'],
                data['city_ru'], data['city_type_ru'],
                txtAddrShortRu.val()));
        });
}

ddlStreet.change(function() { setAddresses(); });
ddlBuildingType1.change(function() { setAddresses(); });
ddlBuildingType2.change(function() { setAddresses(); });
ddlBuildingType3.change(function() { setAddresses(); });
txtNumber1.keyup(function() { setAddresses(); });
txtNumber2.keyup(function() { setAddresses(); });
txtNumber3.keyup(function() { setAddresses(); });

grid_routes.attachEvent('onAfterEditStop', function(state, editor) {

    var item = this.getItem(editor.row);
    if (!item)
        return;

    if (editor.column == 'country') {
        jQuery.getJSON(url_get_cities, { country: item['country'], format: 2 }, function(data) {
            grid_routes.getColumnConfig('city').options = data;
        });
    }
});

grid_routes.attachEvent('onBeforeEditStart', function(state) {
    var item = this.getItem(state.row);
    if (!item)
        return;

    if (state.column == 'city') {
        jQuery.getJSON(url_get_cities, { country: item['country'], format: 2 }, function(data) {
            grid_routes.getColumnConfig('city').options = data;
        });
    }
});

grid_routes.attachEvent('onSelectChange', function() {
    var item_id = this.getSelectedId();
    var item = this.getItem(item_id);
    if (!item)
        return;

    jQuery.getJSON(url_get_cities, { country: item['country'], format: 2 }, function(data) {
        grid_routes.getColumnConfig('city').options = data;
    });
});

function setLvl1(block) {
    if (block) {
        ddlBuildingType1[0].selectedIndex = 0;
        hidBuildingType1.val(null);
        txtNumber1.val('');
    }
    ddlBuildingType1.attr('disabled', block);
    txtNumber1.attr('readonly', !ddlBuildingType1.val());
}

if (!isDisableEdit) {
    ddlStreet.change(function () {
        setLvl1(ddlStreet.val() == false);
        ddlBuildingType1.change();
        ddlBuildingType2.change();
    }).change();
}

$(function(){
    grid_zones.attachEvent('onDataUpdate',function(id,data){
        checkGridValues(data,[
            'actual_weight_from',
            'actual_weight_to',
            'height_from',
            'height_to',
            'lenght_from',
            'lenght_to',
            'width_from',
            'width_to',
        ],'grid_zones');

        checkGridTextVal(data, [
            'name_en',
            'name_uk',
            'name_ru',
        ], 'grid_addservice');

    });
    
    grid_schedule.attachEvent('onDataUpdate', function (id, data) {
        checkGridDate(data, [
            'time_begin',
            'time_end',
        ], 'grid_schedule');

    });

    grid_cameras.attachEvent('onDataUpdate', function (id, data) {
        checkGridTextVal(data, [
            'camera_name',
            'camera_model',
            'placement',
            'addition_info',
        ], 'grid_cameras');

    });
});


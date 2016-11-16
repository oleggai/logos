/**
 * JS функции для работы с формой улиц
 * @author Richok FG
 */

var ddlCity = $('#ddl_city');
var txtCityType = $('#city_type_name');
var txtRegion1 = $('#region1_name');
var txtRegion1Type = $('#region1_type_name');
var txtRegion2 = $('#region2_name');
var txtRegion2Type = $('#region2_type_name');
var txtCountry = $('#country_name');

ddlCity.change(function() {
    if (this.value) {
        loadCityRelatedData(this.value);
    }
    else {
        clearCityRelatedData();
    }
});

function loadCityRelatedData(cityId) {
    jQuery.getJSON(url_get_city_related_data, { city_id: cityId }, function(data) {
        txtCityType.val(data['city_type_name']);
        txtRegion1.val(data['region1_name']);
        txtRegion1Type.val(data['region1_type_name']);
        txtRegion2.val(data['region2_name']);
        txtRegion2Type.val(data['region2_type_name']);
        txtCountry.val(data['country_name']);
    });
}

function clearCityRelatedData() {
    txtCityType.val('');
    txtRegion1.val('');
    txtRegion1Type.val('');
    txtRegion2.val('');
    txtRegion2Type.val('');
    txtCountry.val('');
}
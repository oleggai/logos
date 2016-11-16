var input_region1_type = $('#listregion-parent-region_type');
var input_region1 = $('#listregion-parent_id');



function loadRegionType(region_ref, element){

    if (!region_ref)
        element.val('');
    else
        $.getJSON(url_get_region_type, { region:region_ref},
            (function(this_item) {
                    return function(data) {
                        this_item.val(data).change();
                    };
                }(element)
            )
        );
}


/**
 * Получение регионов 1го уровня, в зависимости от выбранного типа
 */
function loadRegions(type_ref){

    jQuery.getJSON(url_get_regions, { region_type :type_ref}, function(data) {

        var regions = input_region1;
        var options = regions.data('select2').options.options;
        regions.html('');

        var items = [];
        for (var i = 0; i < data.length; i++){
            items.push({ "id": data[i]['id'],"text": data[i]['txt']});
            regions.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
        }

        options.data = items;
        regions.select2(options);


    });
}


input_region1.on("select2:select",function(){loadRegionType(this.value,input_region1_type);});
input_region1.on("select2:unselect",function(){loadRegionType(this.value,input_region1_type)});


input_region1_type.on("select2:select",function(){loadRegions(this.value);});
input_region1_type.on("select2:unselect",function(){loadRegions(this.value)});

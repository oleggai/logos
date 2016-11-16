/**
 * Created by efomenko on 17.04.2015.
 */
$(document).ready(function () {

    var ns = $('ol.sortable').nestedSortable({
        forcePlaceholderSize: true,
        items: 'li',
        placeholder: 'placeholder',
        change: function(){
            $('#save_menu_items_order').show();
        }

    });

    $('#save_menu_items_order').click(function () {

        var arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});
        $.post(base_url, {data: JSON.stringify(arraied)}, function () {
            document.location.reload();
        });

    });


});
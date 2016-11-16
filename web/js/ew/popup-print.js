$(document).ready(function() {

    // Вешаем событие клика на ссылки печати инвойса
    $('.invoice_print_popup').bind('click', function(e) {
        var title = $(this).attr('title');
        var href = $(this).attr('href');

        var blockAjax = true;

        if(showPopup) {

            var checkboxes = $('.checkbox-popup-print');
            // Убираем disabled в чекбоксах
            $.each(checkboxes, function(index, value) {
                $(value).attr('disabled', false);
            });

            $('#div-popup').css('display', 'block');

            $('#close-popup').bind('click', function() {
                $('#div-popup').css('display', 'none');
            });

            $('#button-popup-print').unbind('click');
            $('#button-popup-print').bind('click', function() {
                checkboxes = $('.checkbox-popup-print');
                var print_disp_delivery_cost = $('#printsetupuser-print_disp_delivery_cost').prop('checked');
                var print_disp_third_party   = $('#printsetupuser-print_disp_third_party').prop('checked');
                var print_disp_actual_weight   = $('#printsetupuser-print_disp_actual_weight').prop('checked');
                if(blockAjax) {
                    blockAjax = false;
                    $.ajax({
                        dataType: 'json',
                        method: 'POST',
                        url: urlPrintInvoice,
                        data: {
                            'print_disp_delivery_cost': +print_disp_delivery_cost,
                            'print_disp_third_party': +print_disp_third_party,
                            'print_disp_actual_weight': +print_disp_actual_weight
                        },
                        success: function (responce) {
                            parent.application_create_new_tab(title, href, false, false, false);
                            blockAjax = true;
                        }
                    });
                }
            });
        }
        else {
            parent.application_create_new_tab(title, href,false,false,false);
        }

    });
});
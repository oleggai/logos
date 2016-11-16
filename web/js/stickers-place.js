$(document).ready(function() {

    var stickerFirstPlaceNonTrans = false;
    var stickerFirstPlaceTrans    = false;


    var tabTitleStickerFirstPlaceNonTrans = '';
    var tabTitleStickerFirstPlaceTrans = '';

    var url = 0;
    var tab_title = '';
    var numberPlace = 1;
    $('.webix_modal_cover').click(function() {
        $('.webix_modal_box').css('display', 'none');
        $(this).css('display', 'none');
        $('#numberPlace').val('');
    });

    $('#showFirstPlace').bind('click', function() {
        numberPlace = $('#numberPlace').val();
        if(stickerFirstPlaceNonTrans) {
            url       = urlNonTrans + '&numberPlace=' + numberPlace;
            tab_title = tabTitleStickerFirstPlaceNonTrans;
            stickerFirstPlaceNonTrans = false;
        }
        if(stickerFirstPlaceTrans) {
            url       = urlTrans + '&numberPlace=' + numberPlace;
            tab_title = tabTitleStickerFirstPlaceTrans;
            stickerFirstPlaceTrans = false;
        }
        parent.application_create_new_tab(tab_title,url,false,false,false);
    });


    $('#stickerFirstPlaceNonTrans').bind('click', function() {
        stickerFirstPlaceNonTrans = true;
        tabTitleStickerFirstPlaceNonTrans = tabNonTrans;
        $('.webix_popup_title').html(tabTitleStickerFirstPlaceNonTrans);
        $('.webix_modal_box').css('display', 'inline-block');
        $('.webix_modal_cover').css('display', 'inline-block');
        $('#numberPlace').focus();
        return false;
    });
    $('#stickerFirstPlaceTrans').bind('click', function() {
        stickerFirstPlaceTrans = true;
        tabTitleStickerFirstPlaceTrans = tabTrans;
        $('.webix_popup_title').html(tabTitleStickerFirstPlaceTrans);
        $('.webix_modal_box').css('display', 'inline-block');
        $('.webix_modal_cover').css('display', 'inline-block');
        $('#numberPlace').focus();
        return false;
    });
});
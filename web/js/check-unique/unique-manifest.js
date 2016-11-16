$(document).ready(function() {
    var data = '';
    $('#manifest-mn_num').bind('change', function() {
        data = {'manifestNumber': $(this).val()};
        if($(this).val() != oldManifestNumber) {
            checkUnique(data);
        }
    });
});

function checkUnique(data) {
    $.ajax({
        dataType: 'json',
        type: 'POST',
        url: urlCheckUnique,
        data: data,
        success: function(responce) {
            if(responce.message) {
                parent.show_app_alert('Error', responce.message, 'Ok');
            }
        }
    });
}

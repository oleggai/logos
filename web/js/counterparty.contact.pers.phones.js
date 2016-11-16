/**
 * Created by Hopr on 02.11.2015.
 */

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

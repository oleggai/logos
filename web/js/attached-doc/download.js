
/*grid_doc_file.attachEvent("onAfterLoad", function(){
 var links = $('[data-url-download-file]')
});*/
$('#webix_grid_grid_doc_file').on('click', '[data-url-download-file]',  function() {
    parent.application_create_new_tab('Download File', $(this).attr('data-url-download-file'), false, false, false);
});

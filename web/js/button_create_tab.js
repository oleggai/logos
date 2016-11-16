function app_add_new_tab_from_iframe(element) {
    if (element.getAttribute("disabled"))
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    var linked_frame = null;
    if (element.getAttribute("par_wid")) {
        linked_frame = this_ifr_id.replace('ifarme_','tabheader_');
        this_ifr_id = element.getAttribute("par_wid");
    }

    parent.application_create_new_tab(element.getAttribute("tab_title"),element.getAttribute("ifr_url"),
        element.getAttribute("unique_tab_id"),element.getAttribute("return_data_to"),
        this_ifr_id,element.getAttribute("trigger_el_id"),element.getAttribute("with_creation"), linked_frame);
};
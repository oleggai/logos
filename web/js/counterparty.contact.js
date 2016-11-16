// кнопки
var contactAddBtn  = $('#contact_add_button');
var contactUpdateBtn  = $('#contact_update_button');
var contactDeleteBtn  = $('#contact_del_button');
var contactRestoreBtn  = $('#contact_restore_button');
var contactRefreshBtn  = $('#contact_refresh_button');

//функции
function contactShowButtons(item){
    if (is_deleted)
    {
        contactAddBtn.hide();
        contactUpdateBtn.hide();
        contactDeleteBtn.hide();
        contactRestoreBtn.hide();
        contactRefreshBtn.hide();
    }
    else if (!item){
        contactDeleteBtn.hide();
        contactRestoreBtn.hide();
        contactUpdateBtn.hide();
    }
    else if (item['state']==1){
        contactDeleteBtn.show();
        contactRestoreBtn.hide();
        contactUpdateBtn.show();
    }
    else{
        contactDeleteBtn.hide();
        contactRestoreBtn.show();
        contactUpdateBtn.hide();
    }
}

// события
contactAddBtn.click(function() {

    var cId = cpId.val();
    if (cId == '')
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Создание контактного лица':'Contact person create',
        url_prefix + '?r=counterparty/counterparty-contact-pers/create&counterparty=' + cId,
        'counterpartycontactperscreate',
        'false',
        this_ifr_id,
        'false');
});

contactUpdateBtn.click(function() {

    var item_id = grid_contact_pers.getSelectedId();
    var item = grid_contact_pers.getItem(item_id);

    if (!item)
        return;

    if (item['state']==100){
        parent.show_app_alert('Attention!', "Item is deleted, edit disable. To enable edit restore item", 'Ok');
        return;
    }

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Контактное лицо ':'Contact person ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contact-pers/update&id=' + item['id'],
        'counterpartycontactpersupdate' + item['id'],
        'false',
        this_ifr_id,
        'false');
});

contactDeleteBtn.click(function() {

    var item_id = grid_contact_pers.getSelectedId();
    var item = grid_contact_pers.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contact-pers/delete-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    contactRefreshBtn.click();
});

contactRestoreBtn.click(function() {

    var item_id = grid_contact_pers.getSelectedId();
    var item = grid_contact_pers.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contact-pers/restore-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    contactRefreshBtn.click();
});

contactRefreshBtn.click(function() {

    grid_contact_pers.clearAll();
    grid_contact_pers.load(grid_contact_pers.config.url);

    var item_id = grid_contact_pers.getSelectedId();
    var item = grid_contact_pers.getItem(item_id);
    contactShowButtons(item);

    phoneRefreshBtn.click();
    emailRefreshBtn.click();
    otherRefreshBtn.click();
});

grid_contact_pers.attachEvent("onSelectChange", function (){

    var item_id = this.getSelectedId();
    var item = this.getItem(item_id);

    grid_contact_pers_phones.filter(function(obj){

        if (!item)
            return true;

        return obj.counterparty_contact_pers == item.id;
    });

    grid_contact_pers_email.filter(function(obj){

        if (!item)
            return true;

        return obj.counterparty_contact_pers == item.id;
    });

    grid_contact_pers_othercontact.filter(function(obj){

        if (!item)
            return true;

        return obj.counterparty_contact_pers == item.id;
    });

    contactShowButtons(item);
    phoneShowButtons(null);
    emailShowButtons(null);
    otherShowButtons(null);

});

grid_contact_pers.attachEvent("onItemDblClick", function () {

    var item_id = grid_contact_pers.getSelectedId();
    var item = grid_contact_pers.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Контактное лицо ':'Contact person ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contact-pers/view&id=' + item['id'],
        'counterpatycontactpersview' + item['id'],
        'false',
        this_ifr_id,
        'false');

});

contactShowButtons(null);

////////////////////телефоны///////////////////////////

// кнопки
var phoneAddBtn  = $('#phone_add_button');
var phoneUpdateBtn  = $('#phone_update_button');
var phoneDeleteBtn  = $('#phone_del_button');
var phoneRestoreBtn  = $('#phone_restore_button');
var phoneRefreshBtn  = $('#phone_refresh_button');

function phoneShowButtons(item){

    var c_item_id = grid_contact_pers.getSelectedId();
    var c_item = grid_contact_pers.getItem(c_item_id);

    if (c_item && c_item['state']==1)
        phoneAddBtn.show();
    else
        phoneAddBtn.hide();

    if (is_deleted)
    {
        phoneAddBtn.hide();
        phoneUpdateBtn.hide();
        phoneDeleteBtn.hide();
        phoneRestoreBtn.hide();
        phoneRefreshBtn.hide();
    }
    else if (!item){
        phoneDeleteBtn.hide();
        phoneRestoreBtn.hide();
        phoneUpdateBtn.hide();
    }
    else if (item['state']==1){
        phoneDeleteBtn.show();
        phoneRestoreBtn.hide();
        phoneUpdateBtn.show();
    }
    else{
        phoneDeleteBtn.hide();
        if (c_item && c_item['state']==1) //немного криво - восстановить можно только если в гриде контактов выбрана запись к которой эта привязана
            phoneRestoreBtn.show();
        phoneUpdateBtn.hide();
    }
}

// события
phoneAddBtn.click(function() {

    var item_id = grid_contact_pers.getSelectedId();
    var item = grid_contact_pers.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Создание телефона контактного лица':'Contact person phone create',
        url_prefix + '?r=counterparty/counterparty-contact-pers-phones/create&contact=' + item['id'],
        'counterpartycontactpersphonecreate',
        'false',
        this_ifr_id,
        'false');
});

phoneUpdateBtn.click(function() {

    var item_id = grid_contact_pers_phones.getSelectedId();
    var item = grid_contact_pers_phones.getItem(item_id);

    if (!item)
        return;

    if (item['state']==100){
        parent.show_app_alert('Attention!', "Item is deleted, edit disable. To enable edit restore item", 'Ok');
        return;
    }

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Телефон контактного лица ':'Contact person phone ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contact-pers-phones/update&id=' + item['id'],
        'counterpartycontactpersphoneupdate' + item['id'],
        'false',
        this_ifr_id,
        'false');
});

phoneDeleteBtn.click(function() {

    var item_id = grid_contact_pers_phones.getSelectedId();
    var item = grid_contact_pers_phones.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contact-pers-phones/delete-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    phoneRefreshBtn.click();
});

phoneRestoreBtn.click(function() {

    var item_id = grid_contact_pers_phones.getSelectedId();
    var item = grid_contact_pers_phones.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contact-pers-phones/restore-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    phoneRefreshBtn.click();
});

phoneRefreshBtn.click(function() {

    var contact_item_id = grid_contact_pers.getSelectedId();
    var contact_item = grid_contact_pers.getItem(contact_item_id);

    grid_contact_pers_phones.clearAll();
    grid_contact_pers_phones.load(grid_contact_pers_phones.config.url, function(text, data, http_request) {
        grid_contact_pers_phones.filter(function (obj) {

            if (!contact_item)
                return true;

            return obj.counterparty_contact_pers == contact_item.id;
        });
    });

    var item_id = grid_contact_pers_phones.getSelectedId();
    var item = grid_contact_pers_phones.getItem(item_id);
    phoneShowButtons(item);
});

grid_contact_pers_phones.attachEvent("onSelectChange", function (){

    var item_id = this.getSelectedId();
    var item = this.getItem(item_id);
    phoneShowButtons(item);
});

grid_contact_pers_phones.attachEvent("onItemDblClick", function () {

    var item_id = grid_contact_pers_phones.getSelectedId();
    var item = grid_contact_pers_phones.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Телефон контактного лица ':'Contact person phone ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contact-pers-phones/view&id=' + item['id'],
        'counterpatycontactpersphoneview' + item['id'],
        'false',
        this_ifr_id,
        'false');

});

phoneShowButtons(null);

////////////////////~телефоны///////////////////////////

////////////////////Email///////////////////////////

// кнопки
var emailAddBtn  = $('#email_add_button');
var emailUpdateBtn  = $('#email_update_button');
var emailDeleteBtn  = $('#email_del_button');
var emailRestoreBtn  = $('#email_restore_button');
var emailRefreshBtn  = $('#email_refresh_button');

function emailShowButtons(item){

    var c_item_id = grid_contact_pers.getSelectedId();
    var c_item = grid_contact_pers.getItem(c_item_id);

    if (c_item && c_item['state']==1)
        emailAddBtn.show();
    else
        emailAddBtn.hide();

    if (is_deleted)
    {
        emailAddBtn.hide();
        emailUpdateBtn.hide();
        emailDeleteBtn.hide();
        emailRestoreBtn.hide();
        emailRefreshBtn.hide();
    }
    else if (!item){
        emailDeleteBtn.hide();
        emailRestoreBtn.hide();
        emailUpdateBtn.hide();
    }
    else if (item['state']==1){
        emailDeleteBtn.show();
        emailRestoreBtn.hide();
        emailUpdateBtn.show();
    }
    else{
        emailDeleteBtn.hide();
        if (c_item && c_item['state']==1)
            emailRestoreBtn.show();
        emailUpdateBtn.hide();
    }
}

// события
emailAddBtn.click(function() {
    var item_id = grid_contact_pers.getSelectedId();
    var item = grid_contact_pers.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Создание email контактного лица':'Contact person email create',
        url_prefix + '?r=counterparty/counterparty-contact-pers-email/create&contact=' + item['id'],
        'counterpartycontactpersemailcreate',
        'false',
        this_ifr_id,
        'false');
});

emailUpdateBtn.click(function() {
    var item_id = grid_contact_pers_email.getSelectedId();
    var item = grid_contact_pers_email.getItem(item_id);

    if (!item)
        return;

    if (item['state']==100){
        parent.show_app_alert('Attention!', "Item is deleted, edit disable. To enable edit restore item", 'Ok');
        return;
    }

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Email контактного лица ':'Contact person email ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contact-pers-email/update&id=' + item['id'],
        'counterpartycontactpersemailupdate' + item['id'],
        'false',
        this_ifr_id,
        'false');
});

emailDeleteBtn.click(function() {

    var item_id = grid_contact_pers_email.getSelectedId();
    var item = grid_contact_pers_email.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contact-pers-email/delete-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    emailRefreshBtn.click();
});

emailRestoreBtn.click(function() {

    var item_id = grid_contact_pers_email.getSelectedId();
    var item = grid_contact_pers_email.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contact-pers-email/restore-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    emailRefreshBtn.click();
});

emailRefreshBtn.click(function() {

    var contact_item_id = grid_contact_pers.getSelectedId();
    var contact_item = grid_contact_pers.getItem(contact_item_id);

    grid_contact_pers_email.clearAll();
    grid_contact_pers_email.load(grid_contact_pers_email.config.url, function(text, data, http_request) {
        grid_contact_pers_email.filter(function (obj) {

            if (!contact_item)
                return true;

            return obj.counterparty_contact_pers == contact_item.id;
        });
    });

    var item_id = grid_contact_pers_email.getSelectedId();
    var item = grid_contact_pers_email.getItem(item_id);
    emailShowButtons(item);
});

grid_contact_pers_email.attachEvent("onSelectChange", function (){

    var item_id = this.getSelectedId();
    var item = this.getItem(item_id);
    emailShowButtons(item);
});

grid_contact_pers_email.attachEvent("onItemDblClick", function () {

    var item_id = grid_contact_pers_email.getSelectedId();
    var item = grid_contact_pers_email.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Email контактного лица ':'Contact person email ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contact-pers-email/view&id=' + item['id'],
        'counterpatycontactpersemailview' + item['id'],
        'false',
        this_ifr_id,
        'false');

});

emailShowButtons(null);

////////////////////~Email///////////////////////////


////////////////////Другие контакты///////////////////////////

// кнопки
var otherAddBtn  = $('#other_add_button');
var otherUpdateBtn  = $('#other_update_button');
var otherDeleteBtn  = $('#other_del_button');
var otherRestoreBtn  = $('#other_restore_button');
var otherRefreshBtn  = $('#other_refresh_button');

function otherShowButtons(item){

    var c_item_id = grid_contact_pers.getSelectedId();
    var c_item = grid_contact_pers.getItem(c_item_id);

    if (c_item && c_item['state']==1)
        otherAddBtn.show();
    else
        otherAddBtn.hide();

    if (is_deleted)
    {
        otherAddBtn.hide();
        otherUpdateBtn.hide();
        otherDeleteBtn.hide();
        otherRestoreBtn.hide();
        otherRefreshBtn.hide();
    }
    else if (!item){
        otherDeleteBtn.hide();
        otherRestoreBtn.hide();
        otherUpdateBtn.hide();
    }
    else if (item['state']==1){
        otherDeleteBtn.show();
        otherRestoreBtn.hide();
        otherUpdateBtn.show();
    }
    else{
        otherDeleteBtn.hide();
        if (c_item && c_item['state']==1)
            otherRestoreBtn.show();
        otherUpdateBtn.hide();
    }
}

// события
otherAddBtn.click(function() {

    var item_id = grid_contact_pers.getSelectedId();
    var item = grid_contact_pers.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Создание другого контактного лица ':'Contact person other contact create ',
        url_prefix + '?r=counterparty/counterparty-contact-pers-othercontact/create&contact=' + item['id'],
        'counterpartycontactpersothercreate',
        'false',
        this_ifr_id,
        'false');
});

otherUpdateBtn.click(function() {

    var item_id = grid_contact_pers_othercontact.getSelectedId();
    var item = grid_contact_pers_othercontact.getItem(item_id);

    if (!item)
        return;

    if (item['state']==100){
        parent.show_app_alert('Attention!', "Item is deleted, edit disable. To enable edit restore item", 'Ok');
        return;
    }

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Контактное лицо другой контакт ':'Сontact person оther contact '  + item['id'],
        url_prefix + '?r=counterparty/counterparty-contact-pers-othercontact/update&id=' + item['id'],
        'counterpartycontactpersotherupdate' + item['id'],
        'false',
        this_ifr_id,
        'false');
});

otherDeleteBtn.click(function() {

    var item_id = grid_contact_pers_othercontact.getSelectedId();
    var item = grid_contact_pers_othercontact.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contact-pers-othercontact/delete&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    otherRefreshBtn.click();
});

otherRestoreBtn.click(function() {

    var item_id = grid_contact_pers_othercontact.getSelectedId();
    var item = grid_contact_pers_othercontact.getItem(item_id);

    if (!item)
        return;

    $.post(url_prefix + '?r=counterparty/counterparty-contact-pers-othercontact/restore-no-form&id=' + item['id'] + '&current_operation=1000',
        {}, function(){}, "json");

    otherRefreshBtn.click();
});

otherRefreshBtn.click(function() {

    var contact_item_id = grid_contact_pers.getSelectedId();
    var contact_item = grid_contact_pers.getItem(contact_item_id);

    grid_contact_pers_othercontact.clearAll();
    grid_contact_pers_othercontact.load(grid_contact_pers_othercontact.config.url, function(text, data, http_request) {
        grid_contact_pers_othercontact.filter(function (obj) {

            if (!contact_item)
                return true;

            return obj.counterparty_contact_pers == contact_item.id;
        });
    });

    var item_id = grid_contact_pers_othercontact.getSelectedId();
    var item = grid_contact_pers_othercontact.getItem(item_id);
    otherShowButtons(item);
});

grid_contact_pers_othercontact.attachEvent("onSelectChange", function (){

    var item_id = this.getSelectedId();
    var item = this.getItem(item_id);
    otherShowButtons(item);
});


grid_contact_pers_othercontact.attachEvent("onItemDblClick", function () {

    var item_id = grid_contact_pers_othercontact.getSelectedId();
    var item = grid_contact_pers_othercontact.getItem(item_id);

    if (!item)
        return;

    var this_ifr_id=window.frameElement.getAttribute("id");
    parent.application_create_new_tab(
        ($('html').attr('lang')=='ru')?'Контактное лицо другой контакт ':'Сontact person оther contact ' + item['id'],
        url_prefix + '?r=counterparty/counterparty-contact-pers-othercontact/view&id=' + item['id'],
        'counterpatycontactpersotherview' + item['id'],
        'false',
        this_ifr_id,
        'false');

});

otherShowButtons(null);

////////////////////~Другие контакты///////////////////////////

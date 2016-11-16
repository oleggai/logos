/**
 * Created by Hopr on 30.07.2015.
 */

//--------------------поля
var contactSex = $('#counterpartycontactpers-sex');

var contactDisplayNameEn = $('#counterpartycontactpers-display_name_en');
var contactSurnameEn = $('#counterpartycontactpers-surname_en');
var contactNameEn = $('#counterpartycontactpers-name_en');
var contactSecondNameEn = $('#counterpartycontactpers-secondname_en');
var contactFullNameEn = $('#counterpartycontactpers-full_name_en');
var contactShortNameEn = $('#counterpartycontactpers-short_name_en');
var contactManualNameEn = $('#counterpartycontactpers-manual_name_en');

var contactDisplayNameUk = $('#counterpartycontactpers-display_name_uk');
var contactSurnameUk = $('#counterpartycontactpers-surname_uk');
var contactNameUk = $('#counterpartycontactpers-name_uk');
var contactSecondNameUk = $('#counterpartycontactpers-secondname_uk');
var contactFullNameUk = $('#counterpartycontactpers-full_name_uk');
var contactShortNameUk = $('#counterpartycontactpers-short_name_uk');
var contactManualNameUk = $('#counterpartycontactpers-manual_name_uk');

var contactDisplayNameRu = $('#counterpartycontactpers-display_name_ru');
var contactSurnameRu = $('#counterpartycontactpers-surname_ru');
var contactNameRu = $('#counterpartycontactpers-name_ru');
var contactSecondNameRu = $('#counterpartycontactpers-secondname_ru');
var contactFullNameRu = $('#counterpartycontactpers-full_name_ru');
var contactShortNameRu = $('#counterpartycontactpers-short_name_ru');
var contactManualNameRu = $('#counterpartycontactpers-manual_name_ru');

//--------------------функции
function formatFullName(surname, name, secondName) {
    if (surname == '' || name == '')
        return '';

    if (secondName == '')
        return surname + ' ' + name;

    return surname + ' ' + name + ' ' + secondName;
}

function formatShortName(surname, name, secondName) {
    if (surname == '' || name == '')
        return '';

    if (secondName == '')
        return surname + ' ' + name.substring(0, 1) + '. ';

    return surname + ' ' + name.substring(0, 1) + '. ' + secondName.substring(0, 1) + '.';
}

//автозаполнение полей контакта
contactSurnameEn.change(function() {
    contactFullNameEn.val(formatFullName(contactSurnameEn.val(), contactNameEn.val(),
        contactSecondNameEn.val()));
    contactShortNameEn.val(formatShortName(contactSurnameEn.val(), contactNameEn.val(),
    contactSecondNameEn.val()));
    contactFullNameEn.change();
    });
contactNameEn.change(function() { contactSurnameEn.change(); });
contactSecondNameEn.change(function() { contactSurnameEn.change(); });
contactFullNameEn.change(function() {
    if (contactManualNameEn.val() == '')
    contactDisplayNameEn.val(contactFullNameEn.val());
    });
contactManualNameEn.change(function() {
    contactDisplayNameEn.val(contactManualNameEn.val());
    });

contactSurnameUk.change(function() {
    contactFullNameUk.val(formatFullName(contactSurnameUk.val(), contactNameUk.val(),
        contactSecondNameUk.val()));
    contactShortNameUk.val(formatShortName(contactSurnameUk.val(), contactNameUk.val(),
    contactSecondNameUk.val()));
    contactFullNameUk.change();
    });
contactNameUk.change(function() { contactSurnameUk.change(); });
contactSecondNameUk.change(function() { contactSurnameUk.change(); });
contactFullNameUk.change(function() {
    if (contactManualNameUk.val() == '')
    contactDisplayNameUk.val(contactFullNameUk.val());
    });
contactManualNameUk.change(function() {
    contactDisplayNameUk.val(contactManualNameUk.val());
    });

contactSurnameRu.change(function() {
    contactFullNameRu.val(formatFullName(contactSurnameRu.val(), contactNameRu.val(),
        contactSecondNameRu.val()));
    contactShortNameRu.val(formatShortName(contactSurnameRu.val(), contactNameRu.val(),
    contactSecondNameRu.val()));
    contactFullNameRu.change();
    });
contactNameRu.change(function() { contactSurnameRu.change(); });
contactSecondNameRu.change(function() { contactSurnameRu.change(); });
contactFullNameRu.change(function() {
    if (contactManualNameRu.val() == '')
    contactDisplayNameRu.val(contactFullNameRu.val());
    });
contactManualNameRu.change(function() {
    contactDisplayNameRu.val(contactManualNameRu.val());
    });

//копирование данных с контрагента
var contactPersFromCounterpartyButton = $('#contact_pers_from_counterparty_button');

function FillFromCounterparty(data){
    if (data == null)
        return;

    contactSex.val(data['sex']);

    contactDisplayNameEn.val(data['display_name_en']);
    contactSurnameEn.val(data['surname_en']);
    contactNameEn.val(data['name_en']);
    contactSecondNameEn.val(data['secondname_en']);
    contactFullNameEn.val(data['full_name_en']);
    contactShortNameEn.val(data['short_name_en']);
    contactManualNameEn.val(data['manual_name_en']);

    contactDisplayNameUk.val(data['display_name_uk']);
    contactSurnameUk.val(data['surname_uk']);
    contactNameUk.val(data['name_uk']);
    contactSecondNameUk.val(data['secondname_uk']);
    contactFullNameUk.val(data['full_name_uk']);
    contactShortNameUk.val(data['short_name_uk']);
    contactManualNameUk.val(data['manual_name_uk']);

    contactDisplayNameRu.val(data['display_name_ru']);
    contactSurnameRu.val(data['surname_ru']);
    contactNameRu.val(data['name_ru']);
    contactSecondNameRu.val(data['secondname_ru']);
    contactFullNameRu.val(data['full_name_ru']);
    contactShortNameRu.val(data['short_name_ru']);
    contactManualNameRu.val(data['manual_name_ru']);
}

contactPersFromCounterpartyButton.click(function(){
    jQuery.getJSON(url_get_counterparty, { id:cp_id},
        function(data) {
            FillFromCounterparty(data);
        }
    );
});

if (read_only)
    contactPersFromCounterpartyButton.hide();

//транслитерация
function Transliterate(element, txt, src, tar){
    jQuery.getJSON(url_get_transliterate, { text:txt, source:src, target:tar},
        (function(this_item) {
            return function(data) {
                this_item.val(data);
                //this_item.change();
            };
        }(element)
        )
    );
}

var contactPersTransliterateButton = $('#contact_pers_transliterate_button');
var contactPersENTab = $('#contact_form_en_tab');
var contactPersUKTab = $('#contact_form_uk_tab');
var contactPersRUTab = $('#contact_form_ru_tab');

if (read_only)
    contactPersTransliterateButton.hide();

contactPersTransliterateButton.click(function() {
    if (contactPersENTab.hasClass('active')) {
        if (contactSurnameUk.val() == '')
            Transliterate(contactSurnameUk, contactSurnameEn.val(), 'en', 'uk');
        if (contactSurnameRu.val() == '')
            Transliterate(contactSurnameRu, contactSurnameEn.val(), 'en', 'ru');

        if (contactNameUk.val() == '')
            Transliterate(contactNameUk, contactNameEn.val(), 'en', 'uk');
        if (contactNameRu.val() == '')
            Transliterate(contactNameRu, contactNameEn.val(), 'en', 'ru');

        if (contactSecondNameUk.val() == '')
            Transliterate(contactSecondNameUk, contactSecondNameEn.val(), 'en', 'uk');
        if (contactSecondNameRu.val() == '')
            Transliterate(contactSecondNameRu, contactSecondNameEn.val(), 'en', 'ru');

        if (contactShortNameUk.val() == '')
            Transliterate(contactShortNameUk, contactShortNameEn.val(), 'en', 'uk');
        if (contactShortNameRu.val() == '')
            Transliterate(contactShortNameRu, contactShortNameEn.val(), 'en', 'ru');

        if (contactFullNameUk.val() == '')
            Transliterate(contactFullNameUk, contactFullNameEn.val(), 'en', 'uk');
        if (contactFullNameRu.val() == '')
            Transliterate(contactFullNameRu, contactFullNameEn.val(), 'en', 'ru');

        if (contactManualNameUk.val() == '')
            Transliterate(contactManualNameUk, contactManualNameEn.val(), 'en', 'uk');
        if (contactManualNameRu.val() == '')
            Transliterate(contactManualNameRu, contactManualNameEn.val(), 'en', 'ru');

        if (contactDisplayNameUk.val() == '')
            Transliterate(contactDisplayNameUk, contactDisplayNameEn.val(), 'en', 'uk');
        if (contactDisplayNameRu.val() == '')
            Transliterate(contactDisplayNameRu, contactDisplayNameEn.val(), 'en', 'ru');
    }
    if (contactPersUKTab.hasClass('active')) {
        if (contactSurnameEn.val() == '')
            Transliterate(contactSurnameEn, contactSurnameUk.val(), 'uk', 'en');
        if (contactSurnameRu.val() == '')
            Transliterate(contactSurnameRu, contactSurnameUk.val(), 'uk', 'ru');

        if (contactNameEn.val() == '')
            Transliterate(contactNameEn, contactNameUk.val(), 'uk', 'en');
        if (contactNameRu.val() == '')
            Transliterate(contactNameRu, contactNameUk.val(), 'uk', 'ru');

        if (contactSecondNameEn.val() == '')
            Transliterate(contactSecondNameEn, contactSecondNameUk.val(), 'uk', 'en');
        if (contactSecondNameRu.val() == '')
            Transliterate(contactSecondNameRu, contactSecondNameUk.val(), 'uk', 'ru');

        if (contactShortNameEn.val() == '')
            Transliterate(contactShortNameEn, contactShortNameUk.val(), 'uk', 'en');
        if (contactShortNameRu.val() == '')
            Transliterate(contactShortNameRu, contactShortNameUk.val(), 'uk', 'ru');

        if (contactFullNameEn.val() == '')
            Transliterate(contactFullNameEn, contactFullNameUk.val(), 'uk', 'en');
        if (contactFullNameRu.val() == '')
            Transliterate(contactFullNameRu, contactFullNameUk.val(), 'uk', 'ru');

        if (contactManualNameEn.val() == '')
            Transliterate(contactManualNameEn, contactManualNameUk.val(), 'uk', 'en');
        if (contactManualNameRu.val() == '')
            Transliterate(contactManualNameRu, contactManualNameUk.val(), 'uk', 'ru');

        if (contactDisplayNameEn.val() == '')
            Transliterate(contactDisplayNameEn, contactDisplayNameUk.val(), 'uk', 'en');
        if (contactDisplayNameRu.val() == '')
            Transliterate(contactDisplayNameRu, contactDisplayNameUk.val(), 'uk', 'ru');
    }
    if (contactPersRUTab.hasClass('active')) {
        if (contactSurnameEn.val() == '')
            Transliterate(contactSurnameEn, contactSurnameRu.val(), 'ru', 'en');
        if (contactSurnameUk.val() == '')
            Transliterate(contactSurnameUk, contactSurnameRu.val(), 'ru', 'uk');

        if (contactNameEn.val() == '')
            Transliterate(contactNameEn, contactNameRu.val(), 'ru', 'en');
        if (contactNameUk.val() == '')
            Transliterate(contactNameUk, contactNameRu.val(), 'ru', 'uk');

        if (contactSecondNameEn.val() == '')
            Transliterate(contactSecondNameEn, contactSecondNameRu.val(), 'ru', 'en');
        if (contactSecondNameUk.val() == '')
            Transliterate(contactSecondNameUk, contactSecondNameRu.val(), 'ru', 'uk');

        if (contactShortNameEn.val() == '')
            Transliterate(contactShortNameEn, contactShortNameRu.val(), 'ru', 'en');
        if (contactShortNameUk.val() == '')
            Transliterate(contactShortNameUk, contactShortNameRu.val(), 'ru', 'uk');

        if (contactFullNameEn.val() == '')
            Transliterate(contactFullNameEn, contactFullNameRu.val(), 'ru', 'en');
        if (contactFullNameUk.val() == '')
            Transliterate(contactFullNameUk, contactFullNameRu.val(), 'ru', 'uk');

        if (contactManualNameEn.val() == '')
            Transliterate(contactManualNameEn, contactManualNameRu.val(), 'ru', 'en');
        if (contactManualNameUk.val() == '')
            Transliterate(contactManualNameUk, contactManualNameRu.val(), 'ru', 'uk');

        if (contactDisplayNameEn.val() == '')
            Transliterate(contactDisplayNameEn, contactDisplayNameRu.val(), 'ru', 'en');
        if (contactDisplayNameUk.val() == '')
            Transliterate(contactDisplayNameUk, contactDisplayNameRu.val(), 'ru', 'uk');
    }
});

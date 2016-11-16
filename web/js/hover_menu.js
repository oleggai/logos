/**
 * Created by ogaidaienko on 27.05.2015.
 */

$(document).ready(function() {

    $('.navbar-nav .dropdown').hover(function() {

        $(this).addClass('open');
        $('a["aria-expanded"]',$(this)).attr('true');

    }, function() {

        $(this).removeClass('open');
        $('a["aria-expanded"]',$(this)).attr('false');

    });
});

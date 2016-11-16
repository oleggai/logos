$(document).ready(function() {
    var spans = $('.tracking-info-2 span');
    var span = $(spans).eq(0);
    var td = $('.tracking-info-2');

    var minFontSize = 8;
    $(span).css('fontSize', minFontSize + 'px');

    var tdWidth = $(td).width();
    var tdHeight = $(td).height();

    var fontSize = '';
    var oldFontSize = '';
    var tdHeightNew = '';
    var tdWidthNew = '';
    while(1) {
        $(span).css('fontSize', minFontSize + 'px');
        tdHeightNew = $(td).height();
        tdWidthNew = $(td).width();
        if(tdWidthNew !== tdWidth || tdHeightNew !== tdHeight) {
            $(span).css('fontSize', oldFontSize + 'px');
            break;
        }
        oldFontSize = minFontSize;
        minFontSize += 0.5;
    }
    $(spans).css('fontSize', oldFontSize + 'px');
});
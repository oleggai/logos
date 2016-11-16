/**
 * Created by ypalaguta on 06.10.2015.
 */

//валидация полей даты
    $(function(){
var dateEls=$('.datetimewithbutton').parent();
for(var i=0;i<dateEls.length;i++)
    if($(dateEls[i]).attr('disabled')!='disabled'&&$(dateEls[i]).attr('readonly')!='readonly'){
        dateEls[i].addEventListener('blur',function(){valiDate($(this).find('.datetimewithbutton'));},true);
    }

        $('#af_ew_entity_date_begin,#af_ew_entity_date_end').addClass('clearablecombo');

    });

function valiDate(elem){

    setTimeout(
        function(){
         elem=$(elem);
         var elemVal = elem.val();
         var elemMask = elem.attr('mask').replace(/[0-9]/g,'_');
            var elemDate=new Date.parse(elemVal);
            var today=globalDate;
          //  console.log(today-elemDate);
            if((Date.parse(elemVal)==null||today-elemDate<(-86400000))&&elemVal!=elemMask&&elemVal!=''){
                elem.val('');
                elem.css('box-shadow','0 0 2px 1px #a94442');
            }
            else
                elem.css('box-shadow','none');

        }, 200);
}

function checkGridDate(data,mas,gridName){
    try{
console.log( data[mas[0]]);
console.log( data);
        if(parseInt(data.id)>0)
            for(var i=0;i<mas.length;i++)
                data[mas[i]]=(Date.parse(data[mas[i]])!=null)?data[mas[i]]:"";

        else
            for(var i=0;i<mas.length;i++){
                var tempData=eval(gridName+".getItem("+data.id+")."+mas[i]);
                if(tempData!=undefined){
                    tempData= (Date.parse(tempData)!=null)?tempData:"''";
                    eval(gridName+".getItem("+data.id+")."+mas[i]+"="+tempData+";");
                }
            }


    }
    catch(ex) {
        console.log(ex);
    }
}

function custom_columnTest(obj, common, value){
    return '<div type="checkbox" style="background-color:'+obj.color+';width: 100%;height: 100%;padding:2px 4px;" >'+value+'</div>';
}

function filterInputmaskDate(insertedDate){
    var maxDay=globalMaxDate;
    var convertedDate=convertDateTime(insertedDate);
    if(globalMaxDate-convertedDate[0]<0)
        return globalMaxDate.format('d.m.Y h:i:s');
    else
        return insertedDate;
}

function convertDateTime(str){
    var parts = str.split(".");
    var timePart=parts[2].split(' ')[1];
    parts[2]=parts[2].split(' ')[0];
    var dt = new Date(parseInt(parts[2], 10),
        parseInt(parts[1], 10) - 1,
        parseInt(parts[0], 10));

    return[dt,timePart];
}

function swapValues(input,newVal){
    if(input.val()!=newVal){
        var start=input[0].selectionStart;
        var end=input[0].selectionEnd;
        input.val(newVal);
        input[0].selectionEnd=end;
        input[0].selectionStart=start;
    }
}

function getLocation(file){
    loc=location;
    return  loc.origin+((loc.pathname.indexOf('/web/')>=0)?'/web/':'/')+file;
}
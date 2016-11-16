<?php
/* @var $this yii\web\View */
/* @var $widget app\widgets\GridWidget*/

use app\assets\GridAsset;
use app\models\common\CommonModel;
use app\models\common\Langs;
use app\widgets\BtnCreateTab;
use app\widgets\GridWidget;
use yii\helpers\Html;
use yii\web\View;

GridAsset::register($this);

$getParams = Yii::$app->getRequest()->get();
?>

<style>
    .move_to_level_item{background-color: #d3d3d3 !important}
</style>

<!-- фильтры -->
<?php if($widget->filters){ ?>

    <div id="form_filter_basic" class="filter_form_div" data-name="<?=$widget->grid_name?>">
        <div class="filter_div_showhide">
            <div class="filter_div_showhide_btn showhide_btn_left"></div>
        </div>


        <?= $widget->filtersHtml ?>

        <!-- изменение языка -->
        <?php if ($widget->lang_dependency && $widget->lang_selector) { ?>

            <script>
                //изменение языка
                var lang_selector = '<?= $widget->lang_selector['id'] ?>';
                var lang_dependency = <?= json_encode($widget->lang_dependency) ?>;

                $('#'+lang_selector).change(function (){

                    for (var i =0; i< lang_dependency.length; i++){

                        var item = lang_dependency[i];
                        var html_item = $("#"+item['id']);

                        // обновление выпадающего списка
                        if (item['type'] == <?= CommonModel::FILTER_DROPDOWN ?>) {

                            jQuery.getJSON(item['url'], { lang:this.value},(function(this_item) {
                                return function(data) {

                                    this_item.empty();
                                    for (var i = 0; i < data.length; i++)
                                        this_item.append($('<option></option>').val(data[i]['id']).html(data[i]['txt']));

                                };
                            }(html_item)) );
                        }

                        // обновление select2
                        else if (item['type'] == <?= CommonModel::FILTER_SELECT2 ?>) {

                            jQuery.getJSON(item['url'], { lang:this.value},(function(this_item) {
                                return function(data) {

                                    var options = this_item.data('select2').options.options;
                                    this_item.html('');

                                    var items = [];
                                    for (var i = 0; i < data.length; i++){
                                        items.push({ "id": data[i]['id'],"text": data[i]['txt']});
                                        this_item.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
                                    };

                                    options.data = items;
                                    this_item.select2(options);

                                };
                            }(html_item)) );
                        }

                        // обновление дропдаугов чекбоксов
                        else if (item['type'] == <?= CommonModel::FILTER_CHECKBOXESDROPDOWN ?>) {
                            jQuery.getJSON(item['url'], { lang:this.value},(function(this_item_super) {
                                return function(data) {
                                    var this_item = this_item_super.find('ul');
                                    this_item.empty();
                                    for (var i = 0; i < data.length; i++)
                                        if(data[i]['id']!=='') // without empty
                                            this_item.append($('<li class="dropdown_label"></li>')
                                                .html(''
                                                +'<label class="dropdown_label">'
                                                +'<input onclick="clickMultiSelect(this)" type="checkbox" value="'
                                                +data[i]['id']+'" id="check_'
                                                +this_item.attr("class")+'" class="check_'
                                                +this_item.attr("class")+'" />'
                                                +data[i]['txt']+'</label>'));

                                };
                            }(html_item)));
                        }

                    }
                });

            </script>

        <?php } ?>

    </div>

    <script>

        //при нажатии на кнопку фильтр
        var <?= $widget->grid_name ?>_last_filter_url = "<?= $widget->url ?>";
        var filters = <?= json_encode($widget->filters)?>;
        $('#form_filter_basic .filter_submit_btn').click(
            function(){
                var url = "<?= $widget->filter_url ?>";

                for (var i =0; i< filters.length; i++) {

                    var e = $('#'+filters[i]['id']);
                    var val = '';

                    if (e.length>0){
                        val = e.is(':checkbox') ? e.prop( "checked")  : e.val();
                        if(!val) {
                            // checkboxList
                            if(e.is('div')) {
                                var selected = [];
                                $('#'+filters[i]['id'] + ' input:checked').each(function() {
                                    selected.push($(this).attr('value'));
                                });
                                val = selected;
                            }
                        }
                    }

                    url = url.replace('input_' + filters[i]['id'], val)
                }

                <?= $widget->grid_name ?>.clearAll();
                <?= $widget->grid_name ?>.loadNext(<?= $widget->grid_name ?>.config.datafetch, 0, null, url);

                <?= $widget->grid_name ?>_last_filter_url = url;
            }
        );

        //для сворачивания разворачивания форм фильтров
        $('.filter_div_showhide_btn').click(function(){
            $(this).toggleClass('open_filter_form');
            $(this).parent().parent().toggleClass('open_filter_form');

            $("#form_afilter_basic").removeClass('open_filter_form');
            $('.afilter_div_showhide_btn').removeClass('open_filter_form');
        });

    </script>

<?php } ?>

<!-- расширенные фильтры -->
<?php if($widget->afilters){ ?>

    <div id="form_afilter_basic" class="afilter_form_div">
        <div class="afilter_div_showhide">
            <div class="afilter_div_showhide_btn showhide_btn_left"></div>
        </div>


        <?= $widget->afiltersHtml ?>

        <!-- изменение языка -->
        <?php if ($widget->alang_dependency && $widget->alang_selector) { ?>

            <script>
                //изменение языка
                var lang_selector = '<?= $widget->alang_selector['id'] ?>';
                var lang_dependency = <?= json_encode($widget->alang_dependency) ?>;

                $('#'+lang_selector).change(function (){

                    for (var i =0; i< lang_dependency.length; i++){

                        var item = lang_dependency[i];
                        var html_item = $("#"+item['id']);

                        // обновление выпадающего списка
                        if (item['type'] == <?= CommonModel::FILTER_DROPDOWN ?>) {

                            jQuery.getJSON(item['url'], { lang:this.value},(function(this_item) {
                                return function(data) {

                                    this_item.empty();
                                    for (var i = 0; i < data.length; i++)
                                        this_item.append($('<option></option>').val(data[i]['id']).html(data[i]['txt']));

                                };
                            }(html_item)) );
                        }

                        // обновление select2
                        else if (item['type'] == <?= CommonModel::FILTER_SELECT2 ?>) {

                            jQuery.getJSON(item['url'], { lang:this.value},(function(this_item) {
                                return function(data) {

                                    var options = this_item.data('select2').options.options;
                                    this_item.html('');

                                    var items = [];
                                    for (var i = 0; i < data.length; i++){
                                        items.push({ "id": data[i]['id'],"text": data[i]['txt']});
                                        this_item.append($('<option>', { val:data[i]['id'], text: data[i]['txt'] }));
                                    };

                                    options.data = items;
                                    this_item.select2(options);

                                };
                            }(html_item)) );
                        }                       
                        // обновление списков чекбоксов
                        else if (item['type'] == <?= CommonModel::FILTER_CHECKBOXES ?>) {
                            jQuery.getJSON(item['url'], { lang:this.value},(function(this_item) {
                                return function(data) {
                                    this_item.empty();
                                    for (var i = 0; i < data.length; i++)
                                        if(data[i]['id']!=='') // without empty
                                            this_item.append($('<label></label>')
                                                .html('<input type="checkbox" name="af_ew_service_type[]" value="'
                                                    +data[i]['id']+'">&nbsp;'+data[i]['txt']+'&nbsp;'));

                                };
                            }(html_item)) );
                        }
                        
                        // обновление дропдаунов чекбоксов
                                else if (item['type'] == <?= CommonModel::FILTER_CHECKBOXESDROPDOWN ?>) {
                            jQuery.getJSON(item['url'], { lang:this.value},(function(this_item_super) {
                                return function(data) {
                                    var this_item = this_item_super.find('ul');
                                    this_item.empty();
                                            for (var i = 0; i < data.length; i++)
                                                        if(data[i]['id']!=='') // without empty
                                                this_item.append($('<li class="dropdown_label"></li>')
                                                .html(''
                                                    +'<label class="dropdown_label">'
                                                    +'<input onclick="clickMultiSelect(this)" type="checkbox" value="'
                                                    +data[i]['id']+'" id="check_'
                                                    +this_item.attr("class")+'" class="check_'
                                                    +this_item.attr("class")+'" />'
                                                    +data[i]['txt']+'</label>'));

                                        };
                            }(html_item)));                           
                        }
                    }
                });
              </script>
        <?php } ?>

    </div>

    <script>

        //при нажатии на кнопку фильтр
        var afilters = <?= json_encode($widget->afilters)?>;
        $('#form_afilter_basic .afilter_submit_btn').click(
            function(){
                var url = "<?= $widget->afilter_url ?>";
                for (var i =0; i< afilters.length; i++) {

                    var e = $('#'+afilters[i]['id']);
                    var val = '';

                        if (e.length > 0){
                            val = e.is(':checkbox') ? e.prop( "checked")  : e.val();
                            if(!val) {
                                // checkboxList
                                if(e.is('div')) {
                                        var selected = [];
                                            $('#'+afilters[i]['id'] + ' input:checked').each(function() {
                                                selected.push($(this).attr('value'));
                                            });
                                        val = selected;
                                        }
                                    }
                                }
                            var substr = '';
                        if(val){
                            if (typeof val === 'boolean') {
                                // single checkbox
                                val = (val==true) ? '1' : '0';
                                url = url.replace('input_' + afilters[i]['id'], val);
                            }
                            else if(typeof val == 'array' || typeof val === 'object') {
                                if(val[0]==='') val.shift();
                                substr = val.join(',');
                                if(!substr) substr = '';
                                url = url.replace('input_' + afilters[i]['id'], substr.replace('+', '%2B'));
                            } else {
                                url = url.replace('input_' + afilters[i]['id'], val.replace('+', '%2B'));
                            }
                        } else {
                            // empty value
                            url = url.replace('input_' + afilters[i]['id'],'');
                    }
                }

                <?= $widget->grid_name ?>.clearAll();
                <?= $widget->grid_name ?>.loadNext(<?= $widget->grid_name ?>.config.datafetch, 0, null, url);
                <?= $widget->grid_name ?>_last_filter_url = url;
                }
            );

        //для сворачивания разворачивания форм фильтров
        $('.afilter_div_showhide_btn').click(function(){
            $(this).toggleClass('open_filter_form');
            $(this).parent().parent().toggleClass('open_filter_form');

            $("#form_filter_basic").removeClass('open_filter_form');
            $('.filter_div_showhide_btn').removeClass('open_filter_form');
        });

    </script>

<?php } ?>

<!-- верхняя панель -->
<div class="view-option-line">

    <?php if($widget->show_buttons){ ?>

    <?php if($widget->multi_select){ ?>

        <div class="further">
            <div id="<?= $widget->grid_name ?>_multiselect_btn" class="grid-multiselect-btn"></div>
        </div>

    <?php } ?>

        <div class="further">
            <?php
            if ($getParams['par_with_create']==="1")
                echo BtnCreateTab::widget([
                    'atributes'=>['id'=>'create_button', 'par_wid'=>$getParams['par_wid']],
                    'btn_classes'=>'add-btn',
                    'tab_title'=>$widget->create_label,
                    'ifr_url'=>$widget->create_url,
                    'unique_tab_id'=>$widget->create_uniqtabname,
                    'return_data_to'=> $getParams['par_el_id'],
                    'trigger_el_id'=>$getParams['par_trigger_el_id']

                ]);
            else
                echo BtnCreateTab::widget([
                    'atributes'=>['id'=>'create_button'],
                    'btn_classes'=>'add-btn',
                    'tab_title'=>$widget->create_label,
                    'ifr_url'=>$widget->create_url,
                    'unique_tab_id'=>$widget->create_uniqtabname,
                ]);
            ?>
        </div>
    
        <div class="further">
            <div id="<?= $widget->grid_name ?>_exp_toexcel_btn" class="export-xls-btn"></div>
        </div>

        <?php if($widget->show_print_button){ ?>
            <div class="further">
                <div id="exp_print_btn" class="print-grid-btn"></div>
            </div>
        <?php }?>

    <?php } ?>

    <?php if($widget->show_operations){ ?>
        <div class="further">
            <div class="operation-styling" style="float: left; display: table; width: 150px; padding: 4px; font-size: 12px;">

                <?php
                    if (sizeof($widget->operations)>0) {
                        echo $widget->operationsHtml;
                        //echo Html::dropDownList('operations' , null,  $widget->model->operations,array('prompt'=>'...', 'class'=>'operation_selector','style'=>'width: 150px;'));
                    }
                ?>

                <script>
                    $('.operation_selector').click(function(){

                        var operation = $(this).attr('operation')
                        if (!operation)
                            return;

                        var options = <?= json_encode($widget->operations_options) ?>[operation];

                        var item_id = <?= $widget->grid_name ?>.getSelectedId();
                        var item = <?= $widget->grid_name ?>.getItem(item_id);
                        var item_ident = options['name_for_tab_full'] ? 'x' : (item ? item.<?= $widget->item_identificator ?>:false);

                        if (!item_ident)
                            return;


                        // при нажатии на операцию необходимо выполнить указанны js
                        if (options['jsOnClick']){
                            eval(options['jsOnClick']);
                            return;
                        }

                        if (!options['url'])
                            return;

                        var url = options['url']
                                +"&<?= $widget->item_identificator ?>="+item_ident
                                +"&operation="+operation;

                        if (options['no_tab']){
                            $.post( url+"&current_operation=1000",
                            (function(this_item) { return function(data) {

                                var item = <?= $widget->grid_name ?>.getItem(this_item);
                                eval(data);


                                item.$css = item.state == 100 ? "red_row_highlight" : "";
                                <?= $widget->grid_name ?>.refresh(this_item);
                                <?= $widget->grid_name ?>_refresh_operations();

                            };}(item_id)) ,"json" )
                        }
                        else{
                            var operation_name = options['name_for_tab'];
                            var sufix = options['tab_name_sufix'] ? options['tab_name_sufix'] : '';
                            var uniq_tab_name = '<?= $widget->doubleclick_uniqtabname ?>'+item_ident + '_' + sufix;//+"_"+operation;
                            var tab_name = '';

                            if (options['name_for_tab_full'])
                                tab_name = options['name_for_tab_full'];
                            else{
                                tab_name = <?= $widget->doubleclick_special_tabname ?>;
                                tab_name = tab_name.substring(0, tab_name.lastIndexOf(" ")+1)+ operation_name;
                            }


                            window.parent.application_create_new_tab(tab_name,url,uniq_tab_name);
                        }

                    });
                </script>
            </div>
        </div>
    <?php } ?>

    <?php if($widget->mode == GridWidget::MODE_TABLE_LEVELS){ ?>
        <div class="further">
            <?= Html::hiddenInput("parent_ref",'', ["id"=>"parent_ref"]) ?>
            <?= Html::checkbox("",true, ["id"=>"use_hierarchy", "label"=>Yii::t("app","Hierarchy")]) ?>
        </div>
        <script>
            $('#use_hierarchy').change(function(){

                var mode = $('#use_hierarchy').prop( "checked") ? "0" : "2";

                var showhide_fields = <?= json_encode($widget->hierarchy_fields) ?>;
                for (var i =0; i< showhide_fields.length; i++) {
                    if (mode == "2") {
                        <?= $widget->grid_name ?>.showColumn(showhide_fields[i]);
                    }
                    else {
                        <?= $widget->grid_name ?>.hideColumn(showhide_fields[i])
                    }
                }

                var url = "<?= \yii\helpers\Url::toRoute(['get-table','item_mode'=>'input_mode']) ?>"
                    .replace('input_mode', mode);
                <?= $widget->grid_name ?>.clearAll();
                <?= $widget->grid_name ?>.load(url);

            });
        </script>
    <?php } ?>

    <?php if ($widget->mode == GridWidget::MODE_TABLE_EDITABLE && $widget->show_editablebuttons): //отобразить кнопку добавления строки (сверху грида) в режиме редактирования ?>
        <input type="button" class = "grid_addBtn_upper grid_addbtn grid-addbtn btn btn-default"  value="+" onclick="
        <?= $widget->grid_name ?>.add( { '<?= $widget->item_identificator ?>' : <?= $widget->grid_name ?>_auto_id--, } <?= $widget->add_btn_revert ? '' : ',0' ?>)"/>
    <?php endif; ?>

    <?php if ($widget->show_refresh_button): //отобразить кнопку обновления грида ?>
        <input type="button" id = "<?= $widget->grid_name ?>_grid_refresh_button" class = "grid-refreshbtn btn btn-default"
        value="~" url='<?= $widget->refresh_url ?>' onclick="
            <?= $widget->grid_name ?>.clearAll(); <?= $widget->grid_name ?>.loadNext(<?= $widget->grid_name ?>.config.datafetch, 0, null, this.getAttribute('url')); <?= $widget->grid_name ?>.callEvent('onGridRefresh'); <?= $widget->grid_name ?>_data_was_loaded = true;"/>
    <?php endif; ?>

    <?php if($widget->show_pager){ ?>
        <div class="further">
            <div id='pager_<?= $widget->grid_name ?>' style="  display: block;  width: 100px;  overflow: hidden;"></div>

            <?php if($widget->show_pager_advanced){ ?>
                <div id='count_<?= $widget->grid_name ?>' class="gridwidget-count" style=" display: inline;  width: 100px"></div>
                <div id='count_pages_<?= $widget->grid_name ?>' class="gridwidget-count-pages" style=" display: inline;  width: 100px"></div>
                <div id='count_onpage_<?= $widget->grid_name ?>' class="gridwidget-count-onpage" style=" display: inline;  width: 100px"></div>
                <?= Yii::t('app', 'Go to page')?>:
                <input id='goto_page_<?= $widget->grid_name ?>' class="gridwidget-goto-page" style="  display: inline;  width: 100px; height: 20px"/>

                <script>

                    function <?= $widget->grid_name ?>_calc_count_onpage(e){
                        var grid = <?= $widget->grid_name ?>;
                        var items_on_page = grid.getPager().config.size;
                        var items_total = grid .count();
                        var current_page = parseInt(e)+1;
                        var items_on_current_page = (items_total < items_on_page*current_page)? items_total%items_on_page : items_on_page;
                        $('#count_onpage_<?= $widget->grid_name ?>').text("<?= Yii::t("app","Items on page: ")?>"+items_on_current_page +". ");
                    }


                    $('#goto_page_<?= $widget->grid_name ?>').keypress(function(e) {
                        if(e.which == 13) {
                            <?= $widget->grid_name ?>.setPage(this.value-1);
                            <?= $widget->grid_name ?>_calc_count_onpage(this.value-1);
                        }
                    });
                </script>
            <?php }?>

        </div>
    <?php } ?>
</div>

<div id='webix_grid_<?= $widget->grid_name ?>' class="webix_grid_conteiner_w_margin"></div>

<script type="text/javascript" charset="utf-8">

    var  <?= $widget->grid_name ?>_data_was_loaded = "<?= $widget->load_on_start ?>";

    // Расширяет webix.editors, добавляет masked-datetime редактор
    webix.editors['masked-datetime'] = webix.extend({
        render: function() {
            return webix.html.create("div", { "class":"webix_dt_editor" }, '<input class="webix-textfield" id="webix-datemaskfield" type="text" ><input class="webix-textfield webix-datefield" id="webix-datefield" type="text" ><span class="webix-date-trigger" id="webix-date-trigger"></span>');
        }
    }, webix.editors.text);

    // Расширяет webix.editors, добавляет masked_time редактор
    webix.editors.masked_time = webix.extend({
        render:function(){
            return webix.html.create("div", { "class":"webix_dt_editor" },"<input id='masked-time-input' >");
        }
    }, webix.editors.text);

    // временное решение
    webix.i18n.setLocale('<?= Langs::$NamesFull[Yii::$app->language] ?>');
    webix.i18n.parseFormat = "%d.%m.%Y %H:%i:%s";
    webix.Date.startOnMonday = true;
    webix.i18n.setLocale();

    webix.editors.$popup.date = {
        view:"popup", width:250, height:250,
        body:{ view:"calendar", timepicker:true, borderless:true, icons: true, format : "%d.%m.%Y %H:%i:%s"}
    };

    // таблица
    var <?= $widget->grid_name ?> = webix.ui({
        container:"<?= $widget->grid_container ?>",
        id:"ctable_<?= $widget->grid_name ?>",
        view:"<?= $widget->grid_view ?>",
        columns: <?= \yii\helpers\Json::htmlEncode($widget->columns) ?>,
        minHeight:50,
        ready: function() {
            <?php echo $widget->ready;?>
        },
        scheme:{
            $change:function(item){

                // раскрашивание строки произвольным цветом
                if (item['row_color']){
                    item.$css = { 'background-color': item['row_color'] + ' !important'};
                }

                // удаленные записи, пока для всех сущностей другим цветом
                if (item.state == 100)
                    item.$css = "red_row_highlight";

                if (item.itemmode == 1)
                    item.$css = "move_to_level_item";
            },
        },
        on:{
            "data->onStoreUpdated":function(){
                this.data.each(function(obj, i){
                    if (obj)
                        obj.npp = i+1;
                })
            },
            <?php if ($widget->mode != GridWidget::MODE_TABLE_EDITABLE): ?>
                onBeforeLoad:function(){
                    this.showOverlay("<div class='upload_div_for_datagrid'><div class='upload_text_for_datagrid'><?=\Yii::t('app','Data loading');?></div><div class='upload_img_for_datagrid'></div></div>");
                },
                "data->onParse":function(driver, data){
                    <?php if ($widget->show_checkboxes)  { ?>

                        // обработка главного чекбокса
                        var values = typeof data == 'object' ? data['data'] : data;
                        if (values)
                        values.forEach(function (row) {
                            row['check'] = <?= $widget->grid_name ?>_master_check;
                        });

                    <?php } ?>
                },
                onAfterLoad:function(){
                    
                    // onAfterLoad
                    if (!this.count())
                        this.showOverlay("<?=\Yii::t('app','No data by this criteria');?>")
                    else this.hideOverlay();

                    <?php if ($widget->show_pager_advanced) { ?>
                        var items_on_page = this.getPager().config.size;
                        var items_total = this.count();
                        var current_page = this.getPage()+1;
                        var items_on_current_page = (items_total < items_on_page*current_page)? items_total%items_on_page : items_on_page;
                        //var total_pages = items_total ? parseInt(items_total/items_on_page)+1 : items_total;
                        var total_pages = parseInt(items_total/items_on_page)+1 ;

                        $('#count_<?= $widget->grid_name ?>').text("<?= Yii::t("app","Found items").": "?>"+items_total+". ");
                        $('#count_pages_<?= $widget->grid_name ?>').text("<?= Yii::t("app","Pages").": "?>"+total_pages+". ");
                        $('#count_onpage_<?= $widget->grid_name ?>').text("<?= Yii::t("app","Items on page").": "?>"+items_on_current_page +". ");
                    <?php } ?>
                }
            <?php endif ?>
        },
<?php if($widget->show_pager){ ?>
        pager:{
<?php if($widget->show_pager_advanced){ ?>
            template:" {common.first()} {common.prev()} {common.pages()} {common.next()} {common.last()}",
            on: {
                onItemClick: function(e, id){

                    <?= $widget->grid_name?>_calc_count_onpage(e);
                }
            },
<?php } ?>
            container:"<?= $widget->pager_container ?>",// the container where the pager controls will be placed into
            size:<?= $widget->pager_size ?>, // the number of records per a page
            group:<?= $widget->pager_group ?>,   // the number of pages in the pager

        },
<?php } ?>
        data: <?= json_encode($widget->data) ?>, // данные для отображения в таблице
        <?= rtrim(ltrim (json_encode($widget->grid_options),'{'),'}') ?>
    });

    <?php if ($widget->filter_onload):?>
        $('#form_filter_basic .filter_submit_btn').click();
    <?php else:?>
        <?= $widget->grid_name ?>.config.url = "<?= $widget->url ?>";
        if ("<?= $widget->url && $widget->load_on_start ?>" ) {
            <?= $widget->grid_name ?>.loadNext(<?= $widget->grid_name ?>.config.datafetch, 0, null, "<?= $widget->url ?>");
        }
    <?php endif?>

    <?= $widget->grid_name ?>.attachEvent('onAfterEditStart', function(id) {
        var grid = this;
        var itemId = id.row;
        var item = grid.getItem(itemId);
        
        // masked_time editor
        var masked_editor = $('#masked-time-input');
        if (masked_editor.length){
            masked_editor.inputmask("h:s",{ "placeholder": "hh/mm" });
            masked_editor[0].setSelectionRange(0,0);
        }
        
        /**
         * masked-datetime editor
         * Состоит из двух полей: mask field, datetimepicker
         * В datetimepicker нельзя встроить следующий функционал: при двойном клике дать возможность редактировать поле вручную,
         * при клике на иконку календаря дать открывать datetimepicker, поэтому реализовано через 2 поля, одно для ручного редактирования,
         * второе - для выбора даты
         */
        var dt_masked_field = $('#webix-datemaskfield'); // datetimepicker, скрыт под mask field-ом
        var dt_picker_field = $('#webix-datefield'); // mask field
        var dt_picker_field_trigger = $('#webix-date-trigger'); // иконка с календарем
        if (dt_masked_field.length > 0 && dt_picker_field.length > 0 && dt_picker_field_trigger.length > 0) {
            dt_masked_field.inputmask({
                'mask': "d.m.y h:s:s",
                'insertMode': false, // возможность вносить изменения (при false - чтоб изменить дату, нужно полностью очистить поле)
                "oncomplete": function() {
                    //checkInputmaskDate проверка даты на выход за ограничения с созвратом корректированной или внесенной
                    //swapValues если дата элемента отличается от даты в параметре то подменяет дату с сохранением курсора в элементе
                    swapValues($(this),filterInputmaskDate($(this).val()));
                    var newVal = $(this).val();
                    item['_date'] = newVal;
                    dt_picker_field.val(newVal);
                }
            }); // d.m.Y H:i:s
            dt_picker_field.datetimepicker({
                format:'d.m.Y H:i:s',
                maxDate:'2038/01/19',
                onChangeDateTime: function(dp, $input) {
                    var newVal = $input.val();
                    item['_date'] = newVal;
                    dt_masked_field.val(newVal);
                }
            });
            dt_picker_field_trigger.on('click', function() { // при клике на иконку - открывать datetimepicker
                dt_picker_field.datetimepicker('show'); //support hide,show and destroy command
            });
        }
    });

    <?= $widget->grid_name ?>.attachEvent('onAfterSelect',function(item_id) {
        <?= $widget->grid_name ?>_refresh_operations();
    });

    // экспорт в эксель
    $('#<?= $widget->grid_name ?>_exp_toexcel_btn').click(function(){
        <?php if ($widget->pager_load_by_page) { ?>
            <?=$widget->grid_name;?>.loadNext(<?=$widget->grid_name;?>.count(),0, function(){
        <?php } ?>
            // Если 2 грида на странице, то вешаем обработчик события на главный грид
            <?php if($widget->oneGrid) { ?>

            var dataGrid = JSON.stringify(<?=$widget->grid_name;?>.data.pull);
            var ids = [];
            $.each(<?=$widget->grid_name;?>.fj, function(index, value) {
                ids.push(value.id);
            });
            var headers = [];
            var gridName = "<?=$widget->grid_name;?>";
            $.each($('#webix_grid_'+ gridName +' .webix_hcell'), function(index, val) {
                headers.push($(val).html());
            });
            $('#input-grid-data').val(dataGrid);
            $('#headers-input').val(headers.join(';'));
            $('#ids-input').val(ids.toString());
            $('#form-grid-data').submit();

            <?php }?>
        <?php if ($widget->pager_load_by_page) { ?>
            });
        <?php } ?>
    });

    function <?= $widget->grid_name ?>_refresh_operations() {

        var item = <?= $widget->grid_name ?>.getItem(<?= $widget->grid_name ?>.getSelectedId());
        var item_state = parseInt(item.state);
        var options = <?= json_encode($widget->operations_options) ?>;

        for (var key in options) {

            if (options[key]['state_depend']) {

                if (!item || $.inArray(item_state, options[key]['state_depend'])==-1)
                    $('li[operation="' + key + '"]').hide();
                else
                    $('li[operation="' + key + '"]').show();
            }
        }
    }

    /* контекстное меню
    webix.ui({
        view:"contextmenu",
        id:"cmenu",
        data:["Item 1","Item 2","Item 3"]
    }).attachTo(<?= $widget->grid_name ?>);
    */

    // Добавить контекстное меню для грида
    var gridMenuOptions = {
        // переводы
        messages: {
            scretchToFill: '<?= Yii::t('app', 'Stretch to fill'); ?>',
            scretchToArea: '<?= Yii::t('app', 'Stretch to area'); ?>'
        }
    };
    gridController.addMenu(
        <?= $widget->grid_name ?>, // grid object
        'ctable_<?= $widget->grid_name ?>', // grid ID
        <?= json_encode($widget->menu_columns) ?>, // grid columns
        gridMenuOptions // Дополнительные параметры
    );

<?php if($widget->doubleclick_generate){ ?>

    // двойное нажатие, открытие
    <?php if ($widget->mode == GridWidget::MODE_TABLE)  { ?>
    <?= $widget->grid_name ?>.attachEvent("onItemDblClick", function(id, e, node){

        var item = this.getItem(id);


        <?php
            if (isset($getParams['par_el_id'])&&$getParams['par_el_id']!='false' && !$widget->multi_select):
        ?>
        // если передан код родительского элемента то вернуть значение родительскому табу
        return parent.return_data_to_parent_tab('<?=$getParams['par_wid']?>','<?=$getParams['par_el_id']?>',item.<?= $widget->doubleclick_returnfield ?>,'<?=$getParams['this_tid']?>','<?=$getParams['par_trigger_el_id']?>');
        <?php endif; ?>
        var url = "<?= $widget->doubleclick_url ?>&<?= $widget->item_identificator ?>="+item.<?= $widget->item_identificator ?>;


        <?php IF ($widget->doubleclick_special_tabname==''):?>
        var tab_name = "<?= $widget->doubleclick_tabname ?>"+" "+item.<?= $widget->doubleclick_tabname_itemfield ?>;
        <?php ELSE:?>
        var tab_name = <?= $widget->doubleclick_special_tabname ?>;
        <?php ENDIF?>

        window.parent.application_create_new_tab(tab_name,url,'<?= $widget->doubleclick_uniqtabname ?>'+item.<?= $widget->item_identificator ?>+'_');

    });
    <?php } else { ?>
    // двойное нажатие, открытие
    <?= $widget->grid_name ?>.attachEvent("onItemDblClick", function(id, e, node){

        var use_hierarchy = $('#use_hierarchy').prop( "checked");
        var item = this.getItem(id);

        // для создания закладки
        if (item.level == <?= $widget->model->maxLevel ?> || !use_hierarchy) {

            <?php
                $getParams = Yii::$app->getRequest()->get();
                if (isset($getParams['par_el_id'])&&$getParams['par_el_id']!='false'):
            ?>
            // если передан код родительского элемента то вернуть значение родительскому табу
                return window.parent.return_data_to_parent_tab('<?=$getParams['par_wid']?>','<?=$getParams['par_el_id']?>',item.<?= $widget->doubleclick_returnfield ?>,'<?=$getParams['this_tid']?>','<?=$getParams['par_trigger_el_id']?>');
            <?php endif; ?>

            var url = "<?= $widget->doubleclick_url ?>&<?= $widget->item_identificator ?>="+item.<?= $widget->item_identificator ?>;

            <?php IF ($widget->doubleclick_special_tabname==''):?>
            var tab_name = "<?= $widget->doubleclick_tabname ?>"+" "+item.<?= $widget->doubleclick_tabname_itemfield ?>;
            <?php ELSE:?>
            var tab_name = <?= $widget->doubleclick_special_tabname ?>;
            <?php ENDIF?>

            window.parent.application_create_new_tab(tab_name,url,'<?= $widget->doubleclick_uniqtabname ?>'+item.<?= $widget->item_identificator ?>);
            return;
        }

        // для навигации
        var url = "<?= \yii\helpers\Url::toRoute(['get-table','parent_ref'=>'input_id','item_mode'=>'input_mode']) ?>"
            .replace('input_id', item.<?= $widget->item_identificator ?>)
            .replace('input_mode', item.itemmode);
        this.clearAll();
        this.load(url);

        $('#parent_ref').val(item.<?= $widget->item_identificator ?>);

        var _href = $('#create_button').attr("ifr_url");
        var parent = item.itemmode == 1 ? item.parent_ref : item.<?= $widget->item_identificator ?>;
        $('#create_button').attr("ifr_url", _href.replace(/parent_ref=[^&]+/, 'parent_ref='+parent));;
    });
    <?php } ?>

<?php } ?>

// Режим редактирования
<?php if ($widget->mode == GridWidget::MODE_TABLE_EDITABLE): ?>

    //удаление строки
    <?= $widget->grid_name ?>.on_click.grid_delbtn=function(e, id, trg){
        <?= $widget->grid_name ?>.remove(id.row);
        return false;
    };

    //добавление строки
    var <?= $widget->grid_name ?>_auto_id = <?= $widget->auto_id_val ?>;
    <?= $widget->grid_name ?>.on_click.grid_addbtn=function(e, id, trg){
        <?= $widget->grid_name ?>.add(
            { '<?= $widget->item_identificator ?>' : <?= $widget->grid_name ?>_auto_id--},
            this.getIndexById(id.row)+1
        );
        
        return false;
    };
    
    // отправка данных с webix DataStorage на сервер
    $("form:first").submit(function() {

        var form = document.forms[0];
        var entity_name = '<?= $widget->model_parent_field?>';
        var name_format = '<?= $widget->post_name_format?>';

        // места
        var container = document.getElementById('post_'+entity_name);
        if (container == null) {
            container = document.createElement('div');
            container.id = 'post_'+entity_name;
            container.style.display = "none";
            form.appendChild(container);
        }
        else
            container.innerHTML = '';


        <?= $widget->grid_name ?>.clearSelection();
        //<?= $widget->grid_name ?>.filter();

        if (!<?= $widget->grid_name ?>_data_was_loaded){

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name_format.replace(':index' , 0).replace(':name','grid_state');
            input.value = '<?= CommonModel::FIELD_WAS_NOT_LOADED ?>';
            container.appendChild(input);
        }

        var <?= $widget->grid_name ?>_row_index = 1;

        <?= $widget->grid_name ?>.eachRow(

            function (row){

                var item = <?= $widget->grid_name ?>.getItem(row);

                // all fields
                var columns = <?= json_encode($widget->post_model_fields) ?>;

                // если строка пустая, пропустить ее
                var empty = true;
                for (var j = 0; j < columns.length; j++) {
                    //if ((columns[j] == 'id' && item[columns[j]] > 0) || (columns[j] != 'id' && item[columns[j]] )) {
                    if (columns[j] == 'id')
                        continue;
                    if (item[columns[j]] ) {
                        empty = false;
                        break;
                    }
                }
                if (empty)
                    return;

                for (var i =0; i< columns.length; i++) {

                    var column = columns[i];

                    if (item[column] === undefined)
                        continue;

                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name_format.replace(':index' , <?= $widget->grid_name ?>_row_index).replace(':name',column);
                    input.value = item[column];
                    container.appendChild(input);
                }

                <?= $widget->grid_name ?>_row_index++;
            }
        ,true);
    });

    /* Это пользовательский редактор. надо из него попробовать сделать такой же как CommonDateTimePicker
    webix.editors.datetime = {
        focus:function(){
            this.getInputNode(this.node).focus();
            this.getInputNode(this.node).select();
        },
        getValue:function(){
            return this.getInputNode(this.node).value;
        },
        setValue:function(value){
            this.getInputNode(this.node).value = value;
        },
        getInputNode:function(){
            return this.node.firstChild;
        },
        render:function(){
            return webix.html.create("div", {
                "class":"webix_dt_editor"
            }, "<input type='text'>");
        }
    }*/

<?php endif; ?>

<?php if ($widget->show_checkboxes)  { ?>

    // обработка главного чекбокса
    var  <?= $widget->grid_name ?>_master_check = false;
    function <?= $widget->grid_name ?>_master_checkbox_click(checkbox){

        <?= $widget->grid_name ?>_master_check = checkbox.checked;
        var header = <?= $widget->grid_name ?>.getColumnConfig('check')['header'][0]['text'];
        if (header)
            <?= $widget->grid_name ?>.getColumnConfig('check')['header'][0]['text'] = $(header).attr('checked',checkbox.checked).prop('outerHTML');

        <?= $widget->grid_name ?>.eachRow(function (row){

            var item = <?= $widget->grid_name ?>.getItem(row);
            if (item) {
                item['check'] = checkbox.checked;
            }
        });

        <?= $widget->grid_name ?>.refresh();
    }

    function <?= $widget->grid_name ?>_get_checked_items(){

        var checked = [];
        var unchecked = [];

        <?= $widget->grid_name ?>.eachRow(function (row){

            var item = <?= $widget->grid_name ?>.getItem(row);
            if (item) {
                if (item['check'])
                    checked.push(item['<?= $widget->item_identificator ?>'])
                else
                    unchecked.push(item['<?= $widget->item_identificator ?>'])
            }
        });

        return {
            item_identificator:'<?= $widget->item_identificator ?>',
            filter_url:<?= $widget->grid_name ?>_last_filter_url,
            master: <?= $widget->grid_name ?>_master_check,
            checked:checked,
            unchecked:unchecked,
            // ничего не выбрано если 1. установили мастер чекбокс и сняли все галочки 2. не устанавливали мастер чекбокс и ничего не выбрали
            not_selected:
                <?= $widget->grid_name ?>_master_check
                    ? unchecked.length == <?= $widget->grid_name ?>.count()
                    : checked.length == 0
        }
    }

    <?php } ?>
    
    <?= $widget->grid_name ?>.attachEvent('onAfterLoad', function() {
        var grid = this;
        grid.eachRow(function (row) {
            item = grid.getItem(row);
            
            <?php foreach($widget->select_entity_urls as $column => $selectEntityUrl): ?>
                // добавить SelectEntityWidget
                var selectEntityUrl = '<?= $selectEntityUrl; ?>';
                var column = '<?= $column; ?>';
                var data = {
                    id: item['status_country'],
                };
                if (item['uniq_id']) {
                    data.uniqId = item['uniq_id'];
                }
                <?php if ($widget->model_parent): ?>
                    data.operation = '<?= $widget->model_parent->getOperation(); ?>';
                <?php endif; ?>
                $.get(selectEntityUrl, data, function(data) {
                    var widgetHtml = $(data);
                    var body = $('body');
                    var uniqId = null;
                    widgetHtml.each(function(key, data) {
                        if (key == 1) {
                            item[column] = $(data)[0].outerHTML;
                            uniqId = $(data).find('.entity-uniq-id').val();
                        } else {
                            body.append(data);
                        }
                    });

                    if (uniqId) {
                        grid.attachEvent('onAfterRender', function() {
                            eval("select_entity_"+uniqId+"_generate_view()");
                        });
                    }
                });
            <?php endforeach; ?>
            
        });
    });
    
    <?= $widget->grid_name ?>.attachEvent('onGridRefresh', function() {
        // grid refresh event
    });

    $("#<?= $widget->grid_name ?>_multiselect_btn").click(function(){

        return parent.return_data_to_parent_tab('<?=$getParams['par_wid']?>','<?=$getParams['par_el_id']?>',JSON.stringify(<?= $widget->grid_name ?>_get_checked_items()),'<?=$getParams['this_tid']?>','<?=$getParams['par_trigger_el_id']?>');
    });

</script>
<?php
    $selecEntityEvens = '';
    if (!empty($widget->data_options['select_entity_names'])) {
        foreach($widget->data_options['select_entity_names'] as $selectEntityWidgetName) {
//            $selecEntityEvens .= 'webix.message("'.$selectEntityWidgetName.'_generate_view()");'; // для debug-а
            $selecEntityEvens .= $selectEntityWidgetName . '_generate_view();';
        }
        $js = new \yii\web\JsExpression("
        ".$widget->grid_name.".attachEvent('onAfterRender', function() {
               ".$selecEntityEvens."
            });
        ");
//        $this->registerJs($js);
    }
?>
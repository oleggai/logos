<?php
use app\assets\SelectCounterpartyAsset;
use app\models\counterparty\Counterparty;
use app\models\counterparty\ListPersDocType;
use app\widgets\GridWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

/* @var $this yii\web\View*/
/* @var $widget app\widgets\SelectCounterparty*/

SelectCounterpartyAsset::register($this);

$model = new Counterparty();
$filters = $model->getAFilters(false);
$filter_url = Url::to(['counterparty/counterparty/get-a-table','type'=>$widget->counterparty_type]);
foreach ($filters as $filter)
    $filter_url .= "&{$filter['id']}=input_{$filter['id']}";
?>
<style>
    .modal-wide .modal-dialog {
        width: 800px;
    }
</style>

<!-- Modal -->
<div class="modal fade modal-wide" id="select_counterparty" tabindex="-1" role="dialog" aria-labelledby="select_counterparty_label">
    <div class="modal-dialog large" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= Yii::t('app', 'Close')?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="select_counterparty_label"><?= Yii::t('counterparty', 'Select counterparty')?></h4>
            </div>
            <div class="modal-body">
                <div class="select-counterparty-grid-filters" id="select_counterparty_grid_filters">

                    <div class="basis-flex">

                        <div class="select-counterparty-filters-label"><?=Yii::t('counterparty','Phone number')?></div>
                        <?= Html::textInput("af_phone_number",null,['id'=>'af_phone_number','class'=>'form-control select-counterparty-filters-input'])?>

                        <div class="select-counterparty-filters-label"><?=Yii::t('counterparty','IDN/EGRPOU code')?></div>
                        <?= Html::textInput("af_itn",null,['id'=>'af_itn','class'=>'form-control select-counterparty-filters-input'])?>

                        <div class="select-counterparty-filters-label"><?=Yii::t('counterparty','Tax number')?></div>
                        <?=Html::textInput("af_taxnumber",null,['id'=>'af_taxnumber','class'=>'form-control select-counterparty-filters-input'])?>

                    </div>

                    <div class="basis-flex">

                        <div class="select-counterparty-filters-label"><?=Yii::t('counterparty','Document type')?></div>
                        <?= Html::dropDownList("af_doc_type",'',ListPersDocType::getList(true),['id'=>'af_doc_type','class'=>'form-control select-counterparty-filters-input'])?>

                        <div class="select-counterparty-filters-label"><?=Yii::t('counterparty','Serial number')?></div>
                        <?= MaskedInput::widget([
                                'mask' => 'ff',
                                'name'=>'af_doc_serial',
                                'options' => ['id'=>'af_doc_serial','class'=>'form-control select-counterparty-filters-input'],
                                'definitions' => ['f' => [
                                    'validator' => '[АБВГҐДЕЄЖЗИІЇЙКЛМНОПРСТУФХЦЧШЩЬЮЯабвгґдеєжзиіїйклмнопрстуфхцчшщьюя]',
                                    'cardinality' => '1',
                                    'casing'=>'upper',
                                ]]
                        ]); ?>

                        <div class="select-counterparty-filters-label"><?=Yii::t('counterparty','Number')?></div>
                        <?= MaskedInput::widget([
                            'name'=>'af_doc_number',
                            'mask' => '999999',
                            'options'=>['id'=>'af_doc_number','class'=>'form-control select-counterparty-filters-input'],
                        ]) ?>

                    </div>

                </div>

                <input id="btn_find_modal" class="btn btn-default btn-xs" type="button" value="<?= Yii::t('app','Find')?>">
                <input id="btn_clear_modal" class="btn btn-default btn-xs" type="button" value="<?= Yii::t('app','Clear')?>">

                <div  class="select-counterparty-grid-pager" id="select_counterparty_grid_pager"  style="  display: block;  width: 300px !important;  overflow: hidden;"></div>

                <div class="select-counterparty-grid-grid" id="select_counterparty_grid_grid"></div>
            </div>
        </div>
    </div>
</div>



<?=
GridWidget::widget([
    'model' => new Counterparty(),
    'grid_name' => 'find_counterparty_modal',
    'columns'=> [
        [ 'id'=>'counterparty_id'],
        [ 'id'=>'person_type'],
        [ 'id'=>'counterparty_name', 'adjust'=>'data'],
        [ 'id'=>'code'],
        [ 'id'=>'counterparty_primary_pers', 'adjust'=>'data','header'=>Yii::t('counterparty','Counterparty Primary Pers')],
        [ 'id'=>'counterparty_primary_adress', 'adjust'=>'data','header'=>Yii::t('counterparty','Primary Address')],
        [ 'id'=>'counterparty_primary_phone','header'=>Yii::t('counterparty','Primary Phone')],
    ],
    'pager_size'=>10,
    'grid_container'=>'select_counterparty_grid_grid',
    'pager_container'=>'select_counterparty_grid_pager',
    'url' => Url::to(['counterparty/counterparty/get-table','type'=>0]),
    'show_operations' => false,
    'show_buttons' => false,
    'load_on_start' =>false,
    'show_id' => false,
    'doubleclick_generate'=>false,
    'grid_options'=>['width'=>765],
]);
?>




<script>


    var filters = <?= json_encode($filters)?>;
    var return_data_element = null;
    var select_counterparty_modal = $('#select_counterparty');

    /**
     * Отобразить модальное окно с поиском КА
     * @param element_return_data_to Елемент для возврата значения
     * @param element_click_on_cancel Елемент для "клика" при отмене поиска в диалоговом окне
     */
    function create_counterparty_find_modal(element_return_data_to, element_click_on_cancel ){

        $('#af_phone_number').val('').trigger('input');
        $('#af_itn').val('').trigger('input');
        $('#af_taxnumber').val('').trigger('input');
        $('#af_doc_type').val(0).change();
        $('#af_doc_serial').val('').trigger('input');
        $('#af_doc_number').val('').trigger('input');
        find_counterparty_modal.clearAll();


        return_data_element = $('#'+element_return_data_to);
        select_counterparty_modal.modal('show');

        if (element_click_on_cancel){
            $('#select_counterparty').off('hidden.bs.modal');
            $('#select_counterparty').on('hidden.bs.modal', function (e) {
                $('#'+element_click_on_cancel).click();
            })
        }
    }

    /**
     * Поиск КА
     */
    $('#btn_find_modal').click(
        function(){
            var url = "<?= $filter_url ?>";

            for (var i =0; i< filters.length; i++) {

                var e = $('#'+filters[i]['id']);
                var val = '';

                if (e.length>0){
                    val = e.is(':checkbox') ? e.prop( "checked")  : e.val();
                    if (val == null)
                        val = '';
                }

                url = url.replace('input_' + filters[i]['id'], val)
            }

            find_counterparty_modal.clearAll();
            find_counterparty_modal.load(url);
        }
    );

    /**
     * Закрытие модального окна
     */
    $('#close_counterparty_modal').click( function(){
        webix.modalbox.hide(counterparty_find_modal);
    });

    /**
     * Выбор Ка из результатов поиска
     */
    find_counterparty_modal.attachEvent("onItemDblClick", function(id, e, node) {

        var item = this.getItem(id);
        return_data_element.val(item.id).change();
        select_counterparty_modal.off('hidden.bs.modal');
        select_counterparty_modal.modal('hide');
    });

    /**
     * Смена типа документа. Для паспорта Украины используются маски
     */
    $("#af_doc_type").change(function() {
        if(this.value==1){
            $('#af_doc_serial').inputmask({mask:"ff"});
            $('#af_doc_number').inputmask("999999");
        }
        else
        {
            $('#af_doc_serial').off();
            $('#af_doc_number').off();
        }

        $('#af_doc_serial').change(function(){setFindBtnEnabled()});
        $('#af_doc_number').change(function(){setFindBtnEnabled()});

        setFindBtnEnabled();
    });
    $('#af_phone_number').change(function(){setFindBtnEnabled()});
    $('#af_itn').change(function(){setFindBtnEnabled()});
    $('#af_taxnumber').change(function(){setFindBtnEnabled()});



    function setFindBtnEnabled() {
        var phone = $('#af_phone_number');
        var itn = $('#af_itn');
        var tax = $('#af_taxnumber');
        var type = $('#af_doc_type');
        var serial = $('#af_doc_serial');
        var num = $('#af_doc_number');
        var btn = $('#btn_find_modal');

        if (phone.val() || itn.val() || tax.val()) {
            btn.prop('disabled', false);
            return;
        }

        if (type.val()==1 && (!serial.val() || !num.val())) {
            $('#btn_find_modal').prop('disabled', 'disabled');
            return;
        }

        if (type.val()>0  && !num.val()) {
            $('#btn_find_modal').prop('disabled', 'disabled');
            return;
        }

        btn.prop('disabled', false);
    }


    $(window).load(function() {

        // debug
        //create_counterparty_find_modal('test');
    });

</script>



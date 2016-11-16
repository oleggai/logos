<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\web\View;
use app\assets\SelectEntityAsset;

/* @var $widget app\widgets\SelectEntityWidget*/

SelectEntityAsset::register($this);

?>

<div class="select_entity_button" id="<?= $widget->name ?>">
    
    <?= Html::hiddenInput(null, $widget->model ? $widget->model->getPrimaryKey() : null, [
        'class' => 'model-index',
    ]); ?>

    <?php if ($widget->show_select) { ?>
        <div id="<?= $widget->name ?>_div_select" style="display:inline-block;" >

            <?= Html::textInput(null, null, ['hidden'=>true,'id'=>$widget->name.'_input', 'style'=>'display: none !important']); ?>
            <?= Html::button('', ['hidden'=>true,'id'=> $widget->name.'_btn', 'style'=>'display: none !important']); ?>

            <?php
                echo app\widgets\BtnCreateTab::widget([
                    'atributes'=>['id'=>$widget->name.'_btn_select', 'with_creation'=>$widget->with_creation],
                    'btn_classes'=>'btn btn_findt btn_select_entity ' . ($widget->select_before_click?'hidden':''),
                    'btn_title'=>'',
                    'tab_title'=>$widget->select_tab_title,
                    'ifr_url'=>$widget->select_url,
                    'unique_tab_id'=>$widget->select_tab_uniqname,
                    'return_data_to'=>$widget->name.'_input',
                    'trigger_el_id'=>$widget->name.'_btn'
                ]);

                if ($widget->select_before_click) {
                    echo "<div class='btn btn_findt btn_select_entity' id='{$widget->name}_pseudo_btn_select'></div>";
                }
            ?>

        </div>
    <?php } ?>

    <?php if ($widget->show_view) { ?>
        <div id="<?= $widget->name ?>_div_view" style="display:inline-block;" <?= !$widget->show_view ? 'hidden':''?> >

            <?= app\widgets\BtnCreateTab::widget([
                'atributes'=>['id'=>$widget->name.'_btn_view'],
                'btn_classes'=>'btn btn_findt btn_view_entity',
                'btn_title'=>'',
                'tab_title'=>$widget->view_tab_title,
                'ifr_url'=>$widget->view_url,
                'unique_tab_id'=>$widget->view_tab_uniqname,
            ]); ?>

        </div>
    <?php } ?>

</div>

<?php
$js = new \yii\web\JsExpression("
    $('".$widget->parent_selector."').on('click', '#".$widget->name."_btn', function() {
        var input = $('#".$widget->name."_input');
        var output = $('#".$widget->linked_field."');
//        alert('#".$widget->linked_field." change');

        output.val(input.val()).change();
    });

    // формирование урл просмотра
    function ".$widget->name."_generate_view () {
//        console.log('".$widget->name."_generate_view');

        var view_btn = $('#".$widget->name."_btn_view');
        var linked_field = $('#".$widget->linked_field."');

        var view_url = '".$widget->view_url."';
        var view_title = '".$widget->view_tab_title."';
        var unique_tab_name = '".$widget->view_tab_uniqname."';

        var ifr_url = view_url+'&id='+linked_field.val();
        view_btn.attr('ifr_url', ifr_url);
        view_btn.attr('tab_title', view_title.replace('{0}',linked_field.val()));
        view_btn.attr('unique_tab_id', unique_tab_name.replace('{0}',linked_field.val()));

        if (linked_field.val())
            view_btn.removeAttr('disabled');
        else
            view_btn.attr('disabled', 'disabled');
    }

    // формирование урл выбора
    function ".$widget->name."_generate_select () {
//        console.log('".$widget->name."_generate_select');

        if (!'".$widget->parent_field."')
            return;

        var select_btn = $('#".$widget->name."_btn_select');
        var parent_field = $('#".$widget->parent_field['id']."');
        var parent_field_in_url = '".$widget->parent_field['name']."';

        var select_url = '".$widget->select_url."';
        //var view_title = '".$widget->select_tab_title."';
        //var unique_tab_name = '".$widget->select_tab_uniqname."';

        select_btn.attr('ifr_url', select_url+'&'+parent_field_in_url+'='+parent_field.val());
        //select_btn.attr('tab_title', view_title.replace('{0}',linked_field.val()));
        //select_btn.attr('unique_tab_id', unique_tab_name.replace('{0}',linked_field.val()));

        if (parent_field.val())
            select_btn.removeAttr('disabled');
        else
            select_btn.attr('disabled', 'disabled');
    }

    ".$widget->name."_generate_view();
    $('#".$widget->linked_field."').change(function(){
        ".$widget->name."_generate_view();
    });

    ".$widget->name."_generate_select ();
    $('#".$widget->parent_field['id']."').change(function(){
        ".$widget->name."_generate_select ();
    });

    $('#".$widget->name."_pseudo_btn_select').click(function(){
        eval(\"".$widget->select_before_click."\");
    });
");
$this->registerJs($js);
?>

<?php if (false): ?>
<!--<script>

    $('#<?= $widget->name ?>_btn').click(function(){

        var input = $('#<?= $widget->name ?>_input');
        var output = $('#<?= $widget->linked_field?>');

        output.val(input.val()).change();

        eval("<?= $widget->select_after_select?>");
    });

    // формирование урл просмотра
    function <?= $widget->name ?>_generate_view (){

        var view_btn = $('#<?=$widget->name?>_btn_view');
        var linked_field = $('#<?= $widget->linked_field?>');

        var view_url = '<?= $widget->view_url ?>';
        var view_title = '<?= $widget->view_tab_title ?>';
        var unique_tab_name = '<?= $widget->view_tab_uniqname ?>';
//        console.log('start');
//        console.log('<?= $widget->name ?>');
//        console.log(unique_tab_name);

        view_btn.attr('ifr_url', view_url+"&id="+linked_field.val());
        view_btn.attr('tab_title', view_title.replace('{0}',linked_field.val()));
        view_btn.attr('unique_tab_id', unique_tab_name.replace('{0}',linked_field.val()));
//        console.log(unique_tab_name.replace('{0}',linked_field.val()));
//        console.log('end');

        if (linked_field.val())
            view_btn.removeAttr("disabled");
        else
            view_btn.attr("disabled", "disabled");
    }

    // формирование урл выбора
    function <?= $widget->name ?>_generate_select (){

        if (!'<?= $widget->parent_field?>')
            return;

        var select_btn = $('#<?=$widget->name?>_btn_select');
        var parent_field = $('#<?= $widget->parent_field['id']?>');
        var parent_field_in_url = '<?= $widget->parent_field['name']?>';

        var select_url = '<?= $widget->select_url ?>';
        //var view_title = '<?= $widget->select_tab_title ?>';
        //var unique_tab_name = '<?= $widget->select_tab_uniqname ?>';

        select_btn.attr('ifr_url', select_url+"&"+parent_field_in_url+"="+parent_field.val());
        //select_btn.attr('tab_title', view_title.replace('{0}',linked_field.val()));
        //select_btn.attr('unique_tab_id', unique_tab_name.replace('{0}',linked_field.val()));

        if (parent_field.val())
            select_btn.removeAttr("disabled");
        else
            select_btn.attr("disabled", "disabled");
    }





    $(document).ready(function(){
        console.log('<?= $widget->name ?>');
        <?= $widget->name ?>_generate_view();
        $('#<?= $widget->linked_field?>').change(function(){
            <?= $widget->name ?>_generate_view();
        });


        <?= $widget->name ?>_generate_select ();
        $('#<?= $widget->parent_field['id']?>').change(function(){
            <?= $widget->name ?>_generate_select ();
        });

        $('#<?= "{$widget->name}_pseudo_btn_select"?>').click(function(){
            eval("<?= $widget->select_before_click?>");
        });
    });

</script>-->
<?php endif;

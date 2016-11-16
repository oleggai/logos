<?php

/* @var $widget app\widgets\EntityControl*/

use yii\helpers\Html;
use app\assets\AppAsset;
use app\models\common\CommonModel;
use app\models\ew\ExpressWaybill;
use demogorgorn\ajax\AjaxSubmitButton;

AppAsset::register($this);

?>

<li>
    <?php
    if (!$widget->model->isNewRecord && $widget->model->operations && sizeof($widget->model->operations) > 0) {
        echo $widget->form->field($widget->model, 'operation')->dropDownList($widget->model->operations,
            ['prompt' => '...', 'id' => 'operation_selector', 'class' => 'operation_selector', 'readonly' => false, 'disabled' => false]);
    }
    ?>
</li>
<li>
    <?php if ($widget->model->isNewRecord) { ?>
        <?= Html::submitButton(Yii::t('app', 'Create'), ['class' => 'btn btn-xs btn-default save-btn', 'id'=>'create_entity_btn']);; ?>
    <?php } else if (($widget->model->state == CommonModel::STATE_DELETED || $widget->model->state == ExpressWaybill::STATE_CLOSED)
        && $widget->model->operation != ExpressWaybill::OPERATION_CHANGE_STATUS) { ?>
        <?php
        AjaxSubmitButton::begin([
            'label' => Yii::t('app', 'Update'),
            'id' => 'ajax_submit_button_restore',
            'ajaxOptions' => [
                'type' => 'POST',
                'url' => $widget->restoreUrl,
                //'beforeSend' => new JsExpression("function(xhr) { if (!confirm('".$widget->deleteConfirm."')) xhr.abort(); }"),
                //'success' => new JsExpression("function() {
                //    $('#tabheader_' + window.parent.get_current_tab_id() + '>.close_this_tab', window.parent.document).click();
                //}")
            ],
            'options' => ['class' => 'btn btn-xs btn-default update-btn']
        ]);
        AjaxSubmitButton::end();
        ?>
    <?php } else { ?>
        <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-xs btn-default update-btn', 'id'=>'update_entity_btn']); ?>
    <?php } ?>
</li>
<li>
    <div>

        <?php if ($widget->model->state != CommonModel::STATE_DELETED) : ?>
            <?php
            AjaxSubmitButton::begin([
                'label' => Yii::t('app', 'Update'),
                'id' => 'ajax_submit_button_delete',
                'ajaxOptions' => [
                    'type' => 'POST',
                    'url' => $widget->deleteUrl,
                    //'beforeSend' => new JsExpression("function(xhr) { if (!confirm('".$widget->deleteConfirm."')) xhr.abort(); }"),
                    //'success' => new JsExpression("function() {
                    //    $('#tabheader_' + window.parent.get_current_tab_id() + '>.close_this_tab', window.parent.document).click();
                    //}")
                ],
                'options' => ['class' => 'btn btn-xs btn-default update-btn']
            ]);
            AjaxSubmitButton::end();
            ?>
            <?php
        endif
        ?>

    </div>
</li>
<li>
    <div class='btn btn-xs btn-default cancel-btn' id='cancel_entity_btn' tabindex="0"> <?= Yii::t('app','Cancel') ?> </div>
</li>
<li>
    <div class="users_info"><?= $widget->usersHtml ?> </div>
</li>

<?php
if (!$widget->model->isNewRecord) {
    echo Html::textInput('view_entity_url', $widget->viewUrl, ['hidden' => true, 'id' => 'view_entity_url']);
}
else
?>

<script type="text/javascript">

    <?php
    $getparams = Yii::$app->getRequest()->get();
    if ($widget->model->isNewRecord && $getparams['par_el_id']):
    ?>
    setattr_current_tab(<?= json_encode($getparams) ?>);
    <?php endif; ?>

    <?php if (!$widget->model->isNewRecord) :?>
    setattr_current_tab(<?= json_encode(["model_id"=>$widget->model->getIdentity()])?>);
    <?php endif; ?>




    var current_operation = '<?= $widget->model->operation ?>';

    var deleteBtn = $('#ajax_submit_button_delete');
    var restoreBtn = $('#ajax_submit_button_restore');
    var defaultBtn = $('#update_entity_btn');
    var cancelBtn = $('#cancel_entity_btn');


    deleteBtn.hide();
    if (restoreBtn)
        restoreBtn.hide();


    if (current_operation == <?= CommonModel::OPERATION_VIEW ?>){
        defaultBtn.hide();
        cancelBtn.hide();
    }

    if (current_operation == <?= CommonModel::OPERATION_CANCEL ?>) {
        restoreBtn.show();
    }

    if ('<?= $widget->model->state != CommonModel::STATE_CREATED && $widget->model->operation != CommonModel::OPERATION_CANCEL
        && $widget->model->operation != CommonModel::OPERATION_CHANGE_STATUS ?>'){
        cancelBtn.hide();
    }

    if ('<?= $widget->model->state == CommonModel::STATE_CREATED && $widget->model->operation == CommonModel::OPERATION_CANCEL ?>'){
        defaultBtn.hide();
        cancelBtn.hide();
    }

    operationToTitle(current_operation);

    $('#operation_selector').change(function() {
        if (this.value == <?= CommonModel::OPERATION_DELETE ?>) {
            //defaultBtn.hide();
            //deleteBtn.show();
            //cancelBtn.show();
            window.location.href = '<?= $widget->deleteUrl ?>';
        }
        if (this.value == <?= CommonModel::OPERATION_CLOSE?>) {

            window.location.href = '<?= $widget->closeUrl ?>';
        }
        else if (this.value == <?= CommonModel::OPERATION_CANCEL ?>) {
            restoreBtn.show();
            cancelBtn.show();
        }
        else if (this.value == <?= CommonModel::OPERATION_UPDATE ?>) {
            //defaultBtn.click();
            window.location.href = '<?= $widget->updateUrl ?>';
        }
        else if (this.value == <?= CommonModel::OPERATION_CHANGE_STATUS ?>) {
            window.location.href = '<?= $widget->editStatusUrl ?>';
        }
        else if (this.value == <?= CommonModel::OPERATION_CHANGE_NONDELIVERY ?>) {
            window.location.href = '<?= $widget->editNondeliveryUrl ?>';
        }
        else {
            defaultBtn.show();
            deleteBtn.hide();
            cancelBtn.show();
            if (restoreBtn)
                restoreBtn.hide();
        }

        //operationToTitle(this.value);
    });

    cancelBtn.click(function(){

        if ('<?= $widget->model->isNewRecord ?>')
            close_current_tab();
        else {
            window.location.href = '<?= $widget->viewUrl ?>';
        }

    });

    function operationToTitle(operation){

        var message = '';
        var date = $.now();
        var needSave = true;

        if (operation == <?= CommonModel::OPERATION_CREATE?>) {
            message = '<?= Yii::t('tab_title', 'create_command') ?>';
        }

        if (operation == <?= CommonModel::OPERATION_DELETE ?>) {
            message = '<?= Yii::t('tab_title', 'delete_command') ?>';
        }

        else if (operation == <?= CommonModel::OPERATION_CANCEL ?>) {
            message = '<?= Yii::t('tab_title', 'restore_command') ?>';
        }

        else if (operation == <?= CommonModel::OPERATION_VIEW ?>) {
            message = '<?= Yii::t('tab_title', 'view_command') ?>';
            needSave = false;
        }

        else if (operation == <?= CommonModel::OPERATION_UPDATE ?>) {
            message = '<?= Yii::t('tab_title', 'edit_command') ?>';
        }

        else if (operation == <?= CommonModel::OPERATION_CLOSE ?>) {
            message = '<?= Yii::t('tab_title', 'close_command') ?>';
        }

        change_current_tab(message,date,needSave);
    }

</script>
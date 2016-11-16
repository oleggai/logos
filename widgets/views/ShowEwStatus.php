<?php
/**
 * В файле форма виджета отображения статусов ЭН
 *
 * @author Точеный Д.Н.
 * @category Интрерфейс
 */

use app\models\ew\EwHistoryStatuses;
use app\models\ew\ListStatusesEw;
use app\widgets\GridWidget;
use yii\helpers\Url;

/* @var $this yii\web\View*/
/* @var $widget app\widgets\ShowEwStatus*/

$model = new EwHistoryStatuses();
$data_url = Url::to(['ew/express-waybill/get-history-statuses','id'=>0]);
?>

<style>
    .modal-wide .modal-dialog {
        width: 1000px;
    }
</style>

<!-- Modal -->
<div class="modal fade modal-wide" id="show_ew_status" tabindex="-1" role="dialog" aria-labelledby="show_ew_statusy_label">
    <div class="modal-dialog large" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?= Yii::t('app', 'Close')?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="show_ew_status_label"><?= Yii::t('ew', 'Tracking statuses history')?></h4>
            </div>
            <div class="modal-body">
                <?=
                GridWidget::widget([
                    'model' => $model,
                    'grid_name' => 'show_ew_status_grid',
                    'columns'=> [
                        ['id' => 'status_code', 'fillspace' => true],
                        ['id' => 'status_name', 'options' => ListStatusesEw::getList('title_full', true), 'fillspace' => true],
                        ['id' => '_date', 'fillspace' => true],
                        ['id' => 'country', 'fillspace' => true],
                        ['id' => 'city', 'fillspace' => true],
                        ['id' => 'department', 'fillspace' => true],
                        ['id' => 'user', 'fillspace' => true],
                        ['id' => 'comment', 'fillspace' => true],
                        ['id' => 'status_type_str', 'fillspace' => true],
                        ['id' => 'inner_status_str', 'fillspace' => true],
                    ],
                    'url' => $data_url,
                    'show_refresh_button'=>true,
                    'show_stateimage' => false,
                    'show_operations' => false,
                    'show_buttons' => false,
                    'load_on_start' =>false,
                    'show_id' => false,
                    'doubleclick_generate'=>false,
                    'show_pager' => false,
                    'grid_options'=>['width'=>930],
                    'oneGrid' => false
                ]);
                ?>
            </div>
        </div>
    </div>
</div>


<script>

    var show_ew_status_modal = $('#show_ew_status');

    /**
     * Отобразить модальное окно
     */
    function create_show_ew_status_modal(ew_id){

        var url = "<?= $data_url ?>";
        url = url.replace('0', ew_id);
        show_ew_status_grid.clearAll();
        show_ew_status_grid.load(url);

        $('#show_ew_status_grid_grid_refresh_button').attr('url', url);

        show_ew_status_modal.modal('show');
    }

</script>



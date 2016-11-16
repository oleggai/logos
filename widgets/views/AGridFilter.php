<?php

/**
 * Файл шаблона формы расширенного поиска
 * 
 * @author Дмитрий Чеусов
 * @category views
 */
use yii\helpers\Url;
?>

<div class="filter_div_data <?= $data['class'] ?>" style="width: <?= $data['width'] ?>" tabindex="1">
    <?php foreach ($data['fields'] as $field): ?>
        <?php if ($field['id'] == 'btn'): ?>
            <script>
                if (typeof (add_button) == 'undefined')
                    var add_button = Array();
                add_button['<?= $field["#id"]; ?>'] = '<div class="btn btn_findt btn_view_entity" id="<?= $field["#id"]; ?>" style="<?= $field["css"]; ?>" tab_title="<?= $field["title"]; ?>" ifr_url="<?= $field["ifr_url"]; ?>" unique_tab_id="<?= $field["unique_tab_id"]; ?>" <?= $field["str"]; ?> ></div>'
                document.write('<div id="<?= $field["#id"]; ?>"></div>');
            </script>
            <?php continue; ?>
        <?php endif ?>
        <?php if ($field['id'] == 'hr'): ?>
            <hr class="af_hr">
            <?php continue; ?>
        <?php endif ?>
        <?php if ($field['id'] == 'br'): ?>
            <br class="af_br">
            <?php continue; ?>
        <?php endif ?>
        <?php if ($field['id'] == 'title'): ?>
            <div class="af_title">
                <?= $field['label'] ?>
            </div>
            <?php continue; ?>
        <?php endif ?>
        <?php if ($field['id'] == 'add'): ?>
            <?php continue; ?>
        <?php endif ?>
        <?php if($field['type'] == app\models\common\CommonModel::FILTER_HIDDEN):?>
            <?= $field['input'] ?>
            <?php continue; ?>
        <?php endif; ?>
        <div class="form-inline <?= $field['class'] ?>">
            <div class="form-group">
                <label class="control-label <?= $field['label_class'] ?>">
                    <?= $field['label'] ?>
                </label>
                <?= $field['input'] ?>
                <span class="comment"><?= $filter['comment'] ?></span>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="btn btn-default btn-sm filter2_clear_btn">Очистить</div>
    <div class="btn btn-default btn-sm  afilter_submit_btn filter_search"><?= Yii::t('app', 'Apply'); ?></div>
</div>
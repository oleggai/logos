<?php
/* @var $widget app\widgets\CreatedUpdatedWidget*/

use yii\helpers\Html;

?>

<div class="custom-row-pad form-inline state-output">
    <div class="form-group">
        <label class="control-label"><?= Yii::t('app', 'State') ?></label>
        <input type="text" class="form-control" value="<?= $widget->data['state'] ?>" readonly>
    </div>
</div>

<div class="custom-row mservices-first-row">
    <div class="form-inline custom-row-pad">
        <?= Html::label(Yii::t('app', 'Creation options')) ?>
    </div>
</div>

<div class="custom-row mservices-second-row options-output">
    <div class="form-group">
        <div class="form-inline further" style="width:19%;">
            <label class="control-label"><?= Yii::t('app', 'Creation date') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['create_date'] ?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div class="form-inline further" style="width:19%;">
            <label class="control-label"><?= Yii::t('app', 'Country') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['create_country'] ?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div class="form-inline further" style="width:19%;">
            <label class="control-label"><?= Yii::t('app', 'City') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['create_city'] ?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div class="form-inline further short-label" style="width:20%;">
            <label class="control-label"><?= Yii::t('app', 'Warehouse') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['create_departament']?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div class="form-inline further long-label" style="width:23%;">
            <label class="control-label"><?= Yii::t('app', 'Username') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['create_surname'] ?>" readonly>
        </div>
    </div>
</div>

<hr class="custom-hr">

<div class="custom-row mservices-third-row">
    <div class="form-inline custom-row-pad">
        <?= Html::label(Yii::t('app','Last edit options')) ?>
    </div>
</div>

<div class="custom-row mservices-fourth-row options-output">
    <div class="form-group">
        <div class="form-inline further" style="width:19%;">
            <label class="control-label"><?= Yii::t('app','Last edit date') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['lastupdate_date'] ?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div class="form-inline further" style="width:19%;">
            <label class="control-label"><?= Yii::t('app','Country') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['lastupdate_country'] ?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div class="form-inline further" style="width:19%;">
            <label class="control-label"><?= Yii::t('app','City') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['lastupdate_city'] ?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div class="form-inline further short-label" style="width:20%;">
            <label class="control-label"><?= Yii::t('app','Warehouse') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['lastupdate_departament']?>" readonly>
        </div>
    </div>

    <div class="form-group">
        <div class="form-inline further long-label" style="width:23%;">
            <label class="control-label"><?= Yii::t('manifest','Username') ?></label>
            <input type="text" class="form-control" value="<?= $widget->data['lastupdate_surname'] ?>" readonly>
        </div>
    </div>
</div>

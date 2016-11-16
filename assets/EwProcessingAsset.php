<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для массовой обаботке накладных
 * @since 2.0
 */
class EwProcessingAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/ew/ew.afilter.js',
        'js/dateformat.min.js', // форматирование даты
        'js/ew/processing.js',
        'js/ew/processing.manual_input.js',
        'js/depends.ccwe.js',
        'js/application_func.js'
    ];

    public $css = [
        'css/ew.afilter.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

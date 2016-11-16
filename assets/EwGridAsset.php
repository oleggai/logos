<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для накладных
 * @since 2.0
 */
class EwGridAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/ew/ew.afilter.js',
    ];

    public $css = [
        'css/ew.afilter.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}

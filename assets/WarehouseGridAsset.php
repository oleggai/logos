<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для подразделений
 * @since 2.0
 */
class WarehouseGridAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/warehouse.afilter.js',
    ];

    public $css = [
        'css/warehouse.afilter.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для подразделений
 * @since 2.0
 */
class WarehouseAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/city.css',
    ];

    public $js = [
        'js/warehouse.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

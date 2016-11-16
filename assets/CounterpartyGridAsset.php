<?php

namespace app\assets;


use yii\web\AssetBundle;
use yii\web\View;

/**
 * Набор ресурсов для накладных
 * @since 2.0
 */
class CounterpartyGridAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/counterparty.afilter.js',
    ];

    public $css = [
        'css/counterparty.afilter.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

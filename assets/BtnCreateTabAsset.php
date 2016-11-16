<?php

/**
 * Набор ресурсов для \app\widgets\BtnCreateTab виджета
 */

namespace app\assets;

use yii\web\AssetBundle;

class BtnCreateTabAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/button_create_tab.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

<?php

namespace app\assets;

use yii\web\AssetBundle;


/**
 * Набор ресурсов для табов приложения
 */
class TabsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/main.css',
        'css/forms.css',
    ];

    public $js = [
        'js/main_for_tab_after.js',
        'js/jquery.hotkeys.js',
        'js/hotkeys_controller.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}
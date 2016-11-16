<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Набор ресурсов для накладных
 * @since 2.0
 */
class SelectCounterpartyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $jsOptions = ['position' => View::POS_HEAD];

    public $css = [
       // '../vendor/webix/codebase/skins/compact.css',
        'css/webix/aircompact_new.css',
        'css/dropdown.css',
    ];

    public $js = [
        'js/jquery.maskedinput.min.js',
        'js/webix/codebase/webix.js',
        'js/webix/codebase/i18n/ru.js' // русский перевод webix
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

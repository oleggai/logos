<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для админ панели для управления меню
 */
class MenuManageAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/jquery-ui.min.js',
        'js/jquery.mjs.nestedSortable.js',
        'js/menu-item.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

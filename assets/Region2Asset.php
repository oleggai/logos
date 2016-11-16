<?php

/**
 * Набор ресурсов для формы ввода справочника улиц
 *
 * @category Справочник улмц
 */

namespace app\assets;

use yii\web\AssetBundle;


class Region2Asset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/region2.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

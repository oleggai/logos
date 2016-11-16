<?php

/**
 * Набор ресурсов для формы ввода справочника улиц
 *
 * @category Справочник улмц
 */

namespace app\assets;

use yii\web\AssetBundle;


class StreetAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/street.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

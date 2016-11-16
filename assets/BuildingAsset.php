<?php

/**
 * Набор ресурсов для формы ввода справочника строений
 *
 * @category Справочник строений
 */

namespace app\assets;

use yii\web\AssetBundle;


class BuildingAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/building.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

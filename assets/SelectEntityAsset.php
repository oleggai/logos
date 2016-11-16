<?php

/**
 * @todo
 *
 * @category @todo
 */

namespace app\assets;

use yii\web\AssetBundle;

class SelectEntityAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
//        'css/city.css',
    ];

    public $js = [
//        'js/city.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

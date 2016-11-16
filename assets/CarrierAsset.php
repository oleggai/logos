<?php

/**
 * Набор ресурсов для формы ввода справочника перевозчиков
 *
 * @category Справочник перевозчиков
 */

namespace app\assets;

use yii\web\AssetBundle;


class CarrierAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/carrier.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

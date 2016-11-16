<?php
/**
 * Файл класса ресурсов тарифных зон
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов тарифных зон
 * @author Дмитрий Чеусов
 * @since 2.0
 */
class ListTariffZoneAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/list.tariff.zone.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

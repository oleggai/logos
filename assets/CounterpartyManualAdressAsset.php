<?php
/**
 * Created by PhpStorm.
 * User: Hopr
 * Date: 31.07.2015
 * Time: 15:48
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для контрагентов
 * @since 2.0
 */
class CounterpartyManualAdressAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/counterparty.manual.adress.js', // адреса
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}
<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для телефонов контактов контрагентов
 * @since 2.0
 */
class CounterpartyContactPersPhonesAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/counterparty.contact.pers.phones.js'
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

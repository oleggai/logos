<?php
/**
 * Created by PhpStorm.
 * User: Hopr
 * Date: 30.07.2015
 * Time: 10:58
 */


namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для контрагентов
 * @since 2.0
 */
class CounterpartyContactPersAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/counterparty.contact.pers.js', // контакты
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

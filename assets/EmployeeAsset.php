<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для сотрудников
 * @since 2.0
 */
class EmployeeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/employee.js', // сотрудники
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

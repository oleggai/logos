<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для накладных
 * @since 2.0
 */
class ManifestAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/dateformat.min.js', // форматирование даты
        'js/manifest.js', // манифест
        'js/check-unique/unique-manifest.js', // проверка уникальности значений при введении
        'js/attached-doc/delete-doc.js', // удаление файлов прикрепленных документов, удаление прикрепленного документа
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

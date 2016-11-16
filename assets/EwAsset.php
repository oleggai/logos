<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для накладных
 * @since 2.0
 */
class EwAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = [
        'js/list.min.js', // работа со списками
        'js/dateformat.min.js', // форматирование даты
        'js/stickers-place.js', // печать мест
        'js/ew/ew.js', // накладная
        'js/ew/ew.places.js', // места в накладной
        'js/ew/ew.positions.js', // позиции накладной
        'js/ew/ew.cost.js', // стоимости в накладной
        'js/check-unique/unique-ew.js', // проверка уникальности значений при введении
        'js/attached-doc/delete-doc.js', // удаление файлов прикрепленных документов, удаление прикрепленного документа
    ];
    public $css = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];

}

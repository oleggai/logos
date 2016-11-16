<?php
/**
 * Набор ресурсов для прикрепленных документов
 *
 * @category Приложение
 */


namespace app\assets;
use yii\web\AssetBundle;
use yii\web\View;

class AttachedDocAsset extends AssetBundle {
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $jsOptions = ['position' => View::POS_END];
    public $css = [
    ];
    public $js = array(
        'js/attached-doc/download.js', // скачивание файлов прикрепленных документов
        'js/attached-doc/delete-file.js', // удаление файлов прикрепленных документов
    );
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
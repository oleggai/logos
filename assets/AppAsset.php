<?php
/**
 * Набор ресурсов для всего приложения
 *
 * @category Приложение
 */


namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;


class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $jsOptions = ['position' => View::POS_HEAD];
    public $css = [
        'css/site.css',
        'css/main.css',
        'css/forms.css',
        'css/flex-styles.css',
        'css/webix/aircompact_new.css',
    ];
    public $js = array(
        'js/application_tabs_controller.js',//управление закладками
        'js/webix/codebase/webix.js',
        'js/application_func.js',//общепрограмные функции
        'js/jquery_parent_hotkeys.js',      //обработчик ГК мейнфрейма
        'js/hotkeys_parent_controller.js',  //функции ГК мейнфрейма
        'js/ew/popup-print.js',
        'js/date.js',                //api для работы с датами
         'js/journal.js',
        'js/check-unique/unique-event.js', // проверка уникальности значений при введении
        'js/check-unique/unique-tracking.js', // проверка уникальности значений при введении
    );
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];

}

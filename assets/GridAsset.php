<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Набор ресурсов для накладных
 * @since 2.0
 */
class GridAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $jsOptions = ['position' => View::POS_HEAD];

    public $css = [
       // '../vendor/webix/codebase/skins/compact.css',
        'css/webix/aircompact_new.css',
        'css/dropdown.css',
        'css/findform.css'
    ];
    
    public $js = []; // заполняем в методе init() т.к. в debug режиме подключаем "webix_debug.js" вместо "webix.js"

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\widgets\MaskedInputAsset',
    ];
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $this->js = [
            'js/grid.js',
            'js/jquery.maskedinput.min.js', // @todo избавиться от этой библиотеки и использовать yii\widgets\MaskedInputAsset (см. свойство $depends)
//            YII_DEBUG ? 'js/webix/codebase/webix_debug.js' : 'js/webix/codebase/webix.js',
            'js/webix/codebase/webix.js',
            'js/webix/codebase/i18n/ru.js', // русский перевод webix
            'js/journal.js',
            'js/date.js',
            'js/onlyJournalJS.js',
        ];
    }
}

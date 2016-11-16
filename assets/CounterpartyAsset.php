<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Набор ресурсов для контрагентов
 * @since 2.0
 */
class CounterpartyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/counterparty.js', // контрагент
        'js/counterparty.contact.js', // контакты
        'js/counterparty.ma.js', // адреса
        'js/counterparty.contract.js', // договора
        'js/check-unique/unique-counterparty.js', // проверка уникальности значений при введении
        'js/attached-doc/delete-doc.js', // удаление файлов прикрепленных документов, удаление прикрепленного документа
        'js/attached-doc/counterparty_contract_attached_doc.js', // Прикрепленные документы к контрактам
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];
}

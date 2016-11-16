<?php
exit();
/*
 * Создать файл index.php на основании этого файла. Удалить этот блок и все что выше. Заменить нужные значение.
 */
?>



<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

$app = new yii\web\Application($config);

$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function () {

    if (Yii::$app->session->get('user.language') != null)
        Yii::$app->language = Yii::$app->session->get('user.language');
    else
        Yii::$app->language = 'en';
});


$app->on(\yii\base\Application::EVENT_BEFORE_ACTION, function ($event) {

    if ($event->action->controller->module->id != 'api')
        if (Yii::$app->user->isGuest &&
            $event->action->id != 'login' &&
            $event->action->id != 'error'
        ) {
            Yii::$app->user->loginRequired();
        }

});

$app->run();
?>

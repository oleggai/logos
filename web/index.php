<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);
// comment out the following two lines when deployed to production
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

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

    if (!($event->action->controller->module->id == 'api' ||  in_array($event->action->controller->id,['common/barcode','common/qr'])))

/*    if ($event->action->controller->module->id != 'api')*/
        if (Yii::$app->user->isGuest &&
            $event->action->id != 'login' &&
            $event->action->id != 'error'
        ) {
            Yii::$app->user->loginRequired();
        }

});

$app->run();
?>

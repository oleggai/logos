<?php
namespace app\controllers\common;


use app\controllers\CommonController;
use app\models\counterparty\Counterparty;
use app\models\dictionaries\events\ListEvents;
use app\models\ew\ExpressWaybill;
use app\models\ew\ListStatusesEw;
use app\models\manifest\Manifest;

class CheckUniqueController extends CommonController {

    public function actionCheck() {
        $post = \Yii::$app->request->post();
        $keys = array_keys(\Yii::$app->request->post());
        $message = '';
        switch($keys[0]) {
            case 'ewNumber':
                //
                $message = ExpressWaybill::checkUniqueEwNum($post['ewNumber']);
                break;
            case 'manifestNumber':
                //
                $message = Manifest::checkUniqueManifestNum($post['manifestNumber']);
                break;
            case 'counterpartyNumber':
                //
                $message = Counterparty::checkUniqueCounterpartyNum($post['counterpartyNumber']);
                break;
            case 'eventNumber':
                //
                $message = ListEvents::checkUniqueEventNum($post['eventNumber']);
                break;
            case 'trackingNumber':
                //
                $message = ListStatusesEw::checkUniqueStatusesNum($post['trackingNumber']);
                break;
        }
        return json_encode(['message' => $message]);
    }
}
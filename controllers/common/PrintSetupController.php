<?php
namespace app\controllers\common;

use app\models\common\PrintSetupUser;

class PrintSetupController extends \app\controllers\CommonController {

    public function actionIndex() {
        if(PrintSetupUser::findOne(['user_id' => \Yii::$app->user->id])) {
            $model = PrintSetupUser::findOne(['user_id' => \Yii::$app->user->id]);
        }
        else {
            $model = new PrintSetupUser();
        }
        if($model->load(\Yii::$app->request->post()) && $model->validate()) {
            switch($model->print_third_party) {
                case 1:
                    //
                    $model->print_disp_third_party = 0;
                    break;
                case 2:
                    //
                    $model->print_disp_third_party = 1;
                    break;
                case 3:
                    //
                    $model->print_disp_third_party = 0;
                    break;
            }
            switch($model->print_delivery_cost) {
                case 4:
                    //
                    $model->print_disp_delivery_cost = 0;
                    break;
                case 5:
                    //
                    $model->print_disp_delivery_cost = 1;
                    break;
                case 6:
                    //
                    $model->print_disp_delivery_cost = 0;
                    break;
            }
            switch($model->print_actual_weight) {
                case 7:
                    //
                    $model->print_disp_actual_weight = 0;
                    break;
                case 8:
                    //
                    $model->print_disp_actual_weight = 1;
                    break;
                case 9:
                    //
                    $model->print_disp_actual_weight = 0;
                    break;
            }
            $model->save();
        }
        return $this->render('form', ['model' => $model]);
    }

    public function actionPopup() {
        $model = PrintSetupUser::findOne(['user_id' => \Yii::$app->user->id]);
        $post = \Yii::$app->request->post();
        if($model) {
            \Yii::$app->session->set('printSetupUserModelOld', serialize($model));
            $model->print_disp_delivery_cost = intval($post['print_disp_delivery_cost']);
            $model->print_disp_third_party = intval($post['print_disp_third_party']);
            $model->print_disp_actual_weight = intval($post['print_disp_actual_weight']);
            return $model->save() ? true : false;
        }
        return false;
    }

}
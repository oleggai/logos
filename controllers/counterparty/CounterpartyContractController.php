<?php

namespace app\controllers\counterparty;


use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\counterparty\CounterpartyContract;

/**
 * CounterpartyContractController implements the CRUD actions for CounterpartyContract model.
 */
class CounterpartyContractController extends CommonController
{

    public function getForm(){
        return '//counterparty/counterparty/form-contract';
    }

    /**
     * Creates a new CounterpartyContract model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param $counterparty
     * @return mixed
     */
    public function actionCreate($counterparty)
    {
        $model = new CounterpartyContract();
        $model->counterparty_id = $counterparty;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('//counterparty/counterparty/form-contract', ['model' => $model]);
        }
    }

    /**
     * Finds the CounterpartyContract model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CounterpartyContract the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CounterpartyContract::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetTable($counterparty){

        $result = array();

        $models = CounterpartyContract::find()->where(['counterparty_id' => $counterparty])->orderBy('id desc')->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }
}

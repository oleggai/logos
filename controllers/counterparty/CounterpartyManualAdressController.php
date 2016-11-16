<?php

namespace app\controllers\counterparty;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\counterparty\CounterpartyManualAdress;
use app\models\common\CommonModel;

/**
 * Контроллер адресов контрагентов
 * @author Tochonyi DM
 * @category Counterparty
 */
class CounterpartyManualAdressController extends CommonController
{

    /**
     * Список всех адресов указанного контрагента
     * @return mixed текст контента страницы
     */
    public function actionIndex($counterparty)
    {
        return $this->render('//counterparty/counterparty/grid-manual-address', ['model' => new CounterpartyManualAdress(), 'counterparty' => $counterparty]);
    }

    /**
     * Creates a new CounterpartyManualAdress model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($counterparty){

        $model = new CounterpartyManualAdress();
        $model->counterparty = $counterparty;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults();
            return $this->render('//counterparty/counterparty/form-manual-address', ['model' => $model]);
        }
    }

    public function getForm(){

        return '//counterparty/counterparty/form-manual-address';
    }

    /**
     * Finds the CounterpartyManualAdress model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CounterpartyManualAdress the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CounterpartyManualAdress::findOne($id)) !== null) {
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

        $models = CounterpartyManualAdress::find()->where(['counterparty' => $counterparty])->orderBy('id desc')->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }

    /**
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetTableSelect($counterparty){

        $result = array();

        $models = CounterpartyManualAdress::find()
            ->where(['counterparty' => $counterparty, 'state' => CommonModel::STATE_CREATED])->andWhere('adress_type=1')->orderBy('id desc')->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }

    /**
     * Получение массива полей
     * @return string Данные в формате json
     */
    public function actionGetAddress($id){
        if (($model = CounterpartyManualAdress::findOne($id)) == null) {
            return json_encode(null);
        }

        return json_encode($model->toJson());
    }

}

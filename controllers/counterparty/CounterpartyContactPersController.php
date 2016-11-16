<?php

namespace app\controllers\counterparty;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\counterparty\CounterpartyContactPers;
use app\models\common\CommonModel;

/**
 * Контроллер контактов контрагентов
 * @author Tochonyi DM
 * @category Counterparty
 */
class CounterpartyContactPersController extends CommonController
{

    /**
     * Список всех контактов указанного контрагента
     * @return mixed текст контента страницы
     */
    public function actionIndex($counterparty)
    {
        return $this->render('//counterparty/counterparty/grid-contact-pers', ['model' => new CounterpartyContactPers(), 'counterparty' => $counterparty]);
    }

    /**
     * Creates a new CounterpartyContactPers model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($counterparty){

        $model = new CounterpartyContactPers();
        $model->counterparty = $counterparty;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults();
            return $this->render('//counterparty/counterparty/form-contact-pers', ['model' => $model]);
        }
    }

    public function getForm(){
        return '//counterparty/counterparty/form-contact-pers';
    }

    /**
     * Finds the CounterpartyContactPers model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CounterpartyContactPers the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id){

        if (($model = CounterpartyContactPers::findOne($id)) !== null) {
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

        $models = CounterpartyContactPers::find()->where(['counterparty' => $counterparty])->orderBy('id desc')->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }

    /**
     * Получение данных для табличного отображения при выборе
     * @return string Данные в формате json
     */
    public function actionGetTableSelect($counterparty){

        $result = array();

        $models = CounterpartyContactPers::find()->where(['counterparty' => $counterparty, 'state' => CommonModel::STATE_CREATED])
            ->orderBy('id desc')->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }

    /**
     * Получение массива полей
     * @return string Данные в формате json
     */
    public function actionGetContactPers($id){
        if (($model = CounterpartyContactPers::findOne($id)) == null) {
            return json_encode(null);
        }

        return json_encode($model->toJson());
    }

}

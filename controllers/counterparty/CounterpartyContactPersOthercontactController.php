<?php

namespace app\controllers\counterparty;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\counterparty\CounterpartyContactPersOthercontact;

/**
 * CounterpartyContactPersOthercontactController implements the CRUD actions for CounterpartyContactPersOthercontact model.
 */
class CounterpartyContactPersOthercontactController extends CommonController
{
/*    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }*/

    /**
     * Lists all CounterpartyContactPersOthercontact models.
     * @return mixed
     */
/*    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => CounterpartyContactPersOthercontact::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }*/

    /**
     * Displays a single CounterpartyContactPersOthercontact model.
     * @param string $id
     * @return mixed
     */
/*    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }*/

    /**
     * Creates a new CounterpartyContactPersOthercontact model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($contact)
    {
        $model = new CounterpartyContactPersOthercontact();
        $model->counterparty_contact_pers = $contact;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('//counterparty/counterparty/form-contact-pers-othercontact', [
                'model' => $model,
            ]);
        }
    }
/* 'create' */
    /**
     * Updates an existing CounterpartyContactPersOthercontact model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
 /*   public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }*/

    public function getForm(){
        return '//counterparty/counterparty/form-contact-pers-othercontact';
    }

    /**
     * Deletes an existing CounterpartyContactPersOthercontact model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {

        $model = $this->findModel($id);

        //$model->state = CounterpartyContactPersOthercontact::STATE_DELETED;
        $model->operation = self::OPERATION_DELETE;
        $model->save(false);

        $getParams = Yii::$app->getRequest()->get();
        if ($getParams['current_operation'] == CounterpartyContactPersOthercontact::OPERATION_VIEW)
            return $this->redirect(['view', 'id' => $model->id]);

        if ($getParams['current_operation'] == CounterpartyContactPersOthercontact::OPERATION_GRIDVIEW)
            return json_encode('item.state = ' . $model->state . ';item.stateText="' . $model->stateText . '"');

        return $this->redirect(['view', 'id' => $model->id]);


    }

    /**
     * Finds the CounterpartyContactPersOthercontact model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CounterpartyContactPersOthercontact the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CounterpartyContactPersOthercontact::findOne($id)) !== null) {
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

        $models = CounterpartyContactPersOthercontact::find()
            ->leftJoin('{{%counterparty_contact_pers}} pers', 'counterparty_contact_pers=pers.id')
            ->where(['pers.counterparty' => $counterparty])->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }

}

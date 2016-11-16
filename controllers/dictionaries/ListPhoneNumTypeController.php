<?php

namespace app\controllers\dictionaries;

use Yii;
use app\models\counterparty\ListPhoneNumType;
use yii\data\ActiveDataProvider;
use app\controllers\CommonController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ListPhoneNumTypeController implements the CRUD actions for ListPhoneNumType model.
 */
class ListPhoneNumTypeController extends CommonController
{
    /**
     * Lists all ListPhoneNumType models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new ListPhoneNumType()]);
    }


    /**
     * Creates a new ListPhoneNumType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListPhoneNumType();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Finds the ListPhoneNumType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListPhoneNumType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListPhoneNumType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionGetTable() {

        $result = [];

        $models = ListPhoneNumType::find()
            ->where($this->getFiltersWhere(new ListPhoneNumType()))
            ->orderBy('id desc')
            ->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }

}

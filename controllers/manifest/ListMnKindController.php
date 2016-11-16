<?php

namespace app\controllers\manifest;

use Yii;
use app\models\manifest\ListMnKind;
//use app\models\manifest\ListMnKindSearch;
use app\controllers\CommonController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ListMnKindController implements the CRUD actions for ListMnKind model.
 */
class ListMnKindController extends CommonController
{
    /*
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
*/
    /**
     * Lists all ListMnKind models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new ListMnKind()]);
    }

    /**
     * Displays a single ListMnKind model.
     * @param string $id
     * @return mixed

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
*/
    /**
     * Creates a new ListMnKind model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListMnKind();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            //return $this->render('create', ['model' => $model,]);
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }

    }

    /**
     * Updates an existing ListMnKind model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ListMnKind model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
*/
    /**
     * Finds the ListMnKind model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListMnKind the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListMnKind::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionGetTable() {

        $result = [];

        $models = ListMnKind::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }

}

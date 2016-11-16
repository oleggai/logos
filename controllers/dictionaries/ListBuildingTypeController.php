<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\address\ListBuildingType;

/**
 * Контроллер типов нас. пунктов
 */
class ListBuildingTypeController extends CommonController
{
    /**
     * Lists all ListBuildingType models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new ListBuildingType()]);
    }

    /**
     * Creates a new ListBuildingType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new ListBuildingType();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Finds the ListBuildingType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListbuildingType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListBuildingType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionGetTable() {

        $result = [];

        $models = ListBuildingType::find()
            ->where($this->getFiltersWhere(new ListBuildingType()))
            ->orderBy('id desc')
            ->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }
}

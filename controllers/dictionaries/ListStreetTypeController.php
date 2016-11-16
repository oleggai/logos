<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\address\ListStreetType;

/**
 * Контроллер типов улиц
 */
class ListStreetTypeController extends CommonController
{
    /**
     * Lists all ListStreetType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ListStreetType();

        return $this->render('grid', [
            'model' => $searchModel,
        ]);
    }

    /**
     * Creates a new ListStreetType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListStreetType();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ListStreetType model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['update', 'id' => $id]);
    }*/

    /**
     * Finds the ListStreetType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListStreetType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListStreetType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    public function actionGetTable() {

        $result = [];

        $models = ListStreetType::find()
            ->where($this->getFiltersWhere(new ListStreetType()))
            ->orderBy('id desc')
            ->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }

}

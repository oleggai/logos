<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\address\ListRegionType;

/**
 * Контроллер типов регионов
 */
class ListRegionTypeController extends CommonController
{
    /*
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    */

    /**
     * Lists all ListRegionType models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ListRegionType();
        $level = Yii::$app->request->get('level');

        return $this->render('grid', ['model' => $searchModel, 'level'=>$level ]);
    }

    /**
     * Creates a new ListRegionType model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListRegionType();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }


    /**
     * Deletes an existing ListRegionType model.
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
     * Finds the ListRegionType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListRegionType the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListRegionType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionGetTable() {
        $result = [];
        $model = new ListRegionType();

        $and_where = '';
        $level = Yii::$app->request->get('level');
        if ($level){
            $and_where = "level = $level";
        }

        $models = ListRegionType::find()
                ->where($this->getFiltersWhere($model))
                ->andWhere($and_where)
                ->orderBy('id desc')
                ->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }

    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result_id_txt = [];
        $result = ListRegionType::getList('name_short',true, $lang);
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id'=>$key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

}

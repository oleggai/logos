<?php

namespace app\controllers\dictionaries;

use Yii;
use app\models\dictionaries\employee\ListOrgKind;
use yii\data\ActiveDataProvider;
use app\controllers\CommonController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ListOrgKindController implements the CRUD actions for ListOrgKind model.
 */
class ListOrgKindController extends CommonController
{


    /**
     * Lists all ListOrgKind models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new ListOrgKind()]);
    }



    /**
     * Creates a new ListOrgKind model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListOrgKind();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Получение списка видов организаций
     * @return string Данные в формате json
     */
    public function actionGetTable() {
        $result = [];
        $model = new ListOrgKind();

        $models = ListOrgKind::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }


    /**
     * Finds the ListOrgKind model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListOrgKind the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListOrgKind::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Получение пар ключ - значение списка типов для формы
     * @return string json
     */
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result = ListOrgKind::getList(true, 'name', $lang);

        // формат ответа: 1 - для Select2, 2 - для select
        $format = $getParams['format'];
        if (!$format)
            $format = 1;

        foreach ($result as $key => $val) {
            if ($format == 1)
                $result_id_txt[] = ['id' => $key, 'txt' => $val];
            else
                $result_id_txt[$key] = $val;
        }
        return json_encode($result_id_txt);
    }
}

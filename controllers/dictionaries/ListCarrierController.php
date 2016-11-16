<?php

namespace app\controllers\dictionaries;

use Yii;
use app\models\dictionaries\carrier\ListCarrier;
use app\controllers\CommonController;
use yii\web\NotFoundHttpException;

/**
 * ListCarrierController implements the CRUD actions for ListCarrier model.
 */
class ListCarrierController extends CommonController
{
    /**
     * Lists all ListCarrier models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new ListCarrier()]);
    }
    /**
     * Creates a new ListCarrier model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListCarrier();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model,
            ]);
        }
    }
    /**
     * Получение списка перевозчиков
     * @return string Данные в формате json
     */
    public function actionGetTable() {
        $result = [];
        $model = new ListCarrier();

        $models = ListCarrier::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }
    /**
     * Finds the ListCarrier model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListCarrier the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListCarrier::findOne($id)) !== null) {
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

        $result = ListCarrier::getList(true, 'name', $lang);

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

<?php

/**
 * Файл класса контроллера WbOrderType
 */

namespace app\controllers\dictionaries;

use Yii;
use app\controllers\CommonController;
use app\models\ew\WbOrderType;
use yii\web\NotFoundHttpException;

/**
 * Класс контроллер WbOrderType
 * @author Дмитрий Чеусов
 * @category ew
 */
class WbOrderTypeController extends CommonController {

    /**
     * Получение пар ключ - значение списка типов для формы
     * @return string json
     */
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result = WbOrderType::getVisibleList(true, 'name', $lang);

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

    /**
     * Lists all WbOrderType models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new WbOrderType()]);
    }

    public function actionCreate()
    {
        $model = new WbOrderType();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    protected function findModel($id)
    {
        if (($model = WbOrderType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionGetTable() {

        $result = [];

        $models = WbOrderType::find()
            ->where($this->getFiltersWhere(new WbOrderType()))
            ->orderBy('id desc')
            ->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }

}

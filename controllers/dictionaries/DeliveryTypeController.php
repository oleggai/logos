<?php

/**
 * Файл класса контроллера DeliveryType (Справочник типов доставки)
 */

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\ew\DeliveryType;
use app\controllers\CommonController;

/**
 * Класс контроллер DeliveryType
 * @author Гайдаенко Олег
 * @category ew
 */
class DeliveryTypeController extends CommonController {

    public function actionIndex() {
        return $this->render('grid', ['model' => new DeliveryType()]);
    }

    public function actionCreate() {

        $model = new DeliveryType();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Получение списка типов доставки
     * @return string Данные в формате json
     */
    public function actionGetTable() {
        $result = [];
        $model = new DeliveryType();

        $models = DeliveryType::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
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

        $result = DeliveryType::getList(true, 'name', $lang);

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
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор типа доставки
     * @return DeliveryType загруженная модель типа доставки
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = DeliveryType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

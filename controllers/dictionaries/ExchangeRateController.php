<?php

namespace app\controllers\dictionaries;

use Yii;
use app\models\dictionaries\exchangerate\ExchangeRate;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;

/**
 * Контроллер курсов валют
 */
class ExchangeRateController extends CommonController
{

    /**
     * Отображение списка курсов
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new ExchangeRate()]);
    }

    /**
     * Создание курса
     * @return mixed текст контента страницы
     */
    public function actionCreate()
    {
        $model = new ExchangeRate();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            //$getParams = Yii::$app->getRequest()->get();
            //$model->generateDefaults($getParams);
            $model->generateDefaults();
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Удаление курса
     * @param string $id идентификатор удаляемой страны
     * @return mixed текст контента страницы
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['update', 'id' => $id]);
    }*/


    /**
     * Получение списка стран
     * @return string Данные в формате json
     */
    public function actionGetTable() {
        $result = [];
        $model = new ExchangeRate();
        $filter = $this->getFiltersWhere($model);

        $models = ExchangeRate::find()
            ->orderBy('id desc')
            ->where($filter)->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор страны
     * @return ExchangeRate загруженная модель курса
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = ExchangeRate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

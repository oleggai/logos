<?php

namespace app\controllers\dictionaries;

use Yii;
use app\models\dictionaries\currency\Currency;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;

/**
 * Контроллер валют
 * @author Richok FG\
 * @category currency
 */
class CurrencyController extends CommonController
{
    /**
     * Начальная инициализация контроллера
     */
    public function init() {
        //$this->accessCheck = 'currency'; // имя сущности для проверки
        parent::init();
    }

    /**
     * Список всех Currency моделей
     * @return mixed текст контента страницы
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new Currency()]);
    }

    /**
     * Создание Currency модели
     * @return mixed текст контента страницы
     */
    public function actionCreate()
    {
        $model = new Currency();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults();
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id идентификатор валюты
     * @return Currency загруженная модель валюты
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = Currency::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }


    /**
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetTable(){

        $result = array();
        $model = new Currency();

        $filter = $this->getFiltersWhere($model);
        if ($filter == null)
            $models = Currency::find()->orderBy('id desc')->all();
        else
            $models = Currency::find()
                ->leftJoin('{{%currency_translate}}', 'currency_id=id')
                ->where($filter)
                ->orderBy('id desc')
                ->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }

    /**
     * Получение списка валют в формате ключ - id, значение - name_short
     * @return string Данные в формате json
     */
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result = (Currency::getList('nameShort', $lang));
        
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id' => $key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

}

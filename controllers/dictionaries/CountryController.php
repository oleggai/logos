<?php

namespace app\controllers\dictionaries;

use Yii;
use app\models\dictionaries\country\Country;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\common\CommonModel;
use app\models\dictionaries\address\ListCity;

/**
 * Контроллер стран
 * @author Richok FG
 * @category country
 */
class CountryController extends CommonController
{
    /**
     * Начальная инициализация контроллера
     */
    public function init() {
        $this->accessCheck = 'country'; // имя сущности для проверки
        parent::init();
    }

    /**
     * Список всех Country моделей
     * @return mixed текст контента страницы
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new Country()]);
    }

    /**
     * Создание Country модели
     * @return mixed текст контента страницы
     */
    public function actionCreate()
    {
        $model = new Country();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор страны
     * @return Country загруженная модель страны
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = Country::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Получение списка стран
     * @return string Данные в формате json
     */
    public function actionGetTable() {

        $model = new Country();

        $filter = $this->getFiltersWhere($model);
        if ($filter == null)
            $models = Country::find()->orderBy('id desc');
        else
            $models = Country::find()
                ->leftJoin('{{%country_translate}}', 'country_id=id')
                ->where($this->getFiltersWhere($model))
                ->orderBy('id desc');

        return json_encode(CommonModel::getDataWithLimits($models));
    }

    /**
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetLogs() {
        $getParams = Yii::$app->getRequest()->get();
        $f_country_id = $getParams['f_country_id'];

        if (!$f_country_id)
            return;

        $result = [];
        $model = Country::findOne(['id' => $f_country_id]);
        if ($model) {
            $logs = $model->logs;
            foreach($logs as $log)
                $result[] = $log->toJson();
        }

        return json_encode($result);
    }

    /**
     * Получение списка стран в формате ключ - id, значение - name_short
     * @return string Данные в формате json
     */
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result_id_txt = [];
        $result = Country::getListFast('name_short',true, $lang);
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id'=>$key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

    /**
     * Получить список городов с указанием страны
     * @return string Данные в формате json
     */
    public function actionGetCountryByCity() {
        
        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        $result_cities = [];

        $resultCities = ListCity::find()
            ->where('visible = :visible AND state != :state', [':visible' => CommonModel::VISIBLE, ':state' => CommonModel::STATE_DELETED])
            ->all();
        
        if (!empty($resultCities))
            foreach ($resultCities as $city) {
                $country = $city->regionModel->countryModel;
                if (!empty($country)) {
                    $result_cities[$city->id] = [
                        'id' => $city->id,
                        'name' => $city->getName($lang),
                        'country_id' => $country->id,
                        'country_name' => $country->nameShort,
                    ];
                }
            }
        return json_encode($result_cities);
    }
    
    public function actionGetCountry($id, $uniqId = null)
    {
        $model = Country::findOne(['id' => $id]);
        if ($model === null) {
            $model = new Country();
        }
        if ($uniqId !== null) {
            $model->setUniqueId($uniqId);
        }
        
        return json_encode($model->toJson(true));
    }
    
    /**
     * Возвращает SelectEntityWidget для данной сущности
     */
    public function actionGetSelectEntityWidget()
    {
        $model = null;
        $getParams = Yii::$app->getRequest()->get();
        
        if (!empty($getParams['id'])) {
            $model = Country::findOne($getParams['id']);
        }
        
        if ($model === null) {
            $model = new Country();
        }
        
        if (!empty($getParams['uniqId'])) {
            $model->setUniqueId($getParams['uniqId']);
        }
        
        if (!empty($getParams['operation'])) {
            $model->setOperation($getParams['operation']);
        }
        
        return $this->renderAjax('selectEntityWidget', [
            'model' => $model,
        ]);
    }

}

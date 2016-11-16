<?php

/**
 * Файл класса контроллера тарифных зон
 */

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\tariff\ListTariffZone;
use app\models\dictionaries\country\Country;

/**
 * Контроллер тарифных зон
 * @author Дмитрий Чеусов
 * @category tariff
 */
class ListTariffZoneController extends CommonController {

    /**
     * Начальная инициализация контроллера
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }

    /**
     * Список всех тарифных зон
     * @return mixed текст контента страницы
     */
    public function actionIndex() {
        return $this->render('grid', ['model' => new ListTariffZone()]);
    }

    /**
     * Создание модели
     * @return mixed форма модели
     */
    public function actionCreate() {

        $model = new ListTariffZone();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Редактирование модели
     * @param string $id идентификатор модели
     * @return mixed форма модели
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id идентификатор модели
     * @return ListTariffZone загруженная модель
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id) {
        if (($model = ListTariffZone::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Вывод табличных данных
     * @return string json
     */
    public function actionGetTable() {

        $city_table = ListCity::tableName();
        $region_table = ListRegion::tableName();
        $tariff_zone_table = ListTariffZone::tableName();
        
        $result = array();
        $model = new ListTariffZone();

        $filter = $this->getFiltersWhere($model);
        if ($filter == null) {
            $models = ListTariffZone::find()->orderBy('id DESC')->all();
        } else {
            $models = ListTariffZone::find()
                    ->leftJoin("$city_table `c2`", "`c2`.`tariff_zone` = $tariff_zone_table.`id`")
                    ->leftJoin("$region_table `r2`", "`r2`.`id` = c2.`region`")
                    ->where($filter)
                    ->orderBy('id DESC')
                    ->all();
        }
        foreach ($models as $i => $model) {
            $result[$i] = $model->getAttributes();

            foreach ($model->listCities as $listCitiy) {
                $result[$i]['listCities'] .= ", " .
                        $listCitiy->getAttributes()['name_' . Yii::$app->language];
            }
            $result[$i]['listCities'] = trim($result[$i]['listCities'], ', ');
        }

        return json_encode($result);
    }
    
    /**
     * Возвращает вхождения для тарифной зоны
     * @params integer|null $id ID ListTariffZone
     * @return string JSON задокированная строка с данными
     */
    public function actionGetEntriesTable($id = null)
    {
        $model = ListTariffZone::findOne($id);
        if ($model == null) {
            $model = new ListTariffZone();
        }
        $result = $model->getEntries();
        
        return json_encode($result);
    }
    
    public function actionGetDetailedTable() {

        $request = Yii::$app->request->get();

        if(empty($request['id'])) return json_encode([]);
        
        $cities = ListCity::find()
                ->where(['tariff_zone' => $request['id']])
                ->all();
        $regions = ListRegion::find()
                ->where(['tariff_zone' => $request['id']])
                ->all();
        $countries = Country::find()
                ->where(['tariff_zone' => $request['id']])
                ->all();

        $result = [];


//        die(var_dump($cities));

        foreach ($countries as $country) {

            $countryName = $country->nameShort;

            $result[] = [
                'state' => $country->state,
                'city' => '',
                'region2' => '',
                'region1' => '',
                'country' => $countryName,
                'options' => '<a href=index.php?r=dictionaries/list-tariff-zone/delete-one&country=' . $country->id . '&id=' . $request['id'] . '>x</a>',
            ];
        }
        $countryName = '';

        foreach ($regions as $region) {

            $region1Name = $region->getName(Yii::$app->language);
            if (!empty($region->parent))
                $region2Name = $region->parent->getName(Yii::$app->language);
            if (!empty($region->countryModel))
                $countryName = $region->countryModel->nameShort;

            $result[] = [
                'state' => $region->state,
                'city' => '',
                'region2' => $region2Name,
                'region1' => $region1Name,
                'country' => $countryName,
                'options' => '<a href=index.php?r=dictionaries/list-tariff-zone/delete-one&region=' . $region->id . '&id=' . $request['id'] . '>x</a>',
            ];
        }
        $countryName = $region2Name = $region1Name = '';

        foreach ($cities as $city) {

            $cityName = $city->getName(Yii::$app->language);
            if (!empty($city->regionModel))
                $region1Name = $city->regionModel->getName(Yii::$app->language);
            if (!empty($city->regionModel->parent))
                $region2Name = $city->regionModel->parent->getName(Yii::$app->language);
            if (!empty($city->regionModel->countryModel))
                $countryName = $city->regionModel->countryModel->nameShort;
            else($countryName = '---');

            $result[] = [
                'state' => $city->state,
                'city' => $cityName,
                'region2' => $region2Name,
                'region1' => $region1Name,
                'country' => $countryName,
                'options' => '<a href=index.php?r=dictionaries/list-tariff-zone/delete-one&city=' . $city->id . '&id=' . $request['id'] . '>x</a>',
            ];
        }
        return json_encode($result);
    }

    public function actionDeleteOne() {

        $request = Yii::$app->request->get();

        $id = (int) $request['id'];
        $city = (int) $request['city'];
        $region = (int) $request['region'];
        $coutry = (int) $request['country'];

        if ($city)
            ListCity::updateAll(['tariff_zone' => null], 'id = ' . $city);
        if ($region)
            ListRegion::updateAll(['tariff_zone' => null], 'id = ' . $region);
        if ($coutry)
            Country::updateAll(['tariff_zone' => null], 'id = ' . $coutry);

        Yii::$app->response->redirect(array('dictionaries/list-tariff-zone/view', 'id' => $id));
    }

}

<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\warehouse\ListWarehouse;
use app\models\common\CommonModel;
use yii\helpers\Url;

/**
 * Контроллер населенных пунктов
 */
class ListCityController extends CommonController
{

    /**
     * Lists all ListCity models.
     * @return mixed
     */
    public function actionIndex()
    {
        $region = Yii::$app->getRequest()->get()['region'];
        $country = Yii::$app->getRequest()->get()['country'];
        return $this->render('grid', ['model' => new ListCity(), 'region'=>$region, 'country'=>$country]);
    }

    /**
     * Список ListCity моделей, доступных для выбора
     * @return mixed текст контента страницы
     */
    public function actionIndexSelect()
    {
        $region = Yii::$app->getRequest()->get()['region'];
        $country = Yii::$app->getRequest()->get()['country'];
        $selectedRoutes = Yii::$app->getRequest()->get()['selectedRoutes'];
        
        return $this->render('grid', [
            'model' => new ListCity(),
            'region' => $region,
            'country' => $country,
            'selectedRoutes' => $selectedRoutes,
        ]);
    }
    
    /**
     * Creates a new ListCity model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListCity();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model,]);
        }
    }

    /**
     * Deletes an existing ListCity model.
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
     * Finds the ListCity model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListCity the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListCity::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionGetTable() {

        $getParams = Yii::$app->getRequest()->get();
        $region2_ref = $getParams['region'];
        $countryId = $getParams['country'];
        $ext_where = '1=1';
        if ($region2_ref)
            $ext_where .= " and region = $region2_ref";
        if ($countryId)
            $ext_where .= " and region in ( select id from ".ListRegion::tableName()." where country = $countryId)";

        $city_table = ListCity::tableName();
        $region_table = ListRegion::tableName();

        $result = [];
        $model = new ListCity();

        $filter = $this->getFiltersWhere($model);

        // отдельная обработка фильтра по индексу
        $getParams = Yii::$app->getRequest()->get();
        $index_input = $getParams['f_city_index'];
        $indexWhere = '';
        if ($index_input) {
            $t = $model->tableName();
            $prefix = substr($index_input, 0, strlen($index_input) - 3);
            $val = substr($index_input, -3);
            $indexWhere =
                "substr($t.begin_per_indexes, 1, char_length($t.begin_per_indexes) - 3) = '$prefix' AND
                substr($t.end_per_indexes, 1, char_length($t.end_per_indexes) - 3) = '$prefix' AND
                cast(substr($t.begin_per_indexes, -3) as UNSIGNED) <= $val AND cast(substr($t.end_per_indexes, -3) as UNSIGNED) >= $val";
        }

        if ($filter)
            $models = ListCity::find()
                ->leftJoin("$region_table r2", "r2.id = $city_table.region")
                ->where($filter)
                ->andWhere($indexWhere)
                ->andWhere($ext_where)
                ->orderBy("$city_table.id desc")
                ->all();
        else
            $models = ListCity::find()->where($ext_where)->andWhere($indexWhere)->orderBy("id desc")->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }

    public function actionGetCity($id, $uniqId = null)
    {
        $model = ListCity::findOne(['id' => $id]);
        if ($model === null) {
            $model = new ListCity();
        }
        if ($uniqId !== null) {
            $model->setUniqueId($uniqId);
        }
//        return json_encode($model->toJson());
        return json_encode($model->toJson('', true));
    }

    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $andWhere = '';
        $regionId = $getParams['region'];
        $countryId = $getParams['country'];
        // формат ответа: 1 - для Select2, 2 - для select
        $format = $getParams['format'];
        if (!$format)
            $format = 1;

        if ($countryId)
            $andWhere = "region in ( select id from ".ListRegion::tableName()." where country = $countryId)";
        if ($regionId) // если указан регион - нужно искать по региону, фильтр по стране не нужен
            $andWhere = "region = $regionId";

        $result_id_txt = [];
        $result = ListCity::getList('name', true, $lang, $andWhere);

        foreach ($result as $key => $val) {
            if ($format == 1)
                $result_id_txt[] = ['id' => $key, 'txt' => $val];
            else
                $result_id_txt[$key] = $val;
        }
        return json_encode($result_id_txt);
    }

    public function actionGetRelatedData() {

        $result = [];

        $getParams = Yii::$app->getRequest()->get();
        $id = $getParams['city_id'];

        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        if (!$id)
            return $result;

        $city = $this->findModel($id);
        if (!$city)
            return $result;

        $result = [
            'city_type_name' => $city->cityType->{"name_$lang"},
            'region2_type_name' => $city->regionModel->regionType->{"name_$lang"},
            'region2_name' => $city->regionModel->{"name_$lang"},
            'region1_type_name' => $city->regionModel->parent->regionType->{"name_$lang"},
            'region1_name' => $city->regionModel->parent->{"name_$lang"},
            'country_name' => $city->regionModel->countryModel->namesShort[$lang]
        ];

        return json_encode($result);
    }


    public function actionGetFullAddress() {

        $result = [];

        $getParams = Yii::$app->getRequest()->get();
        $id = $getParams['city_id'];

        if (!$id)
            return $result;

        $city = $this->findModel($id);
        if (!$city)
            return $result;

        $result = [
            'region2' => $city->region,
            'region1' => $city->regionModel->parent_id,
            'country' => $city->regionModel->country,
        ];

        return json_encode($result);
    }
    
    /**
     * Получение списка городов региона второго уровня
     * @return json string
     */
    public function actionGetCities(){
        $getParams = Yii::$app->getRequest()->get();
        $id = $getParams['region'];
        foreach(ListCity::getList('name', true, null, $andWhere = 'region=' . $id) as $id=>$element) {
            $response[] = [
                'id' => $id,
                'txt' => $element,
            ];
        }
        return json_encode($response);
    }
    
    public function actionGetCityByWarehouse() {
        
        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        $result_warehouses = [];

        $resultWarehouses = ListWarehouse::find()
                ->where('visible = :visible AND state != :state', [':visible' => CommonModel::VISIBLE, ':state' => CommonModel::STATE_DELETED])
                ->all();

        if (!empty($resultWarehouses))
            foreach ($resultWarehouses as $warehouse) {
                if (!empty($warehouse->cityModel)) {
                    $result_warehouses[$warehouse->id] = [
                        'id' => $warehouse->id,
                        'name' => $warehouse->getName($lang),
                        'city_id' => $warehouse->cityModel->id,
                        'city_name' => $warehouse->cityModel->getName($lang),
                    ];
                }
            }
        return json_encode($result_warehouses);
    }

    /**
     * Возвращает SelectEntityWidget для данной сущности
     */
    public function actionGetSelectEntityWidget()
    {
        $city = null;
        $getParams = Yii::$app->getRequest()->get();
        
        if (!empty($getParams['id'])) {
            $cityId = $getParams['id'];
            $city = ListCity::findOne(['id' => $cityId]);
        }
        
        if ($city === null) {
            $city = new ListCity();
        }
        
        echo $this->renderAjax('selectEntityWidget', [
            'city' => $city,
        ]);
    }
}

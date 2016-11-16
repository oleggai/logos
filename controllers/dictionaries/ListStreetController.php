<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\address\ListStreet;

/**
 * Контроллер улиц
 */
class ListStreetController extends CommonController
{
    /**
     * Lists all ListStreet models.
     * @return mixed
     */
    public function actionIndex()
    {
        $city = Yii::$app->getRequest()->get()['city'];

        return $this->render('grid', ['model' => new ListStreet(), 'city'=>$city]);
    }

    /**
     * Creates a new ListStreet model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListStreet();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Finds the ListStreet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListStreet the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListStreet::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionGetTable() {

        $getParams = Yii::$app->getRequest()->get();
        $city_ref = $getParams['city'];
        $ext_where = '1=1';
        if ($city_ref)
            $ext_where .= " and city = $city_ref";

        $street_table = ListStreet::tableName();
        $city_table = ListCity::tableName();
        $region_table = ListRegion::tableName();

        $result = [];
        $model = new ListStreet();

        $filter = $this->getFiltersWhere($model);

        // отдельная обработка фильтра по индексу
        $getParams = Yii::$app->getRequest()->get();
        $index_input = $getParams['f_street_index'];
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
            $models = ListStreet::find()
                ->leftJoin("$city_table c1", "c1.id = $street_table.city")
                ->leftJoin("$region_table r2", "r2.id = c1.region")
                ->where($this->getFiltersWhere($model))
                ->andWhere($indexWhere)
                ->andWhere($ext_where)
                ->orderBy("$street_table.id desc")
                ->all();
        else
            $models = ListStreet::find()->andWhere($indexWhere)->andWhere($ext_where)->orderBy("id desc")->all();

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

        $cityId = $getParams['city'];
        if ($cityId)
            $andWhere = "city = $cityId";

        $result_id_txt = [];
        $result = ListStreet::getList('name', true, $lang, $andWhere);
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id' => $key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

    public function actionGetStreetType() {

        $getParams = Yii::$app->getRequest()->get();
        $id = $getParams['street'];
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        if (!$id)
            return '';

        $street = $this->findModel($id);
        return json_encode($street->streetTypeModel->{"name_$lang"});
    }
}

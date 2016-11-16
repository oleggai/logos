<?php

namespace app\controllers\dictionaries;

use app\models\dictionaries\address\ListBuilding;
use app\models\dictionaries\warehouse\ListWarehouseScheduleReception;
use app\models\dictionaries\warehouse\ListWarehouseScheduleType;
use app\models\dictionaries\warehouse\ListWarehouseZone;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\warehouse\ListWarehouse;
use app\models\dictionaries\address\ListBuildingType;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\address\ListStreet;
use app\models\dictionaries\employee\Employee;
use app\models\common\CommonModel;

/**
 * Контроллер подразделений
 */
class WarehouseController extends CommonController
{
    /**
     * Lists all ListBuilding models.
     * @return mixed
     */
    public function actionIndex()
    {
        $city = Yii::$app->getRequest()->get()['city'];
        return $this->render('grid', ['model' => new ListWarehouse(), 'city'=>$city]);
    }

    /**
     * Creates a new ListBuilding model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListWarehouse();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Updates an existing ListBuilding model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    /*public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('form', ['model' => $model]);
        }
    }*/

    /**
     * Finds the ListBuilding model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListBuilding the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListWarehouse::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionGetTable($afilters = []) {

        $getParams = Yii::$app->getRequest()->get();
        $city = $getParams['city'];
        $ext_where = '';
        if ($city)
            $ext_where = "city = $city";

        $warehouseTbl = ListWarehouse::tableName();
        $streetTbl = ListStreet::tableName();
        $cityTbl = ListCity::tableName();
        $regionTbl = ListRegion::tableName();
        $scheduleTbl = ListWarehouseScheduleReception::tableName();

        $result = [];
        $model = new ListWarehouse();

        $filter = $this->getFiltersWhere($model);

        if ($filter || $afilters) {
            $ids = (new Query())->select("$warehouseTbl.id")
                ->distinct()
                ->from($warehouseTbl)
                ->leftJoin("$streetTbl st", "st.id = $warehouseTbl.street")
                ->leftJoin("$cityTbl ct", "ct.id = st.city")
                ->leftJoin("$regionTbl r2", "r2.id = ct.region")
                ->leftJoin("$scheduleTbl sch", "sch.warehouse = $warehouseTbl.id")
                ->where($filter)
                ->andWhere($ext_where)
                ->andWhere($afilters)
                ->orderBy("$warehouseTbl.id desc")
                ->all();
            $models = ListWarehouse::find()->where(['in', 'id', ArrayHelper::map($ids, 'id', 'id')])->orderBy('id desc')->all();
        }
        else
            $models = ListWarehouse::find()->where($ext_where)->orderBy("id desc")->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }

    /**
     * Получение данных для табличного отображения с расширенным фильтром
     * @return string Данные в формате json
     */
    public function actionGetATable() {

        $model = new ListWarehouse();
        $filter = $this->getFiltersWhere($model, $model->afilters);
        $getParams = Yii::$app->getRequest()->get();
        $field = $getParams['af_dimension_type'];
        $val = $getParams['af_dimension_value'];
        if ($field && $val) {
            $filter .= (($filter) ? ' AND ' : '') . "($field >= $val)";
        }

        return $this->actionGetTable($filter);
    }

    public function actionGetRelatedData() {

        $result = [];

        $getParams = Yii::$app->getRequest()->get();
        $streetId = $getParams['street'];
        $buildingType1Id = $getParams['buildingtype1'];
        $buildingType2Id = $getParams['buildingtype2'];
        $buildingType3Id = $getParams['buildingtype3'];

        if (!$streetId)
            return json_encode($result);

        $street = ListStreet::findOne(['id' => $streetId]);
        if (!$street)
            return json_encode($result);

        $result = [
            'city_en' => $street->cityModel->name_en,
            'city_uk' => $street->cityModel->name_uk,
            'city_ru' => $street->cityModel->name_ru,

            'city_type_en' => $street->cityModel->cityType->name_short_en,
            'city_type_uk' => $street->cityModel->cityType->name_short_uk,
            'city_type_ru' => $street->cityModel->cityType->name_short_ru,

            'region1_en' => $street->cityModel->regionModel->parent->name_en,
            'region1_uk' => $street->cityModel->regionModel->parent->name_uk,
            'region1_ru' => $street->cityModel->regionModel->parent->name_ru,

            'region1_type_en' => $street->cityModel->regionModel->parent->regionType->name_short_en,
            'region1_type_uk' => $street->cityModel->regionModel->parent->regionType->name_short_uk,
            'region1_type_ru' => $street->cityModel->regionModel->parent->regionType->name_short_ru,

            'region2_en' => $street->cityModel->regionModel->name_en,
            'region2_uk' => $street->cityModel->regionModel->name_uk,
            'region2_ru' => $street->cityModel->regionModel->name_ru,

            'region2_type_en' => $street->cityModel->regionModel->regionType->name_short_en,
            'region2_type_uk' => $street->cityModel->regionModel->regionType->name_short_uk,
            'region2_type_ru' => $street->cityModel->regionModel->regionType->name_short_ru,

            'country_en' => $street->cityModel->regionModel->parent->countryModel->nameShortEn,
            'country_uk' => $street->cityModel->regionModel->parent->countryModel->nameShortUk,
            'country_ru' => $street->cityModel->regionModel->parent->countryModel->nameShortRu,

            'street_en' => $street->name_en,
            'street_uk' => $street->name_uk,
            'street_ru' => $street->name_ru,

            'street_type_en' => $street->streetTypeModel->name_short_en,
            'street_type_uk' => $street->streetTypeModel->name_short_uk,
            'street_type_ru' => $street->streetTypeModel->name_short_ru,

        ];

        $buildingType = ListBuildingType::findOne(['id' => $buildingType1Id]);
        if (!$buildingType)
            return json_encode($result);

        $result = $result + [
                'buildingtype1_en' => $buildingType->name_short_en,
                'buildingtype1_uk' => $buildingType->name_short_uk,
                'buildingtype1_ru' => $buildingType->name_short_ru,
            ];

        $buildingType = ListBuildingType::findOne(['id' => $buildingType2Id]);
        if (!$buildingType)
            return json_encode($result);

        $result = $result + [
                'buildingtype2_en' => $buildingType->name_short_en,
                'buildingtype2_uk' => $buildingType->name_short_uk,
                'buildingtype2_ru' => $buildingType->name_short_ru,
            ];

        $buildingType = ListBuildingType::findOne(['id' => $buildingType3Id]);
        if (!$buildingType)
            return json_encode($result);

        $result = $result + [
                'buildingtype3_en' => $buildingType->name_short_en,
                'buildingtype3_uk' => $buildingType->name_short_uk,
                'buildingtype3_ru' => $buildingType->name_short_ru,
            ];

        return json_encode($result);
    }

    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];
        if (!$lang)
            $lang = Yii::$app->language;

        $andWhere = '';
        $cityId = $getParams['city'];
        // формат ответа: 1 - для Select2, 2 - для select
        $format = $getParams['format'];
        if (!$format)
            $format = 1;
        if ($cityId)
            $andWhere = "city = $cityId";

        $result_id_txt = [];
        $result = ListWarehouse::getList('name', true, $lang, $andWhere);

        foreach ($result as $key => $val) {
            if ($format == 1)
                $result_id_txt[] = ['id' => $key, 'txt' => $val];
            else
                $result_id_txt[$key] = $val;
        }
        return json_encode($result_id_txt);
    }
    
    public function actionGetWarehouseByEmployee() {
        
        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        $result_warehouses = [];

        $resultEmployees = Employee::find()
                ->where('visible = :visible AND state != :state', [':visible' => CommonModel::VISIBLE, ':state' => CommonModel::STATE_DELETED])
                ->andWhere('warehouse_id is not null')

                ->all();

        if (!empty($resultEmployees))
            foreach ($resultEmployees as $employee) {
                //if (!empty($employee->warehouseModel)) {
                    $result_warehouses[$employee->id] = [
                        'id' => $employee->id,
                        'name' => $employee->getName($lang),
                        'warehouse_id' => $employee->warehouse_id,
                        'warehouse_name' => $employee->warehouseModel->getName($lang),
                    ];
                //}
            }
        return json_encode($result_warehouses);
    }

    public function actionGetWarehouseByEmployee2() {

        $getParams = Yii::$app->getRequest()->get();
        $employeeTable = Employee::tableName();
        $lang = $getParams['lang'];
        if (!$lang)
            $lang = Yii::$app->language;

        $andWhere = '';
        $employee = $getParams['employee'];
        // формат ответа: 1 - для Select2, 2 - для select
        $format = $getParams['format'];
        if (!$format)
            $format = 1;
        if ($employee)
            $andWhere = "id = (select warehouse_id from $employeeTable where $employeeTable.id = $employee)";

        $result_id_txt = [];
        $result = ListWarehouse::getList('name', true, $lang, $andWhere);

        foreach ($result as $key => $val) {
            if ($format == 1)
                $result_id_txt[] = ['id' => $key, 'txt' => $val];
            else
                $result_id_txt[$key] = $val;
        }
        return json_encode($result_id_txt);
    }

    /**
     * Получение массива полей склада
     * @return string Данные в формате json
     */
    public function actionGetWarehouse($id){
        if (($model = ListWarehouse::findOne($id)) == null) {
            return json_encode(null);
        }

        return json_encode($model->toJsonForAddress());
    }

}

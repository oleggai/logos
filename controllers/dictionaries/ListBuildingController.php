<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\dictionaries\address\ListBuilding;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\address\ListRegion;
use app\models\dictionaries\address\ListStreet;
use app\controllers\CommonController;

/**
 * Контроллер строений
 */
class ListBuildingController extends CommonController
{
    /**
     * Список всех ListBuilding моделей
     * @return mixed текст контента страницы
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new ListBuilding()]);
    }

    /**
     * Создание ListBuilding модели
     * @return mixed текст контента страницы
     */
    public function actionCreate()
    {
        $model = new ListBuilding();

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
     * @param string $id идетификатор строения
     * @return ListBuilding загруженная модель строения
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = ListBuilding::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetTable() {

        $building_table = ListBuilding::tableName();
        $street_table = ListStreet::tableName();
        $city_table = ListCity::tableName();
        $region_table = ListRegion::tableName();

        $result = [];
        $model = new ListBuilding();

        $filter = $this->getFiltersWhere($model);

        if ($filter)
            $models = ListBuilding::find()
                ->leftJoin("$street_table s", "s.id = $building_table.street")
                ->leftJoin("$city_table c", "c.id = s.city")
                ->leftJoin("$region_table r2", "r2.id = c.region")
                ->where($filter)
                ->orderBy("$building_table.id desc")
                ->all();
        else
            $models = ListBuilding::find()->orderBy("id desc")->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }
}

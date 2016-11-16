<?php

namespace app\controllers\dictionaries;

use app\models\common\CommonModel;
use app\models\dictionaries\address\ListCity;
use app\models\dictionaries\warehouse\ListWarehouse;
use Yii;
use app\models\dictionaries\employee\Employee;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;

/**
 * Контроллер сотрудников
 * @author Richok FG
 * @category employee
 */
class EmployeeController extends CommonController
{
    /**
     * Начальная инициализация контроллера
     */
    public function init()
    {
        $this->accessCheck = 'employee'; // имя сущности для проверки
        parent::init();
    }

    /**
     * Список всех Employee моделей
     * @return mixed текст контента страницы
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new Employee()]);
    }

    /**
     * Создание Employee модели
     * @return mixed текст контента страницы
     */
    public function actionCreate()
    {
        $model = new Employee();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id идентификатор сотрудника
     * @return Employee загруженная модель сотрудника
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = Employee::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetTable()
    {

        $model = new Employee();
        $result = array();
        $filter = $this->getFiltersWhere($model);

        if ($filter) {
            $models = Employee::find()
                ->leftJoin('{{%employee_translate}}', 'employee_id=id')
                ->where($filter)
                ->orderBy('id desc');
        } else {
            $models = Employee::find()
                ->orderBy('id desc');
        }

        return json_encode(CommonModel::getDataWithLimits($models));
    }

    /**
     * Получение данных журнала событий
     * @return string Данные в формате json
     */
    public function actionGetLogs()
    {
        $getParams = Yii::$app->getRequest()->get();
        $f_employee_id = $getParams['f_employee_id'];

        if (!$f_employee_id)
            return;

        $result = [];
        $model = Employee::findOne(['id' => $f_employee_id]);
        if ($model) {
            $logs = $model->logs;
            foreach ($logs as $log)
                $result[] = $log->toJson();
        }

        return json_encode($result);
    }

    /**
     * Получение списка сотрудников в формате ключ - id, значение - name_short
     * @return string Данные в формате json
     */
    public function actionGetList($lang = '')
    {

        $getParams = Yii::$app->getRequest()->get();

        if (empty($lang))
            $lang = $getParams['lang'];


        if ($getParams['warehouse']) {
            $andWhere = 'warehouse_id = ' . (int)$getParams['warehouse'];
        } else if ($getParams['city']) {
            $andWhere = 'city_id = ' . (int)$getParams['city'];
        } else if ($getParams['country']) {
            $andWhere = 'country_id = ' . (int)$getParams['country'];
        } else $andWhere = '1=1';
        $result_id_txt = [];

        $result = Employee::getList('surnameFull' . ucfirst($lang), true, $andWhere);

        foreach ($result as $key => $val)
            $result_id_txt[] = ['id' => $key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

    public function actionGetAddress()
    {
        $getParams = Yii::$app->getRequest()->get();

        $employee_id = $getParams['employee'];
        $empoyee = Employee::findOne($employee_id);
        if ($employee_id)
            return json_encode([
                'country' => $empoyee->country_id,
                'warehouse' => $empoyee->warehouse_id,
                'city' => $empoyee->city_id,
                'employee' => $employee_id,
            ]);

        $warehouse_id = $getParams['warehouse'];
        $warehouse = ListWarehouse::findOne($warehouse_id);
        if ($warehouse_id)
            return json_encode([
                'country' => $warehouse->cityModel->regionModel->country,
                'warehouse' => $warehouse->id,
                'city' => $warehouse->city,
            ]);

        $city_id = $getParams['city'];
        $city = ListCity::findOne($city_id);
        if ($city_id)
            return json_encode([
                'country' => $city->regionModel->country,
                'city' => $city->id,
            ]);

        return json_encode([]);
    }
}
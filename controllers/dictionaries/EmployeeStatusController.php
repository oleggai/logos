<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\employee\EmployeeStatus;

/**
 * Контроллер Статусов сотрудника
 * @uathor Richok FG
 * @category employee
 */
class EmployeeStatusController extends CommonController
{
    /**
     * Начальная инициализация контроллера
     */
    public function init(){

        $this->accessCheck = 'employeeStatus'; // имя сущности для проверки
        parent::init();
    }

    /**
     * Список всех EmployeeStatus моделей
     * @return mixed текст контента страницы
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new EmployeeStatus()]);
    }

    /**
     * Создание EmployeeStatus модели
     * @return mixed текст контента страницы
     */
    public function actionCreate()
    {
        $model = new EmployeeStatus();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model,]);
        }
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id идентификатор статуса
     * @return EmployeeStatus загруженная модель статуса сотрудника
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = EmployeeStatus::findOne($id)) !== null) {
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
        $models = EmployeeStatus::find()->orderBy('id desc')->all();
        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }

    /**
     * Получение списка статусов в формате ключ - id, значение - name_short
     * @return string Данные в формате json
     */
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result_id_txt = [];
        $result = EmployeeStatus::getList('name'.$lang,true);
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id'=>$key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

}

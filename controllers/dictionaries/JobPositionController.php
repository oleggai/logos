<?php

namespace app\controllers\dictionaries;

use Yii;
use app\models\dictionaries\employee\JobPosition;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;

/**
 * Контроллер должностей
 * @author Richok FG
 * @category employee
 */
class JobPositionController extends CommonController
{
    /**
     * Начальная инициализация контроллера
     */
    public function init(){

        $this->accessCheck = 'jobPosition'; // имя сущности для проверки
        parent::init();
    }

    /**
     * Список всех JobPosition моделей
     * @return mixed текст контента страницы
     */
    public function actionIndex($type = 0)
    {
        return $this->render('grid', ['model' => new JobPosition(), 'type' => $type]);
    }

    /**
     * Создание JobPosition модели
     * @return mixed текст контента страницы
     */
    public function actionCreate()
    {
        $model = new JobPosition();

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
     * @param string $id идетификатор должности
     * @return JobPosition загруженная модель дожности
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = JobPosition::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Получение данных для табличного отображения
     * @param int $type 0-все, 1-должности для сотрудников, 2-должности для контактных лиц
     * @return string Данные в формате json
     */
    public function actionGetTable($type = 0){

        $result = [];
        $model = new JobPosition();
        $where = '';
        if ($type == 1)
            $where = 'for_employee = 1';
        if ($type == 2)
            $where = 'for_counterparty = 1';

        $models = JobPosition::find()
            ->leftJoin('{{%job_position_translate}}', 'job_position_id=id')
            ->where($this->getFiltersWhere($model))
            ->andWhere($where)
            ->orderBy('id desc')
            ->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }

    /**
     * Получение списка должностей в формате ключ - id, значение - name_short
     * @return string Данные в формате json
     */
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result_id_txt = [];
        $result = JobPosition::getList('name'.$lang,true);
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id'=>$key, 'txt' => $val];
        return json_encode($result_id_txt);
    }
}

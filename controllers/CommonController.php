<?php

/**
 * Created by PhpStorm.
 * User: goga
 * Date: 23.03.2015
 * Time: 9:55
 */

namespace app\controllers;

use app\models\common\CommonModel;
use app\models\common\DateFormatBehavior;
use app\models\common\sys\SysEntity;
use app\models\ew\ExpressWaybill;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;


/**
 * Общий контроллер. Содержит базовые алгоритмы работы для всех контроллеров
 * @property array checkActions Список проверяемых действий
 * @package app\controllers
 */
class CommonController extends Controller {

    /**
     * @var string Имя для проверки прав
     */
    public $accessCheck = null;

    /**
     * Получить имя формы. Если имя не стандартное, указать в своем контроллере путем переопределения метода
     * @return string
     */
    protected function getForm() {
        return 'form';
    }

    /**
     * Поведения контроллера
     * @return array массив поведений
     */
    /*    public function behaviors()
      {
      return [
      'verbs' => [
      'class' => VerbFilter::className(),
      'actions' => ['delete' => ['post']],
      ],
      ];
      } */

    /**
     * Метод перед любым дейсвием. Дополнен проверкой прав на это действие
     * @param \yii\base\Action $action
     * @return bool
     * @throws ForbiddenHttpException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action) {

        if (parent::beforeAction($action)) {

            return $this->checkAccess($this->accessCheck, $action);
        }

        return false;
    }

    /**
     * общий экшн удаления сущности
     * @param $id int ид сущности
     * @return \yii\web\Response
     */
    public function actionDelete($id) {

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {

            //$model->state = CommonModel::STATE_DELETED;
            $model->operation = CommonModel::OPERATION_DELETE;
            if ($model->save(false)) {

                $getParams = Yii::$app->getRequest()->get();
                if ($getParams['current_operation'] == CommonModel::OPERATION_VIEW)
                    return $this->redirect(['view', 'id' => $model->id]);

                if ($getParams['current_operation'] == CommonModel::OPERATION_GRIDVIEW)
                    return json_encode('item.state = ' . $model->state . ';item.stateText="' . $model->stateText . '"');

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)) {
            return $this->redirect(['view', 'id' => $id, 'errors' => $model->errors]);
        }

        $model->operation = CommonModel::OPERATION_DELETE;
        SysEntity::saveOperation($model->getEntityCode(), $id, CommonModel::OPERATION_BEGIN_DELETE);
        return $this->render($this->getForm(), ['model' => $model,]);
    }

    /**
     * общий экшн удаления сущности без формы (например, с грида)
     * @param $id int ид сущности
     * @return \yii\web\Response
     */
    public function actionDeleteNoForm($id) {
        $model = $this->findModel($id);

        //$model->state = CommonModel::STATE_DELETED;
        $model->operation = CommonModel::OPERATION_DELETE;
        $model->save(false);
    }

    /**
     * общий экнш восстановления сущности
     * @param $id int ид сущности
     * @return \yii\web\Response
     */
    public function actionRestore($id) {

        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {

            $getParams = Yii::$app->getRequest()->get();
            if ($getParams['current_operation'] == CommonModel::OPERATION_VIEW)
                return $this->redirect(['view', 'id' => $model->id]);

            if ($getParams['current_operation'] == CommonModel::OPERATION_GRIDVIEW)
                return json_encode('item.state = ' . $model->state . ';item.stateText="' . $model->stateText . '"');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)) {
            return $this->redirect(['view', 'id' => $id, 'errors' => $model->errors]);
        }

        $model->operation = CommonModel::OPERATION_CANCEL;
        SysEntity::saveOperation($model->getEntityCode(), $id, CommonModel::OPERATION_BEGIN_CANCEL);
        return $this->render($this->getForm(), ['model' => $model,]);
    }

    /**
     * общий экнш восстановления сущности
     * @param $id int ид сущности
     * @return \yii\web\Response
     */
    public function actionRestoreNoForm($id) {
        $model = $this->findModel($id);

        //$model->state = CommonModel::STATE_CREATED;
        $model->operation = CommonModel::OPERATION_CANCEL;
        $model->save(false);
    }

    /**
     * общий экнш просмотра сущности
     * @param $id int ид сущности
     * @param $entityName
     * @param $entityId
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionView($id, $entityName = '', $entityId = '') {

        $model = $this->findModel($id);

        $getParams = Yii::$app->getRequest()->get();

        if($entityName && $entityId) {
            $model->entityName = $entityName;
            $model->entityId = $entityId;
        }
        if ($getParams['errors']) {
            $model->addErrors($getParams['errors']);
        }


        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            return $this->redirect(['update', 'id' => $model->id,
                'entityName' => $entityName,
                'entityId' => $entityId]);
        } else {


            $model->operation = CommonModel::OPERATION_VIEW;

            SysEntity::saveOperation($model->getEntityCode(), $id, CommonModel::OPERATION_VIEW);

            return $this->render($this->getForm(), ['model' => $model,]);
        }
    }

    /**
     * Обновление модели.
     * @param string $id
     * @param $entityName
     * @param $entityId
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id, $entityName = '', $entityId = '') {
        $model = $this->findModel($id);

        if($entityName && $entityId) {
            $model->entityName = $entityName;
            $model->entityId = $entityId;
        }


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $id,
                'entityName' => $entityName,
                'entityId' => $entityId]);
        } else {

            if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)) {
                return $this->redirect(['view', 'id' => $id, 'errors' => $model->errors,
                    'entityName' => $entityName,
                    'entityId' => $entityId
                ]);
            }

            $model->operation = CommonModel::OPERATION_UPDATE;
            SysEntity::saveOperation($model->getEntityCode(), $id, CommonModel::OPERATION_BEGIN_UPDATE);
            return $this->render($this->getForm(), ['model' => $model,]);
        }
    }
    
    public function actionGetBooleans(){
        $request = Yii::$app->request->get();
        $lang = $request['lang'];
        $result = (CommonModel::getBooleans(true, $lang));

        foreach ($result as $key => $val)
            $result_id_txt[] = ['id' => $key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

    /**
     * Проверка прав
     * @param $entity string Сущность, для которой проверяется право
     * @param $action Action Выполняемое дейсвие
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function checkAccess($entity, $action) {

        // гость может только производить логин и смотреть ошибки
        if (Yii::$app->user->isGuest &&
                $action->id != 'login' &&
                $action->id != 'error') {

            Yii::$app->user->loginRequired();
            return false;
        }

        if ($entity == null)
            return true;

        // проверка только заданных дейтвсий
        if (!in_array($action->id, $this->checkActions))
            return true;

        if (!Yii::$app->user->identity->checkAccess($entity, $action->id))
            throw new ForbiddenHttpException(Yii::t('app', 'Access denied!'));

        return true;
    }

    /**
     * Список проверяемых дейсвий контроллера
     * @return array
     */
    function getCheckActions() {
        return ['index', 'create', 'update', 'delete'];
    }

    /**
     * @param $model CommonModel Модель
     * @param $filters
     * @param null $request
     * @return array
     */
    protected function getFiltersWhere($model, $filters = null, $request = null) {

        if (!$filters)
            $filters = $model->filters;

        if (!$filters)
            return null;

        if (!$request)
            $request = Yii::$app->getRequest();

        $lang_selectror = null;
        $dateBehavior = new DateFormatBehavior();
        $getParams = $request->get();
        $where = [];

        foreach ($filters as $filter) {

            if ($filter['lang_selector'])
                $lang_selectror = $getParams[$filter['id']];

            if (!$filter['field'])
                continue;

            $filter_input = $getParams[$filter['id']];
            if ($filter_input === null || $filter_input === '')
                continue;

            // SQL injection fix
            $filter_input = str_replace("'", '"', $filter_input);

            $filter_val = "'$filter_input'";
            if ($filter['operation'] == 'like')
                $filter_val = "'%$filter_input%'";
            else if ($filter['operation'] == 'starts') {
                $filter['operation'] = 'like';
                $filter_val = "'$filter_input%'";
            }
            else if ($filter['operation'] == 'in') {
                $filter_val = "('" . str_replace(",", "','", $filter_input) . "')";
            }

            if ($filter['type'] == CommonModel::FILTER_DATETIME)
                $filter_val = "'" . $dateBehavior->convertToStoredFormat($filter_input) . "'";

            if ($filter['operation'] == 'exists') {
                $filter_val = '(' . str_replace( "~exists_input_val~", $filter_val, $filter['field']) . ')';
                $filter['field'] = '';
            }

            //if ($filter['type'] == CommonModel::FILTER_CHECKBOX)
            //    $filter_val = ($filter_input == 'true') ? 1 : 0;

            $sub_where = [];
            $fields = is_array($filter['field'])? $filter['field'] : [$filter['field']];
            foreach ($fields as $field) {

                if ($lang_selectror && $filter['lang_field'])
                    $field = $field . '_' . $lang_selectror;

                $sub_where[] = $field . ' ' . $filter['operation'] . ' ' . $filter_val;
            }

            $where[] = ' ( '. implode(' or ',$sub_where). ' )';
        }

        return implode(' and ', $where);
    }


    /**
     * Получение условия для выборки в виде строки "in (1,2,3)" или "not in (1,2,3)"
     * Используюется совместно с GridWidget->show_checkboxes
     * @param CommonModel $model модель, используется для получения фильтров
     * @param string $param_name имя параметра где сохранен результат выбора
     * @param null $filters Фильтры используемые для выборки сущностей. По умолчанию берутся из модели
     * @return string
     */
    function getCheckedWhere($model, $param_name, $filters=null){

        $result = '1=2';

        $check_param = is_object($param_name) ? $param_name : json_decode(Yii::$app->request->get()[$param_name]);

        if ($check_param){

            $operation = 'in';
            $items = [-1];

            if ($check_param->master){
                $operation = 'not in';
                $items = array_merge($check_param->unchecked, $items);
            }
            else{
                $items = array_merge($check_param->checked, $items);
            }

            $result = $model->tableName() . '.' . $check_param->item_identificator . ' ' . $operation . ' (' . implode(', ', $items) . ')' ;


            $get_string = parse_url($check_param->filter_url)['query'];
            parse_str($get_string, $get_array);
            $request = new Request();
            $request->setQueryParams($get_array);

            if (!$filters) {
                $filter_where = $this->getFiltersWhere($model, $model->filters, $request);
                if (!$filter_where)
                    $this->getFiltersWhere($model, $model->aFilters, $request);
            }
            else{
                $filter_where = $this->getFiltersWhere($model, $filters, $request);
            }


            if ($filter_where)
                $result .= ' and ' . $filter_where;

        }

        return $result;
    }


    /**
     * Получение данных по параметрам [where, having, with, group] etc
     * @param array $q query parts
     * @param CommonModel $class
     * @return array $result
     */
    public function getTableData($q, $class, $filters) {

        if (!empty($q['select'])) {
            $select = ', ' . implode(', ', $q['select']);
        }

        $query = $class::find()
                ->select([$class::tableName().'.id' . $select])
                ->where($q['filter'])
                ->orderBy($class::tableName().'.date desc');

//        if (is_array($q['where']))
//            foreach ($q['where'] as $table)
//                $query->andWhere($table);
//
//        if (is_array($q['with']))
//            foreach ($q['with'] as $table)
//                $query->joinWith($table);
//
//        if (is_array($q['group']))
//            foreach ($q['group'] as $table)
//                $query->groupBy($table);
//
//        if (is_array($q['having']))
//            foreach ($q['having'] as $table)
//                $query->andHaving($table);

        
        if(!empty($filters)&&is_array($filters))
            foreach ($filters as $f) {
                if (!empty($f['join']))
                    foreach ($f['join'] as $join)
                        if (method_exists($query, $join['type']))
                            $query->$join['type']($join['from'], $join['on']);
                if (!empty($f['andWhere']))
                    $query->andWhere($f['andWhere']);
            }

//        die($query->createCommand()->rawSql);

        return ($query) ? $query->asArray(true)->all() : [];
    }    

    /**
     * Поиск модели по первичному ключу.
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id
     * @return CommonModel найденая модель
     * @throws NotFoundHttpException в случае неудачного поиска
     */
    protected function findModel($id) {
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function actionGetField($class, $id, $field, $operation = null){

        $result = [];

        $instance = $class::findOne($id);

        if ($instance && $instance->{$field}) {
            foreach ($instance->{$field} as $item) {
                if ($operation !== null) {
                    $item->setOperation($operation);
                }
                $json = $item->toJson();

                $result[] = $json;
            }
        }

        return json_encode($result);

    }

    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result = \app\models\ew\EwState::getList(true, 'name', $lang);

        // формат ответа: 1 - для Select2, 2 - для select
        $format = $getParams['format'];
        if (!$format)
            $format = 1;

        foreach ($result as $key => $val) if($key!=CommonModel::STATE_CLOSED) {
            if ($format == 1)
                $result_id_txt[] = ['id' => $key, 'txt' => $val];
            else
                $result_id_txt[$key] = $val;
        }
        return json_encode($result_id_txt);
    }


}

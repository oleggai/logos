<?php

/**
 * В файле описан контроллера статуса треккинга
 *
 * @author Мельник И.А.
 * @category Треккинг статусы
 */

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\ew\ListStatusesEw;

/**
 * Контроллер статуса треккинга
 */
class ListStatusesEwController extends CommonController
{

    /**
     * Метод отображения списка статусов
     * @return mixed Строка для отображения
     */
    public function actionIndex()
    {
        //$searchModel = new ListStatusesEwSearch();
        return $this->render('grid', ['model' => new ListStatusesEw()]);
    }


    /**
     * Создание нового статуса
     * @return mixed При удачном создании перенаправление на страницу редактирования
     */
    public function actionCreate()
    {
        $model = new ListStatusesEw();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

            $parentRef = null;
            $getParams = Yii::$app->getRequest()->get();
            if (array_key_exists('parent_ref',$getParams))
                $parentRef = $getParams['parent_ref'];

            $model->generateDefaults($getParams, $parentRef);
            return $this->render('form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Удаление статуса
     * @param string $id Идентификатор статуса
     * @return mixed Страница со списком всех статусов
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['update', 'id' => $id]);
    }*/

    /**
     * Поиск ListStatusesEw модели по первичному ключу.
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id Идентификатор статуса
     * @return ListStatusesEw найденая модель
     * @throws NotFoundHttpException в случае неудачного поиска
     */
    protected function findModel($id)
    {
        if (($model = ListStatusesEw::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
    }

    public function actionGetTable(){

        $result = array();

        $parentRef = null;
        $getParams = Yii::$app->getRequest()->get();
        if (array_key_exists('use_hierarchy', $getParams)) { //это фильтрация
            $use_hierarchy = $getParams['use_hierarchy'];
            if ($use_hierarchy == 'true')
                $mode = 0;
            else
                $mode = 2;
        }
        else {
            $mode = $getParams['item_mode'];
        }

        if (array_key_exists('parent_ref',$getParams)) {
            $parentRef = $getParams['parent_ref'];
            if ($parentRef == '')
                $parentRef = null;
            if ($mode=="1")
                $parentRef = $this->findModel($parentRef)->parent_id;
            elseif ($mode == "2")
                $parentRef = null;

            $parent = $parentRef;

            while ($parent>0) {
                $parentModel = $this->findModel($parent);
                array_unshift($result, array_merge(($parentModel->toJson()), ['itemmode' => 1]));
                $parent = $parentModel->parent_id;
            }
        }

        $where = null;
        if ($mode!='2') $where['parent_id'] = $parentRef;

        $model = new ListStatusesEw();
        $filter = $this->getFiltersWhere($model);
        if ($filter == null)
            $models = ListStatusesEw::find()->where($where)->all();
        else {
            if ($where == null)
                $where= [];
            $models = ListStatusesEw::find()
                ->leftJoin('{{%list_statuses_ew_translate}}', 'id=status_ew_id')
                ->where($filter)->andWhere($where)->all();
        }

        foreach($models as $model)
            $result[] = array_merge(($model->toJson()), ['itemmode' => 0]);

        return json_encode($result);
    }

    function actionGetStatusByCode() {

        $result = [];

        $getParams = Yii::$app->getRequest()->get();
        $code = $getParams['status_code'];

        if (!$code)
            return json_encode($result);

        $status = ListStatusesEw::findOne(['code' => $code]);
       if (!$status)
            return json_encode($result);

        $result = [
            'id' => $status->id,
            'name' => $status->nameFull,
            'type' => $status->type,
            'type_str' => $status->typeStr,
            'inner' => $status->inner,
            'inner_str' => $status->getInnerList()[$status->inner]
        ];

        return json_encode($result);
    }

    function actionGetStatusById() {
        $result = [];

        $getParams = Yii::$app->getRequest()->get();
        $id = $getParams['id'];

        if (!$id)
            return json_encode($result);

        $status = $this->findModel($id);
        if (!$status)
            return json_encode($result);

        $result = [
            'code' => $status->code,
            'type' => $status->type,
            'type_str' => $status->typeStr,
            'inner' => $status->inner,
            'inner_str' => $status->getInnerList()[$status->inner]
        ];

        return json_encode($result);
    }

    /**
     * Получение пар ключ - значение списка типов для формы
     * @param string $field
     * @return string json
     */
    public function actionGetList($field = 'title_short') {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result = ListStatusesEw::getList($field, true, $lang);

        // формат ответа: 1 - для Select2, 2 - для select
        $format = $getParams['format'];
        if (!$format)
            $format = 1;

        foreach ($result as $key => $val) {
            if ($format == 1)
                $result_id_txt[] = ['id' => $key, 'txt' => $val];
            else
                $result_id_txt[$key] = $val;
        }
        return json_encode($result_id_txt);
    }

}

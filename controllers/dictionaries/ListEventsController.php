<?php
/**
 * В файле описан контроллер действий над событием
 *
 * @author Мельник И.А.
 * @category События
 */

namespace app\controllers\dictionaries;

use Yii;
use app\controllers\CommonController;
use yii\web\NotFoundHttpException;
use app\models\dictionaries\events\ListEvents;

/**
 * Контроллер событий
 */
class ListEventsController extends CommonController
{

    /**
     * Отображение списка событий
     * @return mixed Страница с таблицей событий
     */
    public function actionIndex()
    {
        // temp инициализация событий по умолчанию. убрать после заполнения
        //Event::initStdCodes();

        return $this->render('grid', ['model' => new ListEvents()]);
    }

    /**
     * Создание нового события
     * @return mixed Форма создания события, либо форма редактирования
     */
    public function actionCreate()
    {
        $model = new ListEvents();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

            $parentRef = null;
            $getParams = Yii::$app->getRequest()->get();
            if (array_key_exists('parent_ref',$getParams))
                $parentRef = $getParams['parent_ref'];

            $model->generateDefaults($getParams, $parentRef);

            return $this->render('form', ['model' => $model,]);
        }
    }

    /**
     * Удаление события
     * @param string $id
     * @return mixed
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['update', 'id' => $id]);
    }*/

    /**
     * Поиск события по идентификатору
     * @param string $id Идентификатор искомого события
     * @return ListEvents Найденные события
     * @throws NotFoundHttpException Исключение в случае не успешного поиска
     */
    protected function findModel($id)
    {
        if (($model = ListEvents::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('events','The requested page does not exist.'));
        }
    }


    /**
     * Получение данных для табличного вида
     * @return string
     * @throws NotFoundHttpException
     */
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
            if ($mode == "1")
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
        if ($mode!="2")
            $where[ListEvents::tableName().".parent_id"] = $parentRef;

        $model = new ListEvents();
        $filter = $this->getFiltersWhere($model);
        if ($filter == null)
            $models = ListEvents::find()->where($where)->all();
        else {
            if ($where == null)
                $where= [];
            $models = ListEvents::find()
                ->leftJoin('{{%list_events_translate}} tr_s', ListEvents::tableName() . '.id=tr_s.list_events_id')
                ->leftJoin('{{%list_events}} p', ListEvents::tableName() . '.parent_id=p.id')
                ->leftJoin('{{%list_events_translate}} tr_p', 'p.id=tr_p.list_events_id and tr_s.lang=tr_p.lang')
                ->where($filter)->andWhere($where)->all();
        }

        foreach($models as $model)
            $result[] = array_merge(($model->toJson()), ['itemmode' => 0]);

        return json_encode($result);
    }
}

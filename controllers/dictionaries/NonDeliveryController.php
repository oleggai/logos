<?php

namespace app\controllers\dictionaries;

use app\models\common\CommonModel;
use Yii;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\nondelivery\NonDelivery;
use app\models\dictionaries\access\User;

/**
 * Контроллер причин недоставки
 * @author Richok F.G.
 * @category nondelivery
 */
class NonDeliveryController extends CommonController
{
    /**
     * Метод отображения списка причин недоставки
     * @return mixed Строка для отображения
     */
    public function actionIndex()
    {
        $searchModel = new NonDelivery();
        return $this->render('grid', ['model' => $searchModel]);
    }

    /**
     * Создание новой причины недоставки
     * @return mixed При удачном создании перенаправление на страницу редактирования
     */
    public function actionCreate()
    {
        $model = new NonDelivery();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $parentRef = null;
            $getParams = Yii::$app->getRequest()->get();
            if (array_key_exists('parent_ref', $getParams))
                $parentRef = $getParams['parent_ref'];

            $model->generateDefaults($getParams, $parentRef);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Удаление причины недоставки
     * @param string $id Идентификатор причины
     * @return mixed Страница со списком всех причин
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['update', 'id' => $id]);
    }*/

    /**
     * Поиск NonDelivery модели по первичному ключу.
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id Идентификатор причны недоставки
     * @return NonDelivery найденая модель
     * @throws NotFoundHttpException в случае неудачного поиска
     */
    protected function findModel($id)
    {
        if (($model = NonDelivery::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Получение
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionGetTable(){
        $result = [];
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

        if (array_key_exists('parent_ref', $getParams)) {
            $parentRef = $getParams['parent_ref'];
            if ($parentRef == '')
                $parentRef = null;
            if ($mode == "1") {
                $parentRef = $this->findModel($parentRef)->parent_id;
            }
            elseif ($mode == "2") {
                $parentRef = null;
            }

            $parent = $parentRef;
            while ($parent != null) {
                $parentModel = $this->findModel($parent);
                array_unshift($result, array_merge(($parentModel->toJson()), ['itemmode' => 1]));
                $parent = $parentModel->parent_id;
            }
        }

        $where = null;
        if ($mode != "2")
            $where[NonDelivery::tableName().".parent_id"] = $parentRef;

        $model = new NonDelivery();
        $filter = $this->getFiltersWhere($model);
        if ($filter == null)
            $models = NonDelivery::find()->where($where)->all();
        else {
            if ($where == null)
                $where= [];
            $models = NonDelivery::find()
                ->leftJoin('{{%list_nondelivery_translate}} tr_s', NonDelivery::tableName() . '.id=tr_s.nondelivery_id')
                ->leftJoin('{{%list_nondelivery}} p', NonDelivery::tableName() . '.parent_id=p.id')
                ->leftJoin('{{%list_nondelivery_translate}} tr_p', 'p.id=tr_p.nondelivery_id and tr_s.lang=tr_p.lang')
                ->where($filter)->andWhere($where)->all();
        }

        foreach($models as $model)
            $result[] = array_merge(($model->toJson()), ['itemmode' => 0]);

        return json_encode($result);
    }

    /**
     * Получение данных журнала событий
     * @return string Данные в формате json
     */
    /*public function actionGetLogs() {
        $getParams = Yii::$app->getRequest()->get();
        $f_nondelivery_id = $getParams['f_nondelivery_id'];

        if (!$f_nondelivery_id)
            return;

        $result = [];
        $model = Country::findOne(['id' => $f_nondelivery_id]);
        if ($model) {
            $logs = $model->logs;
            foreach($logs as $log)
                $result[] = $log->toJson();
        }

        return json_encode($result);
    }*/


    /**
     * Метод для подгузки зон
     * @return string
     */
    public function actionGetListNonDelivery() {

        $lang = Yii::$app->language;
        $ewNonDeliveryJson = [];
        $nonDeliveriesParent = NonDelivery::find()
            ->where(['is', 'parent_id', null])
            ->andWhere('visible = :visible AND state != :state', [':visible' => CommonModel::VISIBLE, ':state' => CommonModel::STATE_DELETED])
            ->all();

        foreach($nonDeliveriesParent as $nonDelivery) {
            $listNonDelivery = NonDelivery::findAll(['parent_id' => $nonDelivery->id]);
            $list = [];
            foreach($listNonDelivery as $nd) {
                $list[] = [
                    ['id' => $nd->id, 'name' => $nd->names[$lang]],
                    ['occ_zone_id' => $nd->occ_zone, 'occ_zone_name' => NonDelivery::getOccZoneList()[$nd->occ_zone]]
                ];
            }
            $ewNonDeliveryJson[$nonDelivery->id] = $list;
        }
        $ewNonDeliveryJson[0] = [[['id' => 0, 'name' => '--'], ['occ_zone_id' => 0,'occ_zone_name' => '--']]];
        return Json::encode($ewNonDeliveryJson);
    }

    /**
     * Метод для получения доп данных причин недоставки/незабору в формате json
     * @return string
     */
    public function actionGetListEwNonDelivery() {

        $user = User::findOne(['user_id' => Yii::$app->user->id]);
        $userJson = [
            'country'         => $user->employee->country->nameOfficial,
            'city'            => $user->employee->city,
            'departament'     => $user->employee->departament,
            'creator_user_id' => $user->id
        ];
        return  Json::encode($userJson);
    }
}

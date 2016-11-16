<?php

namespace app\controllers\common;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\common\MenuItem;
use app\models\common\CommonModel;
use app\models\common\sys\SysEntity;

/**
 * Контроллер пукнтов меню
 */
class MenuItemController extends CommonController
{

    /**
     * Начальная инициализация контроллера
     */
    public function init() {
        $this->accessCheck = 'menu-item'; // имя сущности для проверки
        parent::init();
    }
    
    /**
     * AJAX метод сохранения порядка и иерархии пунктов меню
     */
    public function actionSave()
    {
        foreach(json_decode($_POST['data']) as $item) {

            if ($item->item_id) {
                $db_item = MenuItem::findOne($item->item_id);
                $db_item->lft = $item->left;
                $db_item->rgt = $item->right;
                $db_item->depth = $item->depth;

                $db_item->save();
            }

        }
    }

    /**
     * Вызов перед любым action
     */
    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Список пунктов меню
     * @return mixed
     */
    public function actionIndex()
    {
        $items = MenuItem::find()
            ->orderBy('lft')
            ->all();

        return $this->render('index', [
            'items' => $items,
        ]);
    }

    /**
     * Создание пункта меню
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new MenuItem();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->makeFirst();
            return $this->redirect(['index']);
        } else {
            return $this->render('form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Изменение пункта меню
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('form', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * Удаление пункта меню со всеми дочерними пунктами
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            
            //$model->state = CommonModel::STATE_DELETED;
            $model->operation = CommonModel::OPERATION_DELETE;
            if ($model->save(false)) {
                $model->deleteChildren(); // удалить все дочерние пункты меню
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        
        if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)) {
            return $this->redirect(['view', 'id' => $id, 'errors' => $model->errors]);
        }
        
        $model->operation = CommonModel::OPERATION_DELETE;
        return $this->render($this->getForm(), ['model' => $model]);
    }

    /**
     * Ищет модель MenuItem
     * @param string $id
     * @return MenuItem модель
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = MenuItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

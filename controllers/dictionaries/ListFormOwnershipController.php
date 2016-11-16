<?php

/**
 * Файл класса контроллера ListFormOwnershipController (Формы собственности)
 */

namespace app\controllers\dictionaries;

use app\models\counterparty\ListFormOwnership;
use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;

/**
 * Класс контроллер ListContractTypeController
 * @author Гайдаенко Олег
 * @category dictionary
 */
class ListFormOwnershipController extends CommonController {

    public function actionIndex() {
        return $this->render('grid', ['model' => new ListFormOwnership()]);
    }

    public function actionCreate() {
        $model = new ListFormOwnership();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Получение списка видов документов удостоверяющие личность
     * @return string Данные в формате json
     */
    public function actionGetTable() {
        $result = [];

        $models = ListFormOwnership::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор Вида документа
     * @return ListFormOwnership загруженная модель форм собственности
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = ListFormOwnership::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

<?php

/**
 * Файл класса контроллера ListPersDocType (Виды документов удостоверяющие личность)
 */

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\counterparty\ListPersDocType;

/**
 * Класс контроллер ListPersDocType
 * @author Гайдаенко Олег
 * @category ew
 */
class ListPersDocTypeController extends CommonController {

    public function actionIndex() {
        return $this->render('grid', ['model' => new ListPersDocType()]);
    }

    public function actionCreate() {
        $model = new ListPersDocType();
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
        $model = new ListPersDocType();

        $models = ListPersDocType::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор Вида документа
     * @return ListPersDocType загруженная модель типа услуги
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = ListPersDocType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

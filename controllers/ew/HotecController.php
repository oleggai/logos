<?php
/**
 * Файл контроллера заявок Hotec
 */

namespace app\controllers\ew;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\ew\ListStatementHotec;
use app\controllers\CommonController;

/**
 * Class HotecController контроллер заявлений Hotec
 * @author Гайдаенко Олег
 * @category Hotec
 */
class HotecController extends CommonController {

    public function actionIndex() {
        return $this->render('grid', ['model' => new ListStatementHotec()]);
    }

    /**
     * Метод создания заявки
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new ListStatementHotec();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Метод получения данных грида
     * @return string Данные в формате json
     */
    public function actionGetTable() {
        $result = [];
        $model = new ListStatementHotec();

        $models = ListStatementHotec::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор hotec
     * @return ListStatementHotec загруженная модель курса
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = ListStatementHotec::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

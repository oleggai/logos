<?php
/**
 * Файл класса контроллера Incoterms
 */

namespace app\controllers\ew;

use Yii;
use yii\web\NotFoundHttpException;
use app\models\ew\ListConditionIncoterms;
use app\models\ew\ListStatementHotec;
use app\controllers\CommonController;

/**
 * Класс контроллер Incoterms
 * @author Гайдаенко Олег
 * @category Incoterms
 */
class IncotermsController extends CommonController {

    public function actionIndex() {
        return $this->render('grid', ['model' => new ListConditionIncoterms()]);
    }

    public function actionCreate() {
        $model = new ListConditionIncoterms();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Получение списка инкотермс
     * @return string Данные в формате json
     */
    public function actionGetTable() {
        $result = [];
        $model = new ListConditionIncoterms();

        $models = ListConditionIncoterms::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор инкотермс
     * @return ListStatementHotec загруженная модель курса
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = ListConditionIncoterms::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

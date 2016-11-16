<?php
/**
 * Файл класса контроллера Units
 */

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\ew\Units;

/**
 * Класс контроллер Units
 * @author Гайдаенко Олег
 * @category ew
 */
class UnitsController extends CommonController {

    public function actionIndex() {
        return $this->render('grid', ['model' => new Units()]);
    }

    public function actionCreate() {
        $model = new Units();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $getParams = Yii::$app->getRequest()->get();
            $model->generateDefaults($getParams);
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Получение списка типов услуг
     * @return string Данные в формате json
     */
    public function actionGetTable() {
        $result = [];
        $model = new Units();

        $models = Units::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор инкотермс
     * @return Units загруженная модель
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = Units::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

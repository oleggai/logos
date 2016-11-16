<?php
/**
 * Файл класса контроллера ServiceTypeList
 */

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\ew\ListServiceType;

/**
 * Класс контроллер Service Type List
 * @author Гайдаенко Олег
 * @category ew
 */
class ListServiceTypeController extends CommonController {

    public function actionIndex() {
        return $this->render('grid', ['model' => new ListServiceType()]);
    }

    public function actionCreate() {

        $model = new ListServiceType();
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
        $model = new ListServiceType();

        $models = ListServiceType::find()->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }
    
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result = (ListServiceType::getList(true, 'name', $lang));
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id' => $key, 'txt' => $val];
        return json_encode($result_id_txt); 
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор инкотермс
     * @return ListServiceType загруженная модель типа услуги
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = ListServiceType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

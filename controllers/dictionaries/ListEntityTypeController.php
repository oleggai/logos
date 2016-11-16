<?php

/**
 * Файл класса контроллера ListEntityType (Справочник cущностей ИС)
 */

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\ew\ListEntityType;

/**
 * Класс контроллер ListEntityType
 * @author Гайдаенко Олег
 * @category ew
 */
class ListEntityTypeController extends CommonController {

    public function actionIndex() {
        return $this->render('grid', ['model' => new ListEntityType()]);
    }

    public function actionCreate() {

        $model = new ListEntityType();
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
        $model = new ListEntityType();

        $models = ListEntityType::find()->all();

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

        $result = ListEntityType::getVisibleList(true, 'name', $lang);

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

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $id идентификатор Вида документа
     * @return ListEntityType загруженная модель списка сущностей ИС
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = ListEntityType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}

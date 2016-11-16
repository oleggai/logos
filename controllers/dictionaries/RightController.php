<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\access\Right;

/**
 * Контроллер для прав
 */
class RightController extends CommonController
{

    /**
     * Начальная инициализация контроллера
     */
    public function init(){

        $this->accessCheck = 'right'; // имя сущности для проверки
        parent::init();
    }

    /**
     * Поведения контроллера
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Список всех Right моделей.
     * @return mixed
     */
    public function actionIndex()
    {
/*        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', ['searchModel' => $searchModel,'dataProvider' => $dataProvider, ]);*/
        return $this->render('grid', ['model' => new Right()]);
    }


    /**
     * Создание Right модели.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Right();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Удаление Right модели.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id
     * @return Right найденая модель
     * @throws NotFoundHttpException  в случае неудачного поиска
     */
    protected function findModel($id)
    {
        if (($model = Right::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
    }

    public function actionGetTable(){

        $result = array();
        $models = Right::find()->orderBy('id desc')->all();
        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }
}

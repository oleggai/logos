<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\access\Role;

/**
 * Контроллер для ролей
 */
class RoleController extends CommonController
{

    /**
     * Начальная инициализация контроллера
     */
    public function init(){

        $this->accessCheck = 'role'; // имя сущности для проверки
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
     * Список Roles моделей.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' =>new Role()]);
    }


    /**
     * Создание Roles модели.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Role();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('form', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Удаление Roles модели.
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
     * @return Role найденая модель
     * @throws NotFoundHttpException  в случае неудачного поиска
     */
    protected function findModel($id)
    {
        if (($model = Role::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
    }

    public function actionGetTable(){

        $result = array();
        $models = Role::find()->orderBy('id desc')->all();
        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }
}

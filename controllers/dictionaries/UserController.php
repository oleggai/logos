<?php

namespace app\controllers\dictionaries;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\access\User;

/**
 * Контроллер пользователей
 */
class UserController extends CommonController
{

    /**
     * Начальная инициализация контроллера
     */
    public function init(){

        $this->accessCheck = 'user'; // имя сущности для проверки
        parent::init();
    }


    /**
     * Список всех User моделей.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new User()]);
    }


    /**
     * Cоздание User модели.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->user_id]);
        } else {
            return $this->render('form', ['model' => $model,
            ]);
        }
    }


    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id
     * @return User найденая модель
     * @throws NotFoundHttpException  в случае неудачного поиска
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
    }

    public function actionGetTable(){

        $result = array();
        $models = User::find()->orderBy('user_id desc')->all();
        foreach($models as $model) {
            $result[] = $model->toJson();
        }
        return json_encode($result);
    }
}

<?php

namespace app\controllers\counterparty;

use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\counterparty\CounterpartyContactPersEmail;
use app\models\common\CommonModel;

/**
 * Контроллер email контактов контрагентов
 * @author Tochonyi DM
 * @category Counterparty
 */
class CounterpartyContactPersEmailController extends CommonController
{

    /**
     * Список всех мыла указанного контрагента
     * @param $counterparty
     * @param null $contact
     * @return mixed текст контента страницы
     */
    public function actionIndex($counterparty, $contact = null)
    {
        return $this->render('//counterparty/counterparty/grid-contact-pers-email',
            ['model' => new CounterpartyContactPersEmail(), 'counterparty' => $counterparty, 'contact' => $contact]);
    }

    /**
     * Creates a new CounterpartyContactPersEmail model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($contact)
    {
        $model = new CounterpartyContactPersEmail();
        $model->counterparty_contact_pers = $contact;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('//counterparty/counterparty/form-contact-pers-email', ['model' => $model]);
        }
    }

    public function getForm(){
        return '//counterparty/counterparty/form-contact-pers-email';
    }

    /**
     * Finds the CounterpartyContactPersEmail model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CounterpartyContactPersEmail the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CounterpartyContactPersEmail::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Получение данных для табличного отображения
     * @return string Данные в формате json
     */
    public function actionGetTable($counterparty){

        $result = array();

        $models = CounterpartyContactPersEmail::find()
            ->leftJoin('{{%counterparty_contact_pers}} pers', 'counterparty_contact_pers=pers.id')
            ->where(['pers.counterparty' => $counterparty])->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }

    /**
     * Получение данных для табличного отображения при выборе
     * @return string Данные в формате json
     */
    public function actionGetTableSelect($counterparty){

        $result = array();

        $models = CounterpartyContactPersEmail::find()
            ->leftJoin('{{%counterparty_contact_pers}} pers', 'counterparty_contact_pers=pers.id')
            ->where(['pers.counterparty' => $counterparty, CounterpartyContactPersEmail::tableName().'.state' => CommonModel::STATE_CREATED])->all();

        foreach($models as $model)
            $result[] = $model->toJson();

        return json_encode($result);
    }

    /**
     * Получение массива полей
     * @return string Данные в формате json
     */
    public function actionGetEmail($id){
        if (($model = CounterpartyContactPersEmail::findOne($id)) == null) {
            return json_encode(null);
        }

        return json_encode($model->toJson());
    }
}

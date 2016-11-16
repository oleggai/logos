<?php

namespace app\controllers\dictionaries;


use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\common\CommonModel;
use app\models\common\sys\SysEntity;
use app\models\dictionaries\address\ListRegion;

/**
 * Контроллер регионов
 */
class ListRegionController extends CommonController
{
    /**
     * Lists all ListRegion models.
     * @return mixed
     */
    public function actionIndex()
    {
        $country = Yii::$app->getRequest()->get()['country'];
        $searchModel = new ListRegion();
        return $this->render('grid', ['model' => $searchModel,'country'=>$country]);
    }

    public function actionIndex2()
    {
        $region = Yii::$app->getRequest()->get()['region'];
        $searchModel = new ListRegion();
        $searchModel->level = 2;

        return $this->render('grid_level2', ['model' => $searchModel, 'region'=>$region]);
    }

    /**
     * Creates a new ListRegion model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ListRegion();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {

            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model,]);
        }
    }

    public function actionCreate2()
    {
        $model = new ListRegion();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view2', 'id' => $model->id]);
        } else {

            $model->generateDefaults(Yii::$app->request->get(), 2);
            return $this->render('form_level2', ['model' => $model,]);
        }
    }

    /**
     * Обновление модели.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate2($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view2', 'id' => $id]);
        } else {

            if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)){
                return $this->redirect(['view2', 'id' => $id, 'errors'=>$model->errors]);
            }

            $model->operation = CommonModel::OPERATION_UPDATE;
            SysEntity::saveOperation($model->getEntityCode(),$id,CommonModel::OPERATION_BEGIN_UPDATE);
            return $this->render('form_level2', ['model' => $model,]);
        }
    }

    public function actionView2($id){

        $model = $this->findModel($id);

        $getParams = Yii::$app->getRequest()->get();
        if ($getParams['errors']) {
            $model->addErrors($getParams['errors']);
        }


        if ($model->load(Yii::$app->request->post()) && $model->save()){

            return $this->redirect(['update2', 'id' => $model->id]);
        } else {

            $model->operation = CommonModel::OPERATION_VIEW;
            SysEntity::saveOperation($model->getEntityCode(),$id,CommonModel::OPERATION_VIEW);

            return $this->render('form_level2', ['model' => $model,]);
        }
    }


    /**
     * Deletes an existing ListRegion model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['update', 'id' => $id]);
    }*/

    public function actionDelete2($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {

            //$model->state = CommonModel::STATE_DELETED;
            $model->operation = CommonModel::OPERATION_DELETE;
            if ($model->save(false)){

                $getParams = Yii::$app->getRequest()->get();
                if ($getParams['current_operation'] == CommonModel::OPERATION_VIEW)
                    return $this->redirect(['view2', 'id' => $model->id]);

                if ($getParams['current_operation'] == CommonModel::OPERATION_GRIDVIEW)
                    return json_encode('item.state = '.$model->state.';item.stateText="'.$model->stateText.'"');

                return $this->redirect(['view2', 'id' => $model->id]);
            }
        }

        if ($model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE)){
            return $this->redirect(['view2', 'id' => $id, 'errors'=>$model->errors]);
        }

        $model->operation = CommonModel::OPERATION_DELETE;
        SysEntity::saveOperation($model->getEntityCode(),$id,CommonModel::OPERATION_BEGIN_DELETE);
        return $this->render('form_level2', ['model' => $model,]);
    }

    public function actionRestore2($id) {
        $m = $this->findModel($id);
        //$m->state = CommonModel::STATE_CREATED;
        $m->operation = CommonModel::OPERATION_CANCEL;
        $m->save(false);

        return $this->redirect(['update2', 'id' => $m->id]);
    }

    /**
     * Finds the ListRegion model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return ListRegion the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ListRegion::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    public function actionGetTable() {
        $result = [];
        $model = new ListRegion();
        $level = $this->getLevel();

        $getParams = Yii::$app->getRequest()->get();
        $country_ref = $getParams['country'];
        $region1_ref = $getParams['region'];
        $ext_where = '1=1';
        if ($country_ref)
            $ext_where .= " and country = $country_ref and level = 1";
        if ($region1_ref)
            $ext_where .= " and parent_id = $region1_ref";


        $models = ListRegion::find()
            ->where($this->getFiltersWhere($model, $model->getFilters($level)))
            ->andWhere('level = '.$level)
            ->andWhere($ext_where)
            ->orderBy('id desc')
            ->all();

        foreach($models as $model) {
            $result[] = $model->toJson();
        }

        return json_encode($result);
    }

    public function getLevel(){

        $level = 1;
        $getParams = Yii::$app->getRequest()->get();

        if ($getParams['level'])
            $level = $getParams['level'];

        return $level;
    }

    public function actionGetList()
    {
        $getParams = Yii::$app->getRequest()->get();
        
        $ext_where = '1=1';

        if (!empty($getParams['country'])) {
            $ext_where .= " and country = " . $getParams['country'];
        }
        if (!empty($getParams['region'])) {
            $ext_where .= " and parent_id = " . $getParams['region'];
        }
        if (!empty($getParams['level'])) {
            $ext_where .= " and level = " . $getParams['level'];
        }
        if (!empty($getParams['region_type'])) {
            $ext_where .= " and region_type = " . $getParams['region_type'];
        }
        
        $lang = !empty($getParams['lang']) ? $getParams['lang'] : Yii::$app->language;
        
        $format = !empty($getParams['format']) ? $getParams['format'] : 1;
        
        if ($format == 1) {
            $result_id_txt[] = ['id' => null, 'txt' => ''];
        } else {
            $result_id_txt = ['' => ''];
        }

        $regions = ListRegion::find()
            ->where('visible = '.CommonModel::VISIBLE.' AND state != '.CommonModel::STATE_DELETED)
            ->andWhere($ext_where)
            ->all();

        foreach ($regions as $region) {
            if ($format == 1) {
                $result_id_txt[] = ['id'=>$region->id, 'txt' => $region->{"name_$lang"}];
            } else {
                $result_id_txt[$region->id] = $region->{"name_$lang"};
            }
        }
        
        return json_encode($result_id_txt);
    }

    public function actionGetRegionType($region){

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];
        $mode = $getParams['mode'];

        if (!$lang)
            $lang = Yii::$app->language;


        $regionModel = ListRegion::findOne(['id'=>$region]);

        if ($mode == '1'){
            return json_encode($regionModel->region_type);
        }

        return json_encode($regionModel->regionType->{"name_$lang"});
    }
}

<?php

namespace app\controllers\counterparty;

use app\controllers\CommonController;
use app\models\attached_doc\AttachedDoc;
use app\models\common\CommonModel;
use app\models\counterparty\CounterpartyContactPers;
use app\models\counterparty\CounterpartyContactPersPhones;
use app\models\counterparty\CounterpartyContract;
use app\models\counterparty\CounterpartyLegalEntity;
use app\models\counterparty\CounterpartyManualAdress;
use app\models\counterparty\CounterpartyPersDocs;
use app\models\counterparty\CounterpartyPrivatPers;
use app\models\counterparty\CounterpartySign;
use app\models\counterparty\ListCounterpartySign;
use app\models\counterparty\ListPersDocType;
use app\models\dictionaries\employee\Employee;
use Yii;
use app\models\counterparty\Counterparty;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * Контроллер контрагентов
 * @author Tochonyi DM\
 * @category Counterparty
 */
class CounterpartyController extends CommonController
{
/*    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }*/

    /**
     * Список всех Counterparty моделей
     * @return mixed текст контента страницы
     */
    public function actionIndex()
    {
        return $this->render('grid', ['model' => new Counterparty(), 'type' => 0]);
    }

    /**
     * Список всех Counterparty моделей для выбора (все кроме удаленных)
     * @return mixed текст контента страницы
     */
    public function actionIndexSelect()
    {
        return $this->render('grid', ['model' => new Counterparty(), 'type' => 1002]);
    }

    /**
     * Возвращает список прикрепленных документов
     * @return int|string
     */
    public function actionGetDocList() {
        $result = [];
        $id = Yii::$app->request->get('id');
        if (!$id) {
            return Json::encode($result);
        }

        $counterparty = Counterparty::findOne($id);
        if ($counterparty === null) {
            return Json::encode($result);
        }

        foreach($counterparty->getAttachedDocs()->all() as $attachedDoc) {
            $result[] = $attachedDoc->toJson();
        }

        return Json::encode($result);
    }

    /**
     * Список Counterparty моделей только юр лиц
     * @return mixed текст контента страницы
     */
    public function actionIndexLegal()
    {
        return $this->render('grid', ['model' => new Counterparty(), 'type' => 2]);
    }

    /**
     * Список Counterparty моделей только юр лиц
     * @return mixed текст контента страницы
     */
    public function actionIndexCarrier()
    {
        return $this->render('grid', ['model' => new Counterparty(), 'type' => 1000]);
    }

    /**
     * Список Counterparty моделей только юр лиц, которые могут быть третьим лицом
     * @return mixed текст контента страницы
     */
    public function actionIndexThirdParty()
    {
        return $this->render('grid', ['model' => new Counterparty(), 'type' => 1001]);
    }

    /**
     * Создание Counterparty модели
     * @return mixed текст контента страницы
     */
    public function actionCreate()
    {
        $model = new Counterparty();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id идентификатор контрагента
     * @return Counterparty загруженная модель контрагента
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        if (($model = Counterparty::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Получение данных для табличного отображения
     * @param $type
     * @param null $filters
     * @return string Данные в формате json
     */
    public function actionGetTable($type, $filters=null){

        $counterpartyTbl = Counterparty::tableName();
        $privatePersTbl = CounterpartyPrivatPers::tableName();
        $legalEntityTbl = CounterpartyLegalEntity::tableName();
        $persDocsTbl = CounterpartyPersDocs::tableName();
        $contractTbl = CounterpartyContract::tableName();
        $signTbl = CounterpartySign::tableName();
        $contactPersTbl = CounterpartyContactPers::tableName();
        $contactPersPhonesTbl = CounterpartyContactPersPhones::tableName();
        $s_log_table = "{{%log_s_counterparty}}";
        $employee_table = Employee::tableName();

        $model = new Counterparty();

        if (!$filters)
            $filters = $model->filters;


        $where = null;
        if ($type > 0) {
            $where[$counterpartyTbl.".state"] = CommonModel::STATE_CREATED;
            if ($type < 1000)
                $where["person_type"] = $type;
            else if ($type == 1000)
                $where["s.counterparty_sign_id"] = 6; // перевозчики
            else if ($type == 1001)
                $where["le.maybe_thirdparty"] = 1; // может выступать третьей стороной
        }

        $filter = $this->getFiltersWhere($model,$filters);

        if ($filter == null && $type != 1000 && $type != 1001) {
            $query = Counterparty::find()->where($where)->orderBy('id desc');
        }
        else {
            if ($where == null)
                $where= [];
            $ids = (new Query())->select("$counterpartyTbl.id")
                ->distinct()
                ->from($counterpartyTbl)
                ->leftJoin("$privatePersTbl pp", "pp.counterparty = $counterpartyTbl.id")
                ->leftJoin("$legalEntityTbl le", "le.counterparty = $counterpartyTbl.id")
                ->leftJoin("$persDocsTbl pd", "pd.counterparty = $counterpartyTbl.id")
                ->leftJoin("$contractTbl c", "c.counterparty_id = $counterpartyTbl.id")
                ->leftJoin("$signTbl s", "s.counterparty_id = $counterpartyTbl.id")
                ->leftJoin("$contactPersTbl cp", "cp.counterparty = $counterpartyTbl.id")
                ->leftJoin("$contactPersPhonesTbl cpp", "cpp.counterparty_contact_pers = cp.id")
                ->leftJoin("$s_log_table log", "log.parent_id = $counterpartyTbl.id")
                ->leftJoin("$employee_table log_empl", "log_empl.id = log.create_user_id")
                ->where($where)
                ->andWhere($filter)
                ->all();

            $query = Counterparty::find()->where(['in', 'id', ArrayHelper::map($ids, 'id', 'id')])->orderBy('id desc');
        }

        return json_encode(CommonModel::getDataWithLimits($query));
    }

    public function actionGetATable($type){

        return $this->actionGetTable($type, (new Counterparty())->afilters);

    }

    /**
     * Получение данных по контрагенту физ лицу для заполнения контакта
     * @return string Данные в формате json
     */
    public function actionGetPrivatePers($id){
        if (($model = Counterparty::findOne($id)) == null) {
            return json_encode(null);
        }

        if (($privatePers = $model->counterpartyPrivatPers) == null) {
            return json_encode(null);
        }

        return json_encode($privatePers->toJson());
    }

    /**
     * Получение кода и названия контрагента
     * @return string Данные в формате json
     */
    public function actionGetName($id){
        if (($model = Counterparty::findOne($id)) == null) {
            return json_encode(null);
        }

        return json_encode($model->counterparty_id.' '.$model->{"counterpartyName_".Yii::$app->language});
    }

    /**
     * Получение массива полей контрагента
     * @return string Данные в формате json
     */
    public function actionGetCounterparty($id){
        if (($model = Counterparty::findOne($id)) == null) {
            return json_encode(null);
        }

        return json_encode($model->toJson());
    }

    /**
     * Получение id контрагента по коду
     * @return string Данные в формате json
     */
    public function actionGetCounterpartyId($counterparty_id){
        if (($model = Counterparty::findOne(['counterparty_id' => $counterparty_id, 'state' => CommonModel::STATE_CREATED])) == null) {
            return json_encode(null);
        }

        return json_encode($model->id);
    }

    public function actionGetListSign() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result_id_txt = [];
        $result = ListCounterpartySign::getList(true,$lang);
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id'=>$key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

    public function actionGetListDoctype() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result_id_txt = [];
        $result = ListPersDocType::getList(true,$lang);
        foreach ($result as $key => $val)
            $result_id_txt[] = ['id'=>$key, 'txt' => $val];
        return json_encode($result_id_txt);
    }

    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;
        
        if($getParams['warehouse']){
            $andWhere = 'warehouse_id = ' . (int) $getParams['warehouse'];
        }
        else if ($getParams['city']) {
            $andWhere = 'city_id = ' . (int) $getParams['city'];
        }
        else if ($getParams['country']) {
            $andWhere = 'country_id = ' . (int) $getParams['country'];
        }
        else $andWhere = '1=1';

        $result = Counterparty::getListByAddress('counterpartyName', true, $lang, $andWhere);

        foreach ($result as $key => $val)
            $result_id_txt[] = ['id' => $key, 'txt' => $val];
        return json_encode($result_id_txt);
    }
    

    public function actionGetCpInfo() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];
        $cpTable = Counterparty::tableName();
        $maTable = CounterpartyManualAdress::tableName();

        $result_cps = [];

        $resultCps = Counterparty::find()
                ->where('visible = :visible AND state != :state', [':visible' => CommonModel::VISIBLE, ':state' => CommonModel::STATE_DELETED])
                ->andWhere("exists( select * from $maTable  where counterparty = $cpTable.id and primary_address=1 and state!=".CommonModel::STATE_DELETED.")")
                ->all();

        if (!empty($resultCps))
            foreach ($resultCps as $cp) {
                //$address = $cp->counterpartyPrimaryAdress;
                //$phone = $cp->counterpartyPrimaryPhone;
                //$pnumber = $phone ? $phone->operator_code . $phone->phone_number : '';
                //if (!empty($address)) {
                    $result_cps[$cp->id] = [
                        'id' => $cp->id,
                        'code' => $cp->counterparty_id,
                        'person_type_id' => $cp->person_type,
                        // не подставлять город и страну (?)
//                        'city_id' => $address->city_id,
//                        'country_id' => $address->country_id,
                        // не подставлять телефон и индекс (?)
//                        'index' => $address->index,
//                        'phone' => $pnumber,
                    ];
                //}
            }
        return json_encode($result_cps);
    }

    public function actionGetAttachedDoc() {
        $result = [];

        $getParams = Yii::$app->request->get();
        $counterpartyId = $getParams['counterpartyId'];

        $counterpartyObj = $this->findModel($counterpartyId);

        $attachedDocs = $counterpartyObj->getAttachedDocsContracts($counterpartyId);

        foreach($attachedDocs->all() as $attachedDocArr) {
            $attachedDoc = AttachedDoc::findOne($attachedDocArr['attdoc_id']);
            $toJson = $attachedDoc->toJson();
            $toJson['contract_id'] = $attachedDocArr['contract_id'];
            $result[] = $toJson;
        }
        return Json::encode($result);
    }

}

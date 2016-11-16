<?php

/**
 * Файл класса контроллера тарифов
 */

namespace app\controllers\dictionaries;

use app\models\dictionaries\delivery\ListTariffDeliveryType;
use app\models\dictionaries\service\ListTariffServiceType;
use app\models\dictionaries\shipment\ListTariffShipmentFormat;
use Yii;
use yii\web\NotFoundHttpException;
use app\controllers\CommonController;
use app\models\dictionaries\delivery\DeliveryType;
use app\models\dictionaries\service\ServiceType;
use app\models\dictionaries\tariff\ListTariff;

/**
 * Контроллер тарифов
 * @author Дмитрий Чеусов
 * @category tariff
 */
class ListTariffController extends CommonController {

    /**
     * Начальная инициализация контроллера
     * @inheritdoc
     */
    public function init() {
        parent::init();
    }

    /**
     * Список всех тарифов
     * @return mixed текст контента страницы
     */
    public function actionIndex() {
        return $this->render('grid', ['model' => new ListTariff()]);
    }

    /**
     * Создание модели
     * @return mixed форма модели
     */
    public function actionCreate() {

        $model = new ListTariff();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->generateDefaults(Yii::$app->request->get());
            return $this->render('form', ['model' => $model]);
        }
    }

    /**
     * Редактирование модели
     * @param string $id идентификатор модели
     * @return mixed форма модели
     *
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('form', ['model' => $model]);
        }
    }*/

    /**
     * Поиск модели по первичному ключу
     * Если модель не найдена генерируется  404 HTTP исключение
     * @param string $id идентификатор модели
     * @return ListTariff загруженная модель
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id) {
        if (($model = ListTariff::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }

    /**
     * Вывод табличных данных
     * @return string json
     */
    public function actionGetTable() {

        $filter = $result = array();
        $model = new ListTariff();

        $f_delivery = Yii::$app->request->get()['f_tariff_zone_delivery'];
        $f_service = Yii::$app->request->get()['f_tariff_zone_service'];
        $f_shipment = Yii::$app->request->get()['f_tariff_zone_shipment'];
        if ($f_delivery) {
            unset(Yii::$app->request->get()['f_tariff_zone_delivery']);
        }
        if ($f_service) {
            unset(Yii::$app->request->get()['f_tariff_zone_service']);
        }
        if ($f_delivery) {
            unset(Yii::$app->request->get()['f_tariff_zone_shipment']);
        }
        $filter = $this->getFiltersWhere($model);

        $tariffTbl = ListTariff::tableName();
        $delivertyTypeTbl = ListTariffDeliveryType::tableName();
        $serviceTypeTbl = ListTariffServiceType::tableName();
        $shipmentFormat = ListTariffShipmentFormat::tableName();

        $models = ListTariff::find()
            // with для нежадной загрузки. Если будет много одинаковых запросов -
            // использовать по примеру $deliveryTypes = DeliveryType::getList();
            ->with('tariffZoneShiper')
            ->with('tariffZoneConsig')
            ->with('custDeclareCurrency')
            ->with('costCurrency')
            ->leftJoin("$delivertyTypeTbl dt", "dt.tariff_id = $tariffTbl.id")
            ->leftJoin("$serviceTypeTbl st", "st.tariff_id = $tariffTbl.id")
            ->leftJoin("$shipmentFormat sf", "sf.tariff_id = $tariffTbl.id")
            // join with для фильтров типа listTariffDeliveryTypes.delivery_id
            //->joinWith('listTariffDeliveryTypes')
            //->joinWith('listTariffServiceTypes')
            //->joinWith('listTariffShipmentFormats')
            ->where($filter)
            ->orderBy('id DESC')
            ->all();

        // при использовании связей with() часто повторяются одинаковые запросы
        // забираем весь справочник (быстрее, больше памяти)
        $deliveryTypes = DeliveryType::getList();
        $servicTypes = ServiceType::getList();
        $shipmentFormats = DeliveryType::getList();

        foreach ($models as $i => $model) {
            $result[$i] = $model->getAttributes();
            $result[$i]['tariffZoneShiper'] = $model
                            ->tariffZoneShiper
                            ->getAttributes()['name_' . Yii::$app->language];
            $result[$i]['tariffZoneConsig'] = $model
                            ->tariffZoneConsig
                            ->getAttributes()['name_' . Yii::$app->language];
            if (!empty($model->cargoType))
                $result[$i]['cargoType'] = $model
                                ->cargoType
                                ->getAttributes()['name_' . Yii::$app->language];
            $result[$i]['custDeclareCurrency'] = $model
                    ->custDeclareCurrency['nameShort'];
            $result[$i]['costCurrency'] = $model
                    ->costCurrency['nameShort'];


            foreach ($model->listTariffDeliveryTypes as $listTariffDeliveryType) {
                $result[$i]['listTariffDeliveryTypes'] .= ", " .
                        $deliveryTypes[$listTariffDeliveryType['delivery_type']];
            }
            $result[$i]['listTariffDeliveryTypes'] = trim($result[$i]['listTariffDeliveryTypes'], ', ');

            foreach ($model->listTariffServiceTypes as $listTariffServiceType) {
                $result[$i]['listTariffServiceTypes'] .= ", " .
                        $servicTypes[$listTariffServiceType['service_type']];
            }
            $result[$i]['listTariffServiceTypes'] = trim($result[$i]['listTariffServiceTypes'], ', ');

            foreach ($model->listTariffShipmentFormats as $listTariffShipmentFormat) {
                $result[$i]['listTariffShipmentFormats'] .= ", " .
                        $shipmentFormats[$listTariffShipmentFormat['shipment_format']];
            }
            $result[$i]['listTariffShipmentFormats'] = trim($result[$i]['listTariffShipmentFormats'], ', ');
        }
        return json_encode($result);
    }

}

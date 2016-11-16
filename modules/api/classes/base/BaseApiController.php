<?php

/**
 * Файл базового класса BaseApiController
 * @author Дмитрий Чеусов
 * @category API/classes/base
 */

namespace app\modules\api\classes\base;

use app\models\counterparty\Counterparty;
use app\models\counterparty\CounterpartyApi;

/**
 * BaseApiController класс
 * Наследуется классами XmlController и JsonController
 * Содержит основные параметры, метод установки параметров и 
 * фабрику для вызова классов (CurrencyApi итд) на основании параметров запроса
 */
class BaseApiController extends BaseApiRequest {

    /**
     * @var Counterparty запрашивающий Контрагент
     */
    public $counterparty;

    /**
     * @var app\modules\api\classes\base\BaseApiResponse обьект ответа сервера 
     */
    public $response;

    /**
     * Установка параметров после обработки конструктором контроллера
     * Используется в дочерних классах JsonController и XmlController
     * @param array $params параметры после распаковки
     * @throws \yii\web\BadRequestHttpException
     */
    public function setParams($params) {

        if (empty($this->response->errors)) {

            // Проверяем ключи
            $this->apiKey = $params->apiKey;
            $this->appKey = $params->appKey;

            if (empty($this->appKey) || empty($this->apiKey)) {
                $this->response->errors[] = ErrorMsg::GetError(410);
                //["error_code" => '410', 'error_msg' => \Yii::t('api', 'apiKey and appKey required!!!')];
            } else if (!empty(\Yii::$app->params['app_keys'])) {
                $keys = \Yii::$app->params['app_keys'];
                if (!in_array($this->appKey, $keys)) {
                    $this->response->errors[] = ["error_code" => '411', 'error_msg' => \Yii::t('api', 'appKey not found!')];
                }
                $this->counterparty = CounterpartyApi::findOne(['api_key' => $this->apiKey]);
                if (!$this->counterparty) {
                    $this->response->errors[] = ["error_code" => '412', 'error_msg' => \Yii::t('api', 'apiKey not found!')];
                }
                // Устанавливаем доступ к екшенам контроллеров
                \Yii::$app->params['apiAccess'] = true;
                // Устанавливаем параметры
                $this->modelName = 'app\\modules\\api\\classes\\'
                        . ucfirst($params->modelName)
                        . "Api";
                $mp_array = [];
                foreach ($params->methodProperties as $key => $mp_values)
                    $mp_array[$key] = $mp_values;
                $this->calledMethod = (string) $params->calledMethod;
                $this->methodParams = $mp_array;
                if (empty($this->modelName) || empty($this->calledMethod)) {
                    $this->response->errors[] = ["error_code" => '413', 'error_msg' => \Yii::t('api', 'modelName and calledMethod required!')];
                }
            } else if (empty(\Yii::$app->params['app_keys'])) {
                $this->response->errors[] = ["error_code" => '416', 'error_msg' => \Yii::t('api', 'Application appKeys are nit defined!')];
            }
        }
        return;
    }

    /**
     * Фабрика для вызова $controller->$method($params)
     * Проверяет наличие класса и метода, вызывает и возвращает результат работы класса
     * @return array массив данных, полученных от вызываемого метода
     * @throws \yii\web\BadRequestHttpException
     */
    public function getData() {

        $cpath = $this->modelName;
        $method = $this->calledMethod;
        if (!empty($this->methodParams)) {
            $params = $this->methodParams;
        } else {
            $params = [];
        }
        if (!class_exists($cpath)) {
            $this->response->errors[] = ["error_code" => '414', 'error_msg' => \Yii::t('api', 'Wrong modelName!')];
            return;
        }
        $controller = @new $cpath;
        if (!method_exists($controller, $method)) {
            $this->response->errors[] = ["error_code" => '415', 'error_msg' => \Yii::t('api', 'Wrong calledMethod!')];
            return;
        }
        $this->response = $controller->$method($params);
        return;
    }

}

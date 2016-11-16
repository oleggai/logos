<?php

/**
 * Файл класса контроллера XML API
 * Использование: <?xml version="1.0" encoding="UTF-8" ?><root><appKey>internal-app-key</appKey><apiKey>e5a303bc-ec5c-40b5-b504-1bcbfdd29f87</apiKey><modelName>currency</modelName><calledMethod>all</calledMethod></root>
 * @author Дмитрий Чеусов
 * @category API/controllers
 */

namespace app\modules\api\controllers;

use app\modules\api\classes\base\BaseApiController;
use app\modules\api\classes\base\BaseApiResponse;

/**
 * XmlController класс
 * Точка входа api/xml
 * Содержит распаковщик строки запроса и запаковщик ответа
 */
class XmlController extends BaseApiController {

    /**
     * Конструктор класса
     * Распаковывает XML запрос
     * Использует метод setParams родительского класса 
     * для установки параметров родительского класса
     * @param string $id Идентификатор модуля
     * @param Module $module Модуль, которому принадлежит конструктор
     * @param arrat $config Пары Имя-Значение для конфигурации модуля
     * @throws \yii\web\BadRequestHttpException
     */
    public function __construct($id, $module, $config = array()) {

        $this->response = new BaseApiResponse;
        $query = (\Yii::$app->request->getRawBody()) ? \Yii::$app->request->getRawBody() : \Yii::$app->request->post('q');
        $params = @simplexml_load_string(urldecode($query));
        if (empty($params)) {
            $this->response->errors[] = [
                'error_code' => '401',
                'error_msg' => \Yii::t('api', 'Empty request')
            ];
        }
        $this->setParams($params);
        parent::__construct($id, $module, $config);
    }

    /**
     * Осноовной метод класса
     * Получает данные из метода getData родительского класса и пакует в XML
     * @return string xml результат обработки запроса
     */
    public function actionIndex() {
        if (empty($this->response->errors)) {
            $this->getData();
        }
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><root></root>");
        $response = $this->array_to_xml($this->response, $xml);
        return $response->asXML();
    }

    /**
     * Стандартный метод преобразования массива в SimpleXML
     * @param array $result
     * @param SimpleXMLElement $response
     * @return SimpleXMLElement
     */
    private function array_to_xml($result, $response) {
        foreach ($result as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $response->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $response->addChild("item_$key");
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                $response->addChild("$key", htmlspecialchars("$value"));
            }
        }
        return $response;
    }

}

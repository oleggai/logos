<?php

/**
 * Файл класса контроллера JSON API
 * Использование: {"apiKey":"e5a303bc-ec5c-40b5-b504-1bcbfdd29f87","appKey":"internal-app-key","modelName":"country","calledMethod":"view","methodProperties":{"id":"616"}}'
 * @author Дмитрий Чеусов
 * @category API/controllers
 */

namespace app\modules\api\controllers;

use app\modules\api\classes\base\BaseApiController;
use app\modules\api\classes\base\BaseApiResponse;

/**
 * JsonController класс
 * Точка входа api/json
 * Содержит распаковщик строки запроса и запаковщик ответа
 */
class JsonController extends BaseApiController {

    /**
     * Конструктор класса
     * Распаковывает JSON запрос
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
        $params = @json_decode($query);
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
     * Получает данные из метода getData родительского класса и пакует в json
     * @return string json результат обработки запроса
     */
    public function actionIndex() {
        if (empty($this->response->errors)) {
            $this->getData();
        }

        $data='';
        //если был задан формат "файл" выдаем в ответ так как есть
        if ($this->response->isfileformat) {$data=$this->response->data;}
        else {unset($this->response->isfileformat);$data=json_encode($this->response);}

        return $data;
    }

}

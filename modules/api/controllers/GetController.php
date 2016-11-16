<?php

/**
 * Файл класса контроллера GET API
 * Использование: index.php?r=api/get&appKey=internal-app-key&apiKey=e5a303bc-ec5c-40b5-b504-1bcbfdd29f87&modelName=PrintForms&calledMethod=printEWMarking&methodProperties[@Ref]=2336
 * @category API/controllers
 */

namespace app\modules\api\controllers;

use app\modules\api\classes\base\BaseApiController;
use app\modules\api\classes\base\BaseApiResponse;

/**
 * GetController класс
 * Точка входа api/get
 * Содержит распаковщик строки запроса и запаковщик ответа
 */
class GetController extends BaseApiController {

    const ARRAY_FLAG='@'; //символ указываюий что параметр являеться массивом (для параметров второго уровня). Он будет начинаеться с этого символа ( например @EWNumber )
    const ARRAY_SEPARATOR=';';//символ разделитель для элементов


    /**
     * Конструктор класса
     * Распаковывает GET запрос
     * Использует метод setParams родительского класса 
     * для установки параметров родительского класса
     * @param string $id Идентификатор модуля
     * @param Module $module Модуль, которому принадлежит конструктор
     * @param arrat $config Пары Имя-Значение для конфигурации модуля
     * @throws \yii\web\BadRequestHttpException
     */
    public function __construct($id, $module, $config = array()) {
        $this->response = new BaseApiResponse;
        $params=new \stdClass();
        $getarr=\Yii::$app->request->get();
        unset($getarr['r']);

        //проходим по всем параметрам запроса
        foreach ($getarr as $get_el=>$get_val) {

            //если гет едлемент массив то преобразуем его в свойство объекта а его значения это свойста
           if (is_array($getarr[$get_el]))
           {
              $params->$get_el=new \stdClass();
              foreach ($get_val as $key=>$val)
              {

                  //если это указано как массив то формируем его как массив
                  if ($key[0]==self::ARRAY_FLAG)
                  {
                      $params->$get_el->{substr($key,1)}=explode(self::ARRAY_SEPARATOR,$val);

                  }else{
                      $params->$get_el->$key=$val;
                  }
              }
           }


            else{
                $params->$get_el=$get_val;
            }


        }


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
     * Входящий экшен
     * Получает данные из метода getData родительского класса
     * @return string - результат обработки запроса
     */
    public function actionIndex() {
        if (empty($this->response->errors)) {
            $this->getData();
        }


        $data='';
        //если нет ошибок то выводим данные
        if (empty($this->response->errors))
        {
            //если задан формат выдачи данных - файл
            if ($this->response->isfileformat) {$data=$this->response->data;}
        }

        //иначе формируем текст с ошибками
        else
        {
            $data='Errors! </br>';
            foreach ($this->response->errors as $error)
            {
                foreach ($error as $k=>$v)
                {
                    $data.= $k.' - '.$v.'</br>';
                }
                $data.='</br>';
            }
        }


        return $data;
    }

}

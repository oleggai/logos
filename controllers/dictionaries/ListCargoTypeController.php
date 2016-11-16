<?php
/**
 * Файл класса контроллера ListCargoType
 */

namespace app\controllers\dictionaries;

use Yii;
use app\models\dictionaries\warehouse\ListCargoType;
use app\controllers\CommonController;

/**
 * Класс контроллер ListCargoType
 * @author Дмитрий Чеусов
 * @category ew
 */
class ListCargoTypeController extends CommonController {

    /**
     * Получение пар ключ - значение списка типов для формы
     * @return string json
     */
    public function actionGetList() {

        $getParams = Yii::$app->getRequest()->get();
        $lang = $getParams['lang'];

        if (!$lang)
            $lang = Yii::$app->language;

        $result = ListCargoType::getList(true, 'name', $lang);

        // формат ответа: 1 - для Select2, 2 - для select
        $format = $getParams['format'];
        if (!$format)
            $format = 1;

        foreach ($result as $key => $val) {
            if ($format == 1)
                $result_id_txt[] = ['id' => $key, 'txt' => $val];
            else
                $result_id_txt[$key] = $val;
        }
        return json_encode($result_id_txt);
    }
}

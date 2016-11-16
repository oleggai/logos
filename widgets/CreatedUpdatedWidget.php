<?php
/**
 * В файле описан класс виджета для отображения инфорации о создателе и последнем редактировании сущности
 *
 * @author Richok FG
 * @category Интрерфейс
 */

namespace app\widgets;

use yii\base\Widget;

/**
 * Класс виджета для отображения инфорации о создателе и последнем редактировании сущности
 * @property mixed usersHtml
 */
class CreatedUpdatedWidget extends Widget {

    public $data = [];

    public function run() {

        return $this->render('CreatedUpdatedWidget', ['widget' => $this]);
    }
}
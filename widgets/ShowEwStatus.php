<?php
/**
 * В файле описан класс виджета отображения статусов ЭН
 *
 * @author Точеный Д.Н.
 * @category Интрерфейс
 */

namespace app\widgets;

use Yii;
use yii\base\Widget;

/**
 * Класс виджета отображения статусов ЭН
 * @property mixed filtersHtml
 */
class ShowEwStatus extends Widget {

    public function run() {

        return $this->render('ShowEwStatus', ['widget' => $this]);
    }

}
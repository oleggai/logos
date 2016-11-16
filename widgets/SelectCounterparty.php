<?php
/**
 * В файле описан класс виджета выбора контрагента
 *
 * @author Мельник И.А.
 * @category Интрерфейс
 */

namespace app\widgets;

use Yii;
use yii\base\Widget;

/**
 * Класс виджета выбора контрагента
 * @property mixed filtersHtml
 */
class SelectCounterparty extends Widget {

    /**
     * @var int Тип отображаемых КА. Подробнее смотреть в CounterpartyController.actionGetTable параметр $type
     */
    public $counterparty_type = 0;

    public function run() {

        return $this->render('SelectCounterparty', ['widget' => $this]);
    }

}
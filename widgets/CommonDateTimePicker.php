<?php
/**
 * В файле описан класс виджета выбора даты-времени
 *
 * @author Мельник И.А.
 * @category Интерфейс
 */

namespace app\widgets;

use vakorovin\datetimepicker\Datetimepicker;
use Yii;
use yii\base\Widget;
use app\models\common\Setup;

/**
 * Класс выбора даты-времени
 * @package app\widgets
 */
class CommonDateTimePicker extends Widget {
    /**
     * @var integer модель сущности
     */
    public $model;
    /**
     * @var CommonForm форма редактирования сущности
     */
    public $form;
    /**
     * @var string поле в модели
     */
    public $field;
    /**
     * @var array настройки виджета
     */
    public $config = [];
    /**
     * @var array опции виджета
     */
    public $options = [];
    public $label = null;

    public function init() {

        parent::init();

        $withTime = (!array_key_exists('mode', $this->config) || $this->config['mode'] != 'date');
        $opt = [
            'lang' => Yii::$app->language,
            'format' => $withTime ? Setup::DATETIME_FORMAT : Setup::DATE_FORMAT,
            'mask' => $withTime ? Setup::DATETIME_MASK : Setup::DATE_MASK,
            'allowBlank' => true,
            'lazyInit' => true,
            'validateOnBlur' => false,
            'class' => 'datetimewithbutton',
            'dayOfWeekStart'=>1,

        ];
        if ($this->model->disableEdit) {
            $opt['readonly'] = true;
        }

        unset($this->config['mode']);
        $this->options = array_merge(['options' => $opt], $this->config);
    }

    public function run() {
        if ($this->model != null){

            return $this->form
                ->field($this->model, $this->field)
                ->label($this->label)
                ->widget(Datetimepicker::className(), $this->options);
        }

        else
            return Datetimepicker::widget($this->options);
    }

    public static function show($config = []) {

        $dtp = new CommonDateTimePicker();
        $dtp->options = array_merge($dtp->options, $config);

        return $dtp->run();
    }
}
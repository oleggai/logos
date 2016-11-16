<?php
/**
 * В файле описан класс виджета-обретки для Select2
 *
 * @author Richok FG
 * @category Интрерфейс
 */

namespace app\widgets;

use app\models\common\CommonModel;
use kartik\select2\Select2;
use Yii;
use yii\base\Widget;
use yii\widgets\ActiveForm;

/**
 * Класс виджета-обретки для Select2
 */
class CommonSelect2Widget extends Widget {
    /**
     * @var CommonModel модель сущности
     */
    public $model;
    /**
     * @var ActiveForm форма редактирования сущности
     */
    public $form;
    /**
     * @var string поле в модели
     */
    public $field;
    /**
     * @var array набор элементов выпадающего списка
     */
    public $items;
    public $value;
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

        if ($this->model->disableEdit) {
                $this->config['disabled'] = true;
        }
    }

    public function run() {

        if ($this->form)
        return $this->form
            ->field($this->model, $this->field)
            ->label($this->label)
            ->widget(Select2::classname(),
                array_merge($this->config, [
                'data' =>  $this->items,
                'value' => $this->value,
                'language' => Yii::$app->language,
                'options' => $this->options,
                'pluginOptions' => [
                    'allowClear' => true
                ]
            ]));

        return Select2::widget(
            array_merge($this->config, [
                    'data' =>  $this->items,
                    'value' => $this->value,
                    'language' => Yii::$app->language,
                    'options' => $this->options,
                    'pluginOptions' => ['allowClear' => true, 'disabled' => $this->model->disableEdit ? true : false]
                    ]
            )
        );
    }
}
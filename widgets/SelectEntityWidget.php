<?php
namespace app\widgets;

use app\models\common\CommonModel;
use yii\base\Widget;


/**
 * Использование
 *
 *
 <?= SelectEntityWidget::widget([
'model' => $model,

'linked_field'=>'manifest-dep_point_id', // связанное поле в которое будет записано значение при выборе
'parent_field' => [ 'name'=>'имя_в_урл', 'id'=>'ид_отцовского_элемента'], // не обязательный параметр. используется для изменения select_url в зависимости от отцовского элемента

'select_tab_title'=>Yii::t('tab_title', 'MN_short_name').': '.Yii::t('tab_title', 'city_full_name').' '.Yii::t('tab_title', 'search_command'), // надпись таба при выборе
'select_url'=>Url::to(['list-city/index']), // урл таба при выборе
'select_tab_uniqname'=>'findcity_deppoint', // уникальное имя таба при выборе

'view_tab_title'=>Yii::t('tab_title', 'city_full_name').' {0} '.Yii::t('tab_title', 'view_command'), // надпись таба при просмотре
'view_url'=>Url::to(['list-city/view']), //  урл таба при просмотре выбранной сущности
'view_tab_uniqname'=>'city_{0}', // уникальное имя таба при просмотре. вместо {0} будет подставлен id
])?>

 * Class SelectEntityWidget
 * @package app\widgets
 */
class SelectEntityWidget extends Widget
{
    /**
     * @var string Имя виджета, для обеспечения уникальности 2+ виджетов на одной странице. Не обязательный параметр
     * По умолчанию генерируется автоматически случайным числом
     */
    public $name = null;
    /**
     * @var CommonModel Модель выбора\просмотра
     */
    public $model;
    /**
     * @var string Cвязанное поле (id) в которое будет записано значение при выборе
     * С этого поля (с атрибута "value) берется значение (id) для формирования url для view кнопки
     */
    public $linked_field = null;
    /**
     * @var string Id отцовское элемента. Используется для изменения select_url в зависимости от значения отцовского элемента. Не обязательный параметр
     */
    public $parent_field = null;
    /**
     * @var bool Прзнак отслеживания создания сущности при выборе.
     * Алгоритм : Выбор - элемент не найдет - создание - закрытие вкладки создания - автоматическое закрытие выбора - подстановка созданной сущности
     */
    public $with_creation = false;


    /**
     * @var bool Признак отображения кнопки выбора сущности
     */
    public $show_select = true;
    /**
     * @var bool Признак отображения кнопки просмотра сущности
     */
    public $show_view = true;

    /**
     * @var string Название закладки выбора сущности
     */
    public $select_tab_title;
    /**
     * @var string Урл закладки выбора сущности
     */
    public $select_url;
    /**
     * @var string Уникальное имя закладки выбора сущности
     */
    public $select_tab_uniqname;
    public $select_before_click = false;
    public $select_after_select = '';
    /**
     * @var string Название закладкипросмотра сущности
     */
    public $view_tab_title;
    /**
     * @var string Урл закладки просмотра сущности
     */
    public $view_url;
    /**
     * @var string Уникальное имя закладки просмотра сущности
     */
    public $view_tab_uniqname;
    /**
     * @var string Название грида (используется для обращение к events webix-а
     */
    public $grid_name;
    /**
     * @var string Используется для jquery метода ".on()". Все обработчики будут вешаться на элементы через этот селектор
     * @see http://api.jquery.com/on/
     */
    public $parent_selector = 'body';

    /**
     * Инициализация виджета
     */
    public function init()
    {
        parent::init();

        if (!$this->name) {
            $this->name = "select_entity_" . time().rand();
        }

        if ($this->model && $this->model->disableEdit){
            $this->show_select = false;
        }
    }

    /**
     * Запуск виджета
     * @return string Html код виджета
     */
    public function run()
    {
        return $this->render('SelectEntityWidget', [ 'widget' => $this,]);
    }

}

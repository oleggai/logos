<?php
/**
 * В файле описан класс виджета, для табличного отображения сущностей
 *
 * @author Мельник И.А.
 * @category Интрерфейс
 */

namespace app\widgets;

use app\models\common\CommonModel;
use kartik\select2\Select2;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

/**
 * Класс виджета для табличного отображения
 *
 * @property string filtersHtml Сгенерированный html со списком полей фильтрации
 * @property mixed operationsHtml
 */
class GridWidget extends Widget {

    /**
     * Режим отображения в виде таблицы
     */
    const MODE_TABLE = 1;
    /**
     * Режим отображения в виде таблицы с уровнями
     */
    const MODE_TABLE_LEVELS = 2;
    /**
     *  Режим отображения в виде редактируемой таблицы
     */
    const MODE_TABLE_EDITABLE = 3;

    /**
     * @var int Режим работы виджета
     */
    public $mode = self::MODE_TABLE;
    /**
     * @var array  Массив колонок
     */
    public $columns;
    /**
     * @var string JS код выполняемый после загрузки грида на странице
     */
    public $ready = '';
    /**
     * @var string Ссылка для получения данных
     */
    public $url;
    public $refresh_url;
    /**
     * @var bool Признак использовать урл для получения данных или нет
     */
    public $use_url = true;
    /**
     * @var CommonModel Модель
     */
    public $model;
    /**
     * @var string Имя переменной в javascript для хранения таблицы
     */
    public $grid_name = 'grida';
    /**
     * @var array Массив фильтров. Пример [['id'=>'my_id', 'label'=>Yii::t('app','some label'), 'comment'=>Yii::t('app','some comment'),],]
     */
    public $filters;
    public $afilters;
    /**
     * @var string Слылка для получения фильтрованных данных. По умолчанию генерится на основаниии this->url и this->filters
     */
    public $filter_url;
    public $afilter_url;
    /**
     * @var bool Генерировать или нет обработчик двойного нажатия на строке таблицы
     */
    public $doubleclick_generate = true;    
    /**
     * @var int Максимальная длина поля ввода символов 
     */
    public $maxlength = 128;
    /**
     * @var string Ссылка при двойном нажатии на строке. По умолчанию формируется как Url::to(['update'])
     */
    public $doubleclick_url;
    /**
     * @var string Название новой закладки при двойном нажатии на строке
     */
    public $doubleclick_tabname = 'Item edit #';

    /**
     * @var string Название новой закладки при двойном нажатии на строке (специфическое используя js форма)
     */
    public $doubleclick_special_tabname = '';

    /**
     * @var string Используемое поле сущности для названия закладки. Конечное название формируется как  $doubleclick_tabname + $doubleclick_itemfield
     * По умолчанию это 'Item edit # + item.id'
     */
    public $doubleclick_tabname_itemfield = 'id';
    /**
     * @var string Уникальный префикс для генерации номера закладки.
     * По умолчанию 'somesting + item.id'
     */
    public $doubleclick_uniqtabname = 'something';
    /**
     * @var string Уникальное имя для закладки создания сущности
     */
    public $create_uniqtabname = null;
    /**
     * @var string Имя поля которое возвращается гридом (в режиме выбора элемента)
     */
    public $doubleclick_returnfield = 'id';
    /**
     * @var string Имя идентификатора записей таблицы. Обычно это id
     */
    public $item_identificator;
    /**
     * @var bool Показывать id в таблице
     */
    public $show_id = true;
    /**
     * @var bool Признак отображения номера строки
     */
    public $show_rownum = true;
    /**
     * @var bool Признак постраничного отображения таблицы
     */
    public $show_pager = true;
    public $show_pager_advanced = false;
    /**
     * @var int Количесво строк на странице
     */
    public $pager_size = CommonModel::DATA_PAGE_SIZE;
    /**
     * @var int Количество отображаемых ссылок на страницы
     */
    public $pager_group = 5;
    public $pager_load_by_page = false;
    /**
     * @var string Надпись на кнопке создания и вкладке создания
     */
    public $create_label;
    /**
     * @var string Урл создания сущности (при нажатии на кнопку создания)
     */
    public $create_url;
    /**
     * @var bool Признак отображения операций над сущностями грида
     */
    public $show_operations = null;
    public $operations = null;
    public $operations_options = null;
    /**
     * @var bool Признак отображения кнопок дейсвий над сущностями грида
     */
    public $show_buttons = true;
    public $show_print_button = false;
    /**
     * @var bool Признак отображения кнопок редактируемого грида - добавить и удалить строку
     */
    public $show_editablebuttons = true;
    public $add_btn_revert = false;
    /**
     * @var bool Признак отображения "флажоков" в гриде, для возможности выбора нескольих записей
     * Пример использования в JS:
     * // получение объекта для работы с выделенными строками
     * var checked_items = ew_guide_get_checked_items();
     * // отправка в запросе объекта checked_items ( encodeURIComponent(JSON.stringify ()) )
     * var url = urlPrintAr + "&ews=" + encodeURIComponent(JSON.stringify(checked_items));
     * // проверка пустое веделение
     * if(checked_items.not_selected)
     *
     */
    public $show_checkboxes = false;
    public $show_refresh_button = false;
    /**
     * @var array Поля иерархического отображения, которые отображаются\скрыкаются при смене вида (вкл или выкл иерархия)
     */
    public $hierarchy_fields = [];
    /**
     * @var string Имя изображения сущностей при иерархическом отображении. К имени добавляется уровень сущности
     */
    public $hierarchy_image;
    /**
     * @var array Поля меню отображения\скрытия колонок. По умолчанию используются все поля
     */
    public $menu_columns = [];
    /**
     * @var array Дополнительные параметры таблицы
     */
    public $grid_options = [];
    /**
     * @var array Дополнительные параметры для данных
     */
    public $data_options = [];
    /**
     * @var array Список фильтров , зависящий от выбранного языка
     */
    public $lang_dependency = null;
    /**
     * @var array Фильтр выбора языка
     */
    public $lang_selector = null;
    /**
     * @var array Список фильтров , зависящий от выбранного языка
     */
    public $alang_dependency = null;
    /**
     * @var array Фильтр выбора языка
     */
    public $alang_selector = null;
    /**
     * @var array Данные для отображения в таблице. Используется при указании $model_parent_field
     */
    public $data = [];
    /**
     * @var CommonModel Отцовская модель для получения данных грида $model_parent->$model_parent_field
     */
    public $model_parent = null;
    /**
     * @var string Поле отцовской модели для получения данных грида $model_parent->$model_parent_field
     */
    public $model_parent_field = null;
    /**
     * @var array Массив полей, которые будут сохранены в $_POST при сохранении таблицы
     */
    public $post_model_fields = null;
    /**
     * @var string Формат названий полей в $_POST при сохранении таблицы
     */
    public $post_name_format = null;
    /**
     * @var int Автоматический номер новой записи для уникальности новых записей таблицы. Значения по умолчанию -1, -2, -3 и тд.
     */
    public $auto_id_val = -1;
    /**
     * @var bool Признак отображения изображения состояния сущности в таблице
     */
    public $show_stateimage = true;
    /**
     * @var string Тип таблицы. datatable. Планировалось использовать еще weibx treetable
     */
    public $grid_view = 'datatable';
    /**
     * @var bool Признак использования опций по умолчанию для таблицы
     * [ 'autoheight' => true, 'select' => "row", 'navigation'=>true, 'resizeColumn'=>true, 'dragColumn'=>true, 'fixedRowHeight'=>false,  'rowLineHeight'=>17, 'rowHeight'=>22, 'resizeRow'=>true];
     */
    public $defaultGridOptions = true;
    /**
     * @var bool Признак включения фильтра сразу после отображения таблицы
     */
    public $filter_onload = false;
    /**
     * @var bool Показывать или не показывать в меню операций *Выгрузка реестра для ТММ* и *Сформировать Акт ППО грузов*
     */
    public $showAdditionalOperation = false;
    public $grid_container;
    public $pager_container;
    /**
     * @var bool Загружать ли данные при начальном открытии страницы
     * Внимание! Использовать очень аккуратно
     * При использовании с набором  model_parent и model_parent_field (тоесть без url) необходима доп. обработка в методах set и save:
        public function setEwHistoryStatuses($value) {

            // признак того, что существующий список не был загружен клиенту, клиент добавляет новые записи
            $this->ewHistoryStatusesInputAppend = ($value[0]['grid_state'] == self::FIELD_WAS_NOT_LOADED);

            for ($i = 1; $i <= count($value); $i++) {

                if (!isset($value[$i]))
                    continue;
       ....

        public function saveEwHistoryStatuses() {
          ...
            // если сохранены не все записи которые были, значит некоторые из них пользователь удалил. удаляем их из базы
            if (!$this->ewHistoryStatusesInputAppend)
            foreach ($statusesInBase as $status) {
          ....
    **/
    public $load_on_start = true;
    public $sort_default = 'int';
    /**
     * Шаблон фильтра
     * @var string
     */
    public $afilters_view = 'AGridFilter';

    /**
     * Общая ширина виджета
     * @var string
     */
    public $afilter_width;
    
    /**
     * Общий класс виджета
     * @var string
     */
    public $afilter_class;
    
    public $select_entity_urls = [];

    /**
     * @var bool $oneGrid. Используеться в выгрузке гридов в ексель. Случай когда 2 и более гридов на странице
     */
    public $oneGrid = true;
    public $multi_select = false;

    /**
     * Метод инициализации параметров виджета
     */
    public function init()
    {
        parent::init();

        $getParams = Yii::$app->getRequest()->get();
        if (!$this->multi_select)
            $this->multi_select = $getParams['operation'] == CommonModel::OPERATION_GRIDVIEW_MULTISELECT;

        if ($this->multi_select)
            $this->show_checkboxes = true;


        if (empty($this->data_options['show_select_entity_columns'])) {
            // Специальное свойства для отображения SelectEntityWidget
            $this->data_options['show_select_entity_columns'] = [];
        }
        
        if (!$this->operations){
            $this->operations = $this->model->gridOperations;
            $this->operations_options = $this->model->gridOperationsOptions;
        }

        if (!$this->grid_container){
            $this->grid_container = "webix_grid_{$this->grid_name}";
        }

        if (!$this->pager_container && $this->show_pager){
            $this->pager_container = "pager_{$this->grid_name}";
        }

        if ($this->pager_load_by_page) {
            $this->sort_default = 'server';
        }

        if (!$this->create_uniqtabname){
            $this->create_uniqtabname = "create_".$this->doubleclick_uniqtabname;
        }

        if (!$this->model_parent_field && $this->model)
            $this->model_parent_field = $this->model->formName();

        if (!$this->create_label)
            $this->create_label = Yii::t('app','Create');

        if (!$this->item_identificator) {

            if ($this->model)
                $this->item_identificator = $this->model->tableSchema->primaryKey[0];
            else
                $this->item_identificator = 'id';
        }

        if (!$this->url){
            $this->url = Url::to(['get-table']);
        };

        if (!$this->columns) {
            $this->columns = [];
        }
        
        // Если не указаны поля для сохранения (отправки на сервер) - берем их массива колонок ($this->columns)
        if (!$this->post_model_fields) {
            $this->post_model_fields[] = $this->item_identificator;
            foreach ($this->columns as $item) {
                $this->post_model_fields[] = $item['id'];
            }
        }

        if (!$this->post_name_format){

            if ($this->model_parent)
                $this->post_name_format = $this->model_parent->formName().'['.$this->model_parent_field.'][:index][:name]';
            else
                $this->post_name_format = $this->model_parent_field.'[:index][:name]';
        }

        if (!$this->create_url) {
            if ($this->mode == self::MODE_TABLE_LEVELS) {
                $this->create_url = Url::to(['create', 'parent_ref'=>0]);
            }
            else {
                $this->create_url = Url::to(['create']);
            }
        }

        if (!$this->doubleclick_url) {
            $this->doubleclick_url = Url::to(['view']);
        }

        if (!$this->filter_url){
            $this->filter_url = $this->url;
        }
        if (!$this->afilter_url){
            $this->afilter_url = $this->url;
        }

        if ($this->filters)
            foreach ($this->filters as $filter)
                $this->filter_url .= "&{$filter['id']}=input_{$filter['id']}";

        if ($this->afilters)
            foreach ($this->afilters as $filter)
                $this->afilter_url .= "&{$filter['id']}=input_{$filter['id']}";


        if ($this->filters) {
            foreach ($this->filters as $key => $item) {
                if ($this->model && !array_key_exists('label',$item)) {
                    $this->filters[$key]['label'] = $this->model->getAttributeLabel($item['field']).':';
                }
            }
        }

        if ($this->afilters) {
            foreach ($this->afilters as $key => $item) {
                if ($this->model && !array_key_exists('label',$item)) {
                    $this->afilters[$key]['label'] = $this->model->getAttributeLabel($item['field']).':';
                }
            }
        }

        foreach ($this->columns as $key => $item) {

            if ($this->model) {
                // добавляем 'header' параметр в описание каждой колонки, если этот параметр не был указан в описании грида
                if (!array_key_exists('header', $item)) {
                    $this->columns[$key]['header'] = $this->model->getAttributeLabel($item['id']);
                }
            }

            if ($item['editor']=='date') {
                $this->columns[$key]['stringResult'] = true;
            }

            if (!array_key_exists('sort',$item)) {
                $this->columns[$key]['sort'] = $this->sort_default;
            }

            if ($this->hierarchy_fields && !array_key_exists('hidden',$item)) {
                if (in_array ($item['id'], $this->hierarchy_fields)) {
                    $this->columns[$key]['hidden'] = true;
                }
            }
        }

        foreach ($this->menu_columns as $key => $item) {
            if ($this->model) {
                if (!array_key_exists('value',$item)) {
                    $this->menu_columns[$key]['value'] = $this->model->getAttributeLabel($item['id']);
                }
            }
        }

        if ($this->show_operations === null) {
            $this->show_operations = $this->mode == self::MODE_TABLE_LEVELS;
        }

        $auto_columns = [
            [ 'id'=>'npp',	'header'=>'#', 'css'=>'rank','adjust'=>'data','sort'=>'int','width'=>30,'hidden'=>!$this->show_rownum,],
            [ 'id'=>'state_img','header'=>Yii::t('app','State'),'width'=>30, 'template'=>"<img src='pictures/table_item_state_#state#.png'/>",'sort'=>'int','hidden'=>!$this->show_stateimage],
            [ 'id'=>$this->item_identificator,	'header'=>Yii::t('app','ID'), 'width'=>50, 'hidden'=>!$this->show_id,'sort'=>$this->sort_default],
        ];

        if ($this->hierarchy_image){
            $auto_columns[] = [ 'id'=>'level_img','header'=>Yii::t('app','Level'),'width'=>30, 'template'=>"<img src='pictures/{$this->hierarchy_image}#level#.png'/>"];
        }
        if ($this->mode == self::MODE_TABLE_LEVELS){
            $auto_columns[] = [ 'id'=>'level',	'hidden'=>true];
            $auto_columns[] = [ 'id'=>'itemmode',	'hidden'=>true];
            $auto_columns[] = [ 'id'=>'parent_ref',	'hidden'=>true];
        }
        elseif ($this->mode == self::MODE_TABLE_EDITABLE) {
            $auto_columns = [
                [ 'id'=>'npp',	'header'=>'#', 'css'=>'rank','adjust'=>'data','sort'=>'int', 'width'=>30,'hidden'=>!$this->show_rownum,],
                [ 'id'=>$this->item_identificator,	'header'=>Yii::t('app','ID'), 'width'=>50, 'hidden'=>!$this->show_id,'sort'=>'int'],
            ];
        }

        if ($this->show_checkboxes){
            $auto_columns = array_merge([[
                'id'=>"check",
                'css'=> ['text-align' => 'center'],
                'header'=>[
                    "text"=>"<input type='checkbox' id='{$this->grid_name}_master_checkbox'  class='grid-master-checkbox' onclick='{$this->grid_name}_master_checkbox_click(this)' >",
                    'css'=> ['text-align' => 'center'],
                ],
                'template'=>"{common.checkbox()}",
                'width'=>30,
            ]], $auto_columns);
        }

        $this->columns = array_merge($auto_columns,$this->columns);

        if ($this->defaultGridOptions) {
            $grid_options = [
                'autoheight' => true,
                'select' => "row",
                'navigation'=>true,
                'resizeColumn'=>true,
                'dragColumn'=>true,
                'fixedRowHeight'=>false,
                'rowLineHeight'=>17,
                'rowHeight'=>22,
                'resizeRow'=>true
            ];
        } else {
            $grid_options = [];
        }

        if ($this->pager_load_by_page){
            $grid_options = array_merge($grid_options, [ 'datafetch'=>$this->pager_size, 'datathrottle'=>500, 'loadahead'=>0]) ;
        }
        else{
            $grid_options = array_merge($grid_options, [ 'datafetch'=>0]) ;
        }


        if ($this->mode == self::MODE_TABLE_EDITABLE) {

            $editEnable = $this->model_parent ? !$this->model_parent->getDisableEdit($this->model_parent_field) : true;
            $this->show_editablebuttons = $editEnable;

            $grid_options['editable'] = $editEnable;
            $grid_options['editaction'] = 'dblclick';

            if ($this->show_editablebuttons) {
                $this->columns[] = [
                    'id'=>'operations',
                    'template'=>"<input class='grid_addbtn grid-addbtn btn btn-default' type='button' value='+'><input class='grid_delbtn grid_delbtn btn btn-default' type='button' value='-'>",
                    'width'=>80,
                    'header'=>Yii::t('app', 'Operations')
                ];
            }

        }
        $this->grid_options = array_merge($grid_options, $this->grid_options);
        
        // Итоговая конфигурация columns
        // Ниже не изменять этот массив!
        foreach($this->columns as &$column) {
            $column['fillspace'] = 1;
            $column['adjust'] = false;
        }
        unset($column);
        
        if (!$this->use_url) {
            $this->url = null;

            $result = [];

            if ($this->model_parent) {
//                if ($this->model_parent->{$this->model_parent_field}) {
                if (($this->load_on_start || $this->model_parent->errors) && $this->model_parent->{$this->model_parent_field}) {
                    
                    $isDisableEdit = $this->model_parent->getDisableEdit($this->model_parent_field);
                    
                    foreach($this->model_parent->{$this->model_parent_field} as $item) {
                        /* @var $item \app\models\dictionaries\address\ListCity */
                        
                        // Если запрещено редактировать элементы - добавить соответствующее свойство каждому элементу
                        // Используется для \app\widgets\SelectEntityWidget
//                        if ($isDisableEdit) {
//                            $item->setDisableEdit();
//                            $item->setOperation(CommonModel::OPERATION_VIEW);
                            $item->setOperation($this->model_parent->getOperation());
//                        }
                        
                        $json = $item->toJson(); // получить данные
                        
                        if ($json[$this->item_identificator] == '') {
                            $json[$this->item_identificator] = $this->auto_id_val--;
                        }
                        
                        // Сгенерировать имена для SelectEntityWidget виджетов
                        // Далее по этим уникальным именам будут навешаны js event-ы
                        if (!empty($this->data_options['show_select_entity_columns'])) {
                            $selectEntityWidgetName = '';
                            foreach($this->data_options['show_select_entity_columns'] as $columnId) {
                                $selectEntityWidgetName = 'select_entity_' . $item->getUniqueId(); //@todo переделать SelectEntityWidget::generateName($uniqId)
                                $this->data_options['select_entity_names'][] = $selectEntityWidgetName;
                            }
                        }

                        $result[] = $json;
                    }
                    
                }

                if (empty($this->refresh_url)) {
                    $this->refresh_url = Url::to([
                        'get-field',
                        'class'=>$this->model_parent->className(),
                        'id'=>$this->model_parent->getIdentity(),
                        'field'=>$this->model_parent_field
                    ]);
                }
            }
            $this->data = $result;
        }
        else {
            $this->refresh_url =  $this->url;
        }
    }

    /**
     * Запуск виджета
     * @return string html код виджета
     */
    public function run(){
        return $this->render('GridWidget',['widget'=>$this]);
    }

    /**
     * Формирование фильтров виджета
     * @return string Html код фильтров
     */
    public function getFiltersHtml(){

        $result = '';

        if ($this->filters){
            $result .= '<div class="filter_div_data" style="width: 330px" tabindex="1">';

            foreach ($this->filters as $filter) {

                if ($filter['hidden'])
                    continue;

                if (empty($filter['options']))
                    $filter['options'] = [];

                $id = $filter['id'];

                switch ($filter['type']) {

                    case CommonModel::FILTER_SELECT2:
                        $input_html = Select2::widget([
                            'id'=>$id,'name'=>$id,
                            'class'=>'ff_select2',
                            'data'=>$filter['items'],
                            'options' => ['placeholder' => ' '],
                            'pluginOptions' => ['allowClear' => true],
                        ]);
                        break;
                    case CommonModel::FILTER_DROPDOWN:
                        $input_html = Html::dropDownList($id, $filter['value'],$filter['items'],['id'=>$id,'class'=> 'form-control'] );
                        break;
                    case CommonModel::FILTER_CHECKBOXESDROPDOWN:
                        $input_html = \app\classes\CommonHtml::checkboxListDropdown($id, null, $filter['items'], array_merge(['id' => $id,'class'=> 'form-control'], $filter['options']));
                        break;
                    case CommonModel::FILTER_CHECKBOX:
                        $input_html = Html::checkbox($id, $filter['value'],['id'=>$id,'class'=> 'form-control'] );
                        break;
                    case CommonModel::FILTER_DATETIME:
                        $input_html = CommonDateTimePicker::show(['id'=>$id,'name'=>$id, 'value'=>$filter['value']]);
                        break;
                    case CommonModel::FILTER_MASKEDEDIT:
                        $input_html = MaskedInput::widget([
                            'mask' => $filter['mask'],
                            'name'=>$id,
                            'options' => ['id'=>$id,'class'=>'form-control'],
                            'definitions' => $filter['mask_definitions'],
                        ]);
                        break;
                    case CommonModel::FILTER_HIDDEN:
                        $input_html = "<input id='$id' type='hidden' value='{$filter['value']}' />";
                        break;
                    default:
                        if ($filter['maxlength'])
                            $max = $filter['maxlength'];
                        else 
                            $max=128;
                        $input_html = "<input id='$id' class='form-control' maxlength='$max'/>";
                };

                //$width = $filter['use_select_widget'] ? "85%" : "100%";

                $result .= '<div class="form-inline">';
                $result .= '<div class="form-group">
                           <label class="ff_label control-label">' . $filter['label'] . '</label>
                           '. $input_html.'
                           <span class="comment">' . $filter['comment'] . '</span>
                           </div>';

                if ($filter['use_select_widget']){
                    $result.= SelectEntityWidget::widget([
                        'model' => $this->model,
                        'linked_field'=>$id,

                        'select_tab_title'=>$filter['select_tab_title'],
                        'select_url'=>$filter['select_url'],
                        'select_tab_uniqname'=>$filter['select_tab_uniqname'],

                        'view_tab_title'=>$filter['view_tab_title'],
                        'view_url'=>$filter['view_url'],
                        'view_tab_uniqname'=>$filter['view_tab_uniqname'],
                    ]);
                }
                $result .= '</div>';




                if ($filter['lang_dependency']){
                    $this->lang_dependency[] = $filter;
                }
                else if ($filter['lang_selector']){
                    $this->lang_selector = $filter;
                }

            }

            $result .= '<div class="btn btn-default btn-sm filter_clear_btn">'.Yii::t('app','Clear').'</div>
            <div class="btn btn-default btn-sm filter_submit_btn filter_search">'.Yii::t('app','Apply').'</div>
             </div>';
        }

        return $result;
    }

    /**
     * Переделанный метод GridWidget::getFiltersHtml() для расширенных фильтров
     *
     * @return string
     */
    public function getAFiltersHtml() {

        $data = null;

        if ($this->afilters) {

            $data = [
                'width' => $this->afilter_width,
                'class' => $this->afilter_class,
                'fields' => [],
            ];

            foreach ($this->afilters as $filter) {

                if ($filter['hidden'])
                    continue;

                $id = $filter['id'];

                if (empty($filter['options']))
                    $filter['options'] = [];

                $input = null;
                switch ($filter['type']) {

                    case 'add':
                        break;

                    case CommonModel::FILTER_SELECT2:
                        $input = Select2::widget([
                            'id' => $id, 'name' => $id,
                            'class' => 'ff_select2',
                            'data' => $filter['items'],
                            'options' => [
                                'placeholder' => ' ',
                                $filter['options'],
                            ],
                            'pluginOptions' => ['allowClear' => true],
                        ]);
                        break;

                    case CommonModel::FILTER_DROPDOWN:
                        $input = Html::dropDownList($id, $filter['value'], $filter['items'], array_merge(['id' => $id, 'class' => 'form-control'], $filter['options']));
                        break;

                    case CommonModel::FILTER_CHECKBOX:
                        $input = Html::checkbox($id, $filter['checked'], array_merge(['id' => $id, 'class' => 'form-control'], $filter['options']));
                        break;

                    case CommonModel::FILTER_CHECKBOXES:
                        $input = Html::checkboxList($id, null, $filter['items'], array_merge(['id' => $id,'class'=> 'form-control'], $filter['options']));
                        break;
                    
                    case CommonModel::FILTER_CHECKBOXESDROPDOWN:
                        $input = \app\classes\CommonHtml::checkboxListDropdown($id, null, $filter['items'], array_merge(['id' => $id,'class'=> 'form-control'], $filter['options']));
                        break;
                    
                    case CommonModel::FILTER_DATETIME:
                        $input = CommonDateTimePicker::show(array_merge(['id' => $id, 'name' => $id, 'value' => $filter['value']], $filter['options']));
                        break;

                    case CommonModel::FILTER_MASKEDEDIT:
                        $input = MaskedInput::widget([
                            'mask' => $filter['mask'],
                            'name'=>$id,
                            'options' => ['id'=>$id,'class'=>'form-control'],
                            'definitions' => $filter['mask_definitions'],
                        ]);
                        break;
                    
                    case CommonModel::FILTER_HIDDEN:
                        $input = "<input id='$id' type='hidden' value='{$filter['value']}' />";
                        break;

                    default:
                        $options = '';
                        if (!empty($filter['options']))
                            foreach ($filter['options'] as $name => $value)
                                $options .= "$name='$value' ";
                        $input = "<input id='$id' class='form-control' $options/>";
                };

                if ($filter['use_select_widget']) {

                    $input.= SelectEntityWidget::widget([
                            'model' => $this->model,
                            'linked_field' => $id,

                            'select_tab_title' => $filter['select_tab_title'],
                            'select_url' => $filter['select_url'],
                            'select_tab_uniqname' => $filter['select_tab_uniqname'],

                            'view_tab_title' => $filter['view_tab_title'],
                            'view_url' => $filter['view_url'],
                            'view_tab_uniqname' => $filter['view_tab_uniqname'],
                        ]);
                }

                $filter['label_class'] = "{$id}_label_class";
                $filter['class'] = "{$id}_class";
                $data['fields'][] = array_merge($filter, ['input' => $input]);

                if ($filter['lang_dependency']) {
                    $this->alang_dependency[] = $filter;
                } else if ($filter['lang_selector']) {
                    $this->alang_selector = $filter;
                }
            }
        }

        $result = $this->render($this->afilters_view, ['data' => $data]);

        return $result;
    }

    /**
     * Формирование операций над сущностями таблицы
     * @return string Html код операций над сущностями
     */
    public function getOperationsHtml(){

        $result =
            '<div class="btn-group btn-group-sm operation_selector">
            <a id="operation_label" role="button" data-toggle="dropdown" class="btn btn-default dropdown-toggle" data-target="#">'
            .Yii::t('app', 'Operations').' <span class="caret"></span></a>
            <ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">';

        $lastGroup = null;

        if ($this->operations_options)
        foreach ($this->operations_options as $operation=>$options){


            $hidden = isset($options['state_depend']) ? 'hidden' : '';

            if ($options['separator_before'])
                $result .= '<li role="separator" class="divider"></li>';


            if ($options['group']){
                if ( $options['group']!=$lastGroup) {

                    if ($lastGroup)
                        $result .= '</ul></li>';

                    $lastGroup = $options['group'];
                    $result .= '<li class="dropdown-submenu"><a href="#!">' . $options['group'] . '</a><ul class="dropdown-menu">';
                }
            }
            else{
                if ($lastGroup)
                    $result .= '</ul></li>';

                $lastGroup = $options['group'];
            }

            $result .= '<li class = "operation_selector" operation="'.$operation.'" '.$hidden.'><a href="#!">'.$this->operations[$operation].'</a></li>';

            if ($options['separator_after'])
                $result .= '<li role="separator" class="divider"></li>';
        }

        if ($lastGroup)
            $result .= '</ul></li>';
        if($this->showAdditionalOperation) {
            $result .= '<li class = "operation_selector"><a href="'.Url::toRoute(['index', 'printAr' => true]).'" class = "href_newtab" title="'.Yii::t("ew", "Form cargo AR").'">'.Yii::t("ew", "Form cargo AR").'</a></li>';
            $result .= '<li class = "operation_selector"><a href="'.Url::toRoute(['index', 'registryAr' => true]).'" class = "href_newtab" title="'.Yii::t("ew", "List of shipments").'">'.Yii::t("ew", "List of shipments").'</a></li>';
        }
        $result .= '</ul></div>';

        return $result;
    }
}
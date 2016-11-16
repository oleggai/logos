<?php
/**
 * В файле описана модель статуса треккинга
 *
 * @author Мельник И.А.
 * @category Треккинг статусы
 */


namespace app\models\ew;

use app\models\common\CommonModel;
use app\models\common\Translator;
use app\models\common\Langs;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Модель статуса треккинга
 *
 * @property string $id Идентификатор
 * @property string $parent_id Ссылка на родительский елемент
 * @property string $code Код статуса
 * @property integer $inner Внутренний статус
 * @property integer $level Уровень иерархии
 * @property integer $state Состояние
 * @property integer $type Вид статуса
 * @property string $description Описание
 *
 *  Переведенные свойства
 * @property string $nameShort Название статуса (сокращенное)
 * @property string $nameShortUk Название статуса на украинском (сокращенное)
 * @property string $nameShortEn Название статуса на английском (сокращенное)
 * @property string $nameShortRu Название статуса на русском (сокращенное)
 * @property string $nameFull Название статуса (полное)
 * @property string $nameFullUk Название статуса на украинском (полное)
 * @property string $nameFullEn Название статуса на английском (полное)
 * @property string $nameFullRu Название статуса на русском (полное)
 *
 * @property ListStatusesEw $parent
 * @property mixed statusTypeList
 * @property mixed maxLevel
 * @property mixed stateStr
 * @property mixed typeStr
 * @property mixed levels
 * @property ListStatusesEw[] childs
 */
class ListStatusesEw extends CommonModel
{

    // Вид статуса
    const STATUS_TYPE_NPI = 1;
    const STATUS_TYPE_NP = 2;
    const STATUS_TYPE_PARTNER = 3;


    // поля из таблицы переводов
    public $nameFull;
    public $namesFull;
    public $nameShort;
    public $namesShort;

    private $parentInput;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_statuses_ew}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['parent_id', 'inner', 'level', 'state', 'type'], 'integer'],
            [['code'], 'required'],
            [['code'], 'unique'],
            [['code'], 'string', 'max' => 10],
            [['description'], 'string', 'max' => 255],
            [['nameShortEn', 'nameShortUk', 'nameShortRu'], 'required'],
            [['nameShortEn', 'nameShortUk', 'nameShortRu'], 'string', 'max' => 255],
            [['nameFullEn', 'nameFullUk', 'nameFullRu'], 'required'],
            [['nameFullEn', 'nameFullUk', 'nameFullRu'], 'string', 'max' => 255],
            ['parent','validateParent']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('ewstatus', 'ID'),
            'parent_id' => Yii::t('ewstatus', 'Parent id'),
            'code' => Yii::t('ewstatus', 'Code'),
            'inner' => Yii::t('ewstatus', 'Inner code'),
            'level' => Yii::t('ewstatus', 'Level').' '.$this->level,
            'levelCheck' => Yii::t('ewstatus', 'Level').' '.$this->level,
            'state' => Yii::t('ewstatus', 'State'),
            'type' => Yii::t('ewstatus', 'Type'),
            'description' => Yii::t('ewstatus', 'Description'),
            'nameShort' => Yii::t('ewstatus', 'Name short'),
            'nameShortEn' => Yii::t('ewstatus', 'Name short (Eng)'),
            'nameShortUk' => Yii::t('ewstatus', 'Name short (Ukr)'),
            'nameShortRu' => Yii::t('ewstatus', 'Name short (Rus)'),
            'nameFull' => Yii::t('ewstatus', 'Name full'),
            'nameFullEn' => Yii::t('ewstatus', 'Name full (Eng)'),
            'nameFullUk' => Yii::t('ewstatus', 'Name full (Ukr)'),
            'nameFullRu' => Yii::t('ewstatus', 'Name full (Rus)'),
            'level1' => Yii::t('ewstatus', 'Level 1'),
            'level2' => Yii::t('ewstatus', 'Level 2'),
            'level3' => Yii::t('ewstatus', 'Level 3'),
            'stateStr' => Yii::t('ewstatus', 'State'),
            'typeStr' => Yii::t('ewstatus', 'Type'),
            'statement_notes'=> Yii::t('ew', 'Statement Notes')
        ];
    }

    /**
     * Получение отцовской модели
     * @return ListStatusesEw Отцовскиая модель
     */
    public function getParent(){
        return $this->hasOne(ListStatusesEw::className(), ['id' => 'parent_id']);
    }

    /**
     * Получение полного названия на английском
     * @return string Полное название на английском
     */
    public function getNameFullEn() {
        if ($this->namesFull != null && array_key_exists('en',$this->namesFull))
            return $this->namesFull['en'];

        return '';
    }

    /**
     * Установка полного названия на английском
     * @param $value string Полное название на английском
     */
    public function setNameFullEn($value) {
        $this->namesFull['en'] = $value;
    }

    /**
     * получение полного названия на украинском
     * @return string Полное название на украинском
     */
    public function getNameFullUk() {
        if ($this->namesFull != null && array_key_exists('uk',$this->namesFull))
            return $this->namesFull['uk'];

        return '';
    }

    /**
     * Установка полного названия на украинском
     * @param $value string Полное название на украинском
     */
    public function setNameFullUk($value) {
        $this->namesFull['uk'] = $value;
    }

    /**
     * Получение полного названия на русском
     * @return string Полное название на русском
     */
    public function getNameFullRu() {
        if ($this->namesFull != null && array_key_exists('ru',$this->namesFull))
            return $this->namesFull['ru'];

        return '';
    }

    /**
     * Установка полного названия на русском
     * @param $value string Полное название на русском
     */
    public function setNameFullRu($value) {
        $this->namesFull['ru'] = $value;
    }

    /**
     * Получение краткого названия на английском
     * @return string Краткое название на английском
     */
    public function getNameShortEn() {
        if ($this->namesShort != null && array_key_exists('en',$this->namesShort))
            return $this->namesShort['en'];

        return '';
    }

    /**
     * Установка краткого названия на английском
     * @param $value string Кртакое название на английском
     */
    public function setNameShortEn($value) {
        $this->namesShort['en'] = $value;
    }

    /**
     * Получение краткого названия на украинском
     * @return string Краткое название на украинском
     */
    public function getNameShortUk() {
        if ($this->namesShort != null && array_key_exists('uk',$this->namesShort))
            return $this->namesShort['uk'];

        return '';
    }

    /**
     * Yстановка краткого названия на украинском
     * @param $value string Краткое название на украинском
     */
    public function setNameShortUk($value) {
        $this->namesShort['uk'] = $value;
    }

    /**
     * Получение краткого названия на русском
     * @return string Краткое название на русском
     */
    public function getNameShortRu() {
        if ($this->namesShort != null && array_key_exists('ru',$this->namesShort))
            return $this->namesShort['ru'];

        return '';
    }

    /**
     * Установка краткого названия на русском
     * @param $value string Значение на русском
     */
    public function setNameShortRu($value) {
        $this->namesShort['ru'] = $value;
    }

    /**
     * Метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {

        parent::afterFind();

        // загрузка переводов полного и сокращенного названий
        $this->namesFull = Translator::getAll('{{%list_statuses_ew_translate}}', 'title_full', 'status_ew_id', $this->id);
        if (array_key_exists(Yii::$app->language,$this->namesFull))
            $this->nameFull = $this->namesFull[Yii::$app->language];
        $this->namesShort = Translator::getAll('{{%list_statuses_ew_translate}}', 'title_short', 'status_ew_id', $this->id);
        if (array_key_exists(Yii::$app->language,$this->namesShort))
            $this->nameShort = $this->namesShort[Yii::$app->language];
    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        // сохранение всех переводов
        Translator::setAll('{{%list_statuses_ew_translate}}', 'status_ew_id', $this->id,
            ['title_full' => $this->namesFull, 'title_short' => $this->namesShort]);

        $this->saveSServiceData($insert);
    }


    /**
     *  Метод получения доступных типов статусов
     * @return array Массив статусов [ значение => надпись]
     */
    public function getStatusTypeList($empty = false) {
        $r = [
                self::STATUS_TYPE_NPI=>Yii::t('ewstatus','NPI'),
                self::STATUS_TYPE_NP=>Yii::t('ewstatus','NP'),
                self::STATUS_TYPE_PARTNER=>Yii::t('ewstatus','Partner'),
        ];
        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Формирование полей по-умолчанию, перед созданием нового статуса
     * @param string $parent_ref string Ссылка на родительский элемент
     */
    public function generateDefaults($params, $parent_ref = null) {

        if ($this->hasErrors())
            return;

        if ($parent_ref==0)
            $parent_ref = null;

        $this->parent_id = $parent_ref;
        $this->state = self::STATE_CREATED;
        $this->level = 1;

        if ($parent_ref!=null){
            $this->level = $this->parent->level+1;
        }

        if ($params['operation'] != null)
            $this->copyStatusesEw($params);
    }

    public function copyStatusesEw($params){
        if($params['operation'] == self::OPERATION_COPY) {
            $statusEw = ListStatusesEw::findOne(['id' => $params['id']]);
            if($statusEw) {
                $this->attributes = $statusEw->getAttributes(null, ['code']);
                $this->nameFull = $statusEw->nameFull;
                $this->namesFull = $statusEw->namesFull;
                $this->nameShort = $statusEw->nameShort;
                $this->namesShort = $statusEw->namesShort;
                $this->parent = $statusEw->parent;
            }
        }
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение
     */
    public function toJson(){
        return array_merge($this->levels,
            [
            'id'=>$this->id,
            'code'=>$this->code,
            'nameShortUk'=>$this->nameShortUk,
            'nameShortEn'=>$this->nameShortEn,
            'nameShortRu'=>$this->nameShortRu,
            'inner'=>$this->inner,
            'state'=>$this->state,
            'stateStr'=>$this->stateStr,
            'type'=>$this->type,
            'typeStr'=>$this->typeStr,
            'level'=>$this->level,
            'parent_ref'=>$this->parent_id,
        ]);
    }

    /**
     * Получение массива уровней. От первого до максимального
     * @return array Массив  [ уровень => код]
     */
    public function getLevels(){

        $result = [];
        $node = $this;

        for ($i = $this->maxLevel ; $i > 0 ; $i--){

            $code  = '';
            if ($node && $node ->level == $i) {
                $code = $node->code;
                $node = $node->parent;
            }

            $result["level$i"] = $code;
        }

        return $result;

    }

    /**
     * Получение максимального уровня иерархии
     * @return int Максимальный уровень
     */
    public function getMaxLevel(){
        return 3;
    }

    /**
     * Получение значения чек-бокса уровня
     * @return int
     */
    public function getLevelCheck(){
        return 1;
    }


    /**
     * Получение надписи для состояния статуса
     * @return string Надпись
     */
    public function getStateStr(){
        if ($this->state!=null)
            return $this->stateList[$this->state];

        return '';
    }

    /**
     * Получение надписи для типа статуса
     * @return string Надпись
     */
    public function getTypeStr(){
        if ($this->type!=null)
            return $this->statusTypeList[$this->type];

        return '';
    }

    /**
     * Проверка нового родительского элемента
     * @param $attribute
     */
    public function validateParent($attribute) {

        if ($this->state == self::STATE_DELETED && $this->operation == self::OPERATION_CANCEL)
            return true;

        $newParentCode = $this->parentInput['code'];

        // код не поменялся
        if ($newParentCode==$this->parent->code)
            return;


        // проверка на ввод того же отцовского кода что и у редактируемого элемента
        if ($newParentCode == $this->code) {
            $this->addError($attribute, Yii::t('ewstatus', 'Parent code and self code are equal!'));
            return;
        }

        // проверка на существование введенного кода
        $newParent = self::findOne(['code'=>$newParentCode]);
        if ($newParent == null) {
            $this->addError($attribute, Yii::t('ewstatus', 'Parent code not found!'));
            return;
        }

        // получившийся уровень не должен превышать максимального
        $maxLevel = self::getMaxLevel();
        if ($newParent->level+$this->level > $maxLevel){
            $this->addError($attribute, Yii::t('ewstatus', "Parent level + self level bigger then max level ($maxLevel)!"));
            return;
        }

        // проверка на привязку к дочернему элементу
        if ($this->findChild($this, $newParentCode)!=null){
            $this->addError($attribute, Yii::t('ewstatus', 'New parent cannot be a child of the current element!'));
            return;
        }
    }


    /**
     * Сохранение введеного пользователем нового родительского элемента
     * @param $value array Новое значение
     */
    public function setParent($value){
        $this->parentInput = $value;
    }

    /**
     * Поиск дочернего элемента
     * @param $code string Код элемента
     * @return ListStatusesEw Найденный элемент
     */
    private function findChild($code){

        foreach ($this->childs as $child){
            if ($child->code == $code)
                return $child;

            $find = $child->findChild($code);
            if ($find)
                return $find;
        }

        return null;
    }


    /**
     * Получение дочерних элементов
     * @return \yii\db\ActiveQuery
     */
    public function getChilds(){
        return $this->hasMany(ListStatusesEw::className(), ['parent_id' => 'id']);
    }

    /**
     * Метод перед сохранением
     * @param bool $insert Вставка или обновление
     * @return bool Результат метода
     */
    public function beforeSave($insert) {

        if (parent::beforeSave($insert)) {

            // смена состояния в зависимости от выбранной операции
            if ($this->operation == self::OPERATION_CANCEL)
                $this->state = self::STATE_CREATED;

            // если операция Удаление, то удалить все дочерние элементы
            if ($this->operation == self::OPERATION_DELETE && $this->level < 3) {
                $children = ListStatusesEw::findAll(['parent_id' => $this->id]);
                foreach ($children as $child) {
                    if ($this->level == 1) {
                        $grandchildren = ListStatusesEw::findAll(['parent_id' => $child->id]);
                        foreach ($grandchildren as $grandchild) {
                            $grandchild->delete();
                        }
                    }
                    $child->delete();
                }

            }

            $this->operation = self::OPERATION_NONE;

            $newParentCode = $this->parentInput['code'];
            // код поменялся
            if ($newParentCode!=$this->parent->code) {
                $newParent = self::findOne(['code' => $newParentCode]);
                $this->parent_id = $newParent->id;
                $this->level = $newParent->level+1;
                $this->updateChildLevels();
            }

            return true;
        }

        return false;
    }

    /**
     * Обновление уровней дочерних элементов (+1 от текущего уровня)
     */
    private function updateChildLevels(){

        foreach ($this->childs as $child){

            $child->level = $this->level+1;
            $child->save();
            $child->updateChildLevels();
        }
    }

    /**
     * Получение массива уровней
     * @return array Массив уровней (код => надпись)
     */
    public function getLevelList($empty = false){
        $r = [
            1 =>Yii::t("ewstatus","1-st level of hierarchy"),
            2 =>Yii::t("ewstatus","2-nd level of hierarchy"),
            3 =>Yii::t("ewstatus","3-rd level of hierarchy"),
        ];
        if ($empty)
            $r = [null => ''] + $r;
        return $r;    }

    /**
     * Получение массива уровней
     * @return array Массив уровней (код => надпись)
     */
    public function getInnerList($empty = false){
        $r = [
            0 =>Yii::t("app","No"),
            1 =>Yii::t("app","Yes")
        ];
        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Получение списка полей для виджета фильтрации
     * @return array массив для виджета фильтрации
     */
    public function getFilters(){

        return  [
            [ 'id' => 'use_hierarchy', 'hidden' => true],
            [ 'id' => 'parent_ref', 'value'=>null, 'hidden' => true],

            ['id'=>'f_ewstatus_state', 'type'=>self::FILTER_DROPDOWN,
                'items'=>$this->getStateList(true), 'operation' => '=', 'field' => 'state'],
            ['id'=>'f_ewstatus_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,
                'items'=>Langs::$Names, 'operation' => '=', 'field' => 'lang', 'label'=>Yii::t('app', 'Language')],
            ['id'=>'f_ewstatus_id', 'operation' => '=', 'field' => 'id'],
            ['id'=>'f_ewstatus_code', 'operation' => '=', 'field' => 'code'],
            ['id'=>'f_ewstatus_level',  'type'=>self::FILTER_DROPDOWN, 'value'=>null,
                'items'=>$this->getLevelList(true), 'operation' => '=', 'field' => 'level', 'label' => Yii::t('ewstatus', 'Level')],
            ['id'=>'f_ewstatus_namehort','operation' => 'starts', 'field' => 'title_short',
                'label'=>$this->getAttributeLabel('nameShort')],
            ['id'=>'f_ewstatus_namefull','operation' => 'starts', 'field' => 'title_full',
                'label'=>$this->getAttributeLabel('nameFull')],
            ['id'=>'f_ewstatus_inner', 'type'=>self::FILTER_DROPDOWN, 'value'=>null,
                'items'=>$this->getInnerList(true), 'operation' => '=', 'field' => '`inner`',
                'label'=>$this->getAttributeLabel('inner')],
            ['id'=>'f_ewstatus_type', 'type'=>self::FILTER_DROPDOWN, 'value'=>null,
                'items'=>$this->getStatusTypeList(true), 'operation' => '=', 'field' => 'type'],
        ];
    }

    public static function getTopList($field = 'nameFull', $empty = false) {
        $arr = ListStatusesEw::find()->where('state != :state AND level = 1', [':state' => CommonModel::STATE_DELETED])->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    public function getGridOperations() {
        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app','Copy'),
        ];
    }

    public function getGridOperationsOptions() {
        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before'=>true, 'tab_name_sufix'=>'copy'],
        ];
    }

    public static function checkUniqueStatusesNum($trackingNum) {
        return ListStatusesEw::findOne(['code' => $trackingNum]) ? Yii::t('error', "Attention! $trackingNum already exists") : '';
    }


    public static function getList($field = 'title_short', $empty = false, $lang = null, $andWhere = null) {

        $translateTable = '{{%list_statuses_ew_translate}}';
        $selfTable = self::tableName();

        if (!$lang)
            $lang = Yii::$app->language;

        if(!$andWhere)
            $andWhere = 'visible = '.  CommonModel::VISIBLE.' AND state != '.CommonModel::STATE_DELETED;

        $models = self::find()
            ->select("id,$field")
            ->leftJoin("$translateTable tr", "tr.status_ew_id = $selfTable.id and lang='$lang'" )
            ->andWhere($andWhere)
            ->asArray(true)
            ->all();

        $result = ArrayHelper::map($models, 'id', $field);

        if ($empty)
            $result = [null => ''] + $result;

        return $result;
    }
}

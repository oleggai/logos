<?php

namespace app\models\dictionaries\exchangerate;

use Yii;
use app\models\common\CommonModel;
use app\models\dictionaries\currency\Currency;


/**
 * Модель курсов валют
 *
 * @property string $id Идентификатор
 * @property string $currency_parent Отцовская валюта
 * @property string $currency_child Дочерняя валюта
 * @property string $ratio Отоношение
 * @property string $date_ratio Дата отношения
 *
 * @property Currency $currencyParent Модель для отцовской валюты
 * @property Currency $currencyChild Модель для дочерней валюты
 */
class ExchangeRate extends CommonModel
{
    /**
     * Имя таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%exchange_rates}}';
    }

    /**
     * Получение курса СТАРАЯ ВРСИЯ (не находит прямой курс)
     * @param $parent int Ид валюты из которой нужно конвертировать
     * @param $child int Ид валюты в которую нужно конвертировать
     * @return float Конвертированное значение
     */
    public static function getExRate($parent, $child){

        $result = null;
        $result_reverse = null; // обратный курс

        if ($parent == $child)
            return 1;

        $rates = self::getList();

        for ($i = 0; $i < count($rates); $i++) {

            if ($rates[$i]['id_child'] == $child &&
                    $rates[$i]['id_parent'] == $parent) {

                $result = $rates[$i]['ratio'];
                break;
            }

            if ($rates[$i]['id_child'] ==  $parent &&
                    $rates[$i]['id_parent'] == $child) {

                $result_reverse = $rates[$i]['ratio'];
                break;
            }

        }

        // прямой не найден, но найден обратный
        if ($result === null && $result_reverse !== null && $result_reverse > 0)
            $result = 1 / $result_reverse;

        return $result;
    }

    /**
     * Получение курса НОВАЯ версия
     * @param $parent int Ид валюты из которой нужно конвертировать
     * @param $child int Ид валюты в которую нужно конвертировать
     * @return float Конвертированное значение
     */
    public static function getExRealRate($parent, $child){
        
        $result = null;

        if ($parent == $child)
            return 1;

        $rate = self::find()
                ->where(['currency_parent' => $parent, 'currency_child' => $child])
                ->andWhere(['visible' => 1, 'state' => 1])
                ->orderBy('date_ratio desc')
                ->one();
        if (!empty($rate)) {
           $result = $rate->ratio;
        } else {
            // прямой не найден, ищем обратный
            $rate = self::find()
                    ->where(['currency_parent' => $child, 'currency_child' => $parent])
                    ->andWhere(['visible' => 1, 'state' => 1])
                    ->orderBy('date_ratio desc')
                    ->one();
            if (!empty($rate)) {
                $result = 1/$rate->ratio;
            }
        }
        return $result;
    }
    
    /**
     * Правила для полей
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['currency_parent', 'currency_child', 'ratio', 'date_ratio'], 'required'],
            [['currency_parent', 'currency_child'], 'integer'],
            [['ratio'], 'number'],
            [['date_ratio'], 'safe']
        ]);
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('exchangerate', 'ID'),
            'currency_parentStr' => Yii::t('exchangerate', 'Currency Parent'),
            'currency_childStr' => Yii::t('exchangerate', 'Currency Child'),
            'currency_parent' => Yii::t('exchangerate', 'Currency Parent'),
            'currency_child' => Yii::t('exchangerate', 'Currency Child'),
            'ratio' => Yii::t('exchangerate', 'Ratio'),
            'date_ratio' => Yii::t('exchangerate', 'Date Ratio'),
            'operation' => Yii::t('app', 'Operation')
        ];
    }

    /**
     * Метод получения модели отцовской валюты
     */
    public function getCurrencyParent()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_parent']);
    }

    /**
     * Метод получения модели дочерней валюты
     */
    public function getCurrencyChild()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_child']);
    }

    /**
     * Метод получения родительской валюты как строки
     */
    public function getCurrency_parentStr(){
        return Currency::getListAll('nameShort')[$this->currency_parent];
    }

    /**
     * Метод получения дочерней валюты как строки
     */
    public function getCurrency_childStr(){
        return Currency::getListAll('nameShort')[$this->currency_child];
    }


    /**
     * Формирование полей по-умолчанию, перед созданием нового курса
     */
    //public function generateDefaults($params)
    public function generateDefaults()
    {
        if ($this->hasErrors())
            return;

        $this->date_ratio = date("Y-m-d H:i:s");
        $this->state = CommonModel::STATE_CREATED;
        //if ($params['operation'] != null)
        //    $this->copyRate($params);
    }

    /*public function copyRate($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $rate = ExchangeRate::findOne(['id' => $params['id']]);
            if($rate) {
                $this->attributes = $rate->getAttributes();
            }
        }
    }*/

    /**
     * Метод получения массива актуальных курсов
     */
    public static function getList() {

        $rates = Yii::$app->db->createCommand(
        "select (select symbol from ".Currency::tableName()." where id = currency_parent) currency_parent,
                (select symbol from ".Currency::tableName()." where id = currency_child) currency_child,
                currency_parent as id_parent, currency_child as id_child,
                ratio
         from ".self::tableName()."
         inner join
            (select currency_parent as cp,currency_child as cc , max(date_ratio) as maxdt from ".self::tableName()."
             group by currency_child,currency_parent) maxt
         on (cp = currency_parent and cc = currency_child and maxdt = date_ratio)")->queryAll();

        return $rates;
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes){

        parent::afterSave($insert, $changedAttributes);
        $this->saveSServiceData($insert);
        $this->operation = self::OPERATION_NONE;
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение

     */
    public function toJson(){

        return [
            'id'                 => $this->id,
            'currency_parent'    => $this->currency_parent,
            'currency_parentStr' => $this->getCurrency_parentStr(),
            'currency_childStr'  => $this->getCurrency_childStr(),
            'currency_child'     => $this->currency_child,
            'ratio'              => $this->ratio,
            'date_ratio'         => $this->date_ratio,
            'state'              => $this->state
        ];

    }

    /**
     * Получение списка полей для виджета фильтрации
     * @return array массив для виджета фильтрации
     */
    public function getFilters(){

        return  [
            //['id'=>'f_exchangerate_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,
            //    'items'=>Langs::$Names, 'operation' => '=', 'field' => 'lang', 'label'=>Yii::t('app', 'Lang')],

            //['id'=>'f_exchangerate_state', 'type'=>self::FILTER_DROPDOWN, 'value'=>self::STATE_CREATED,
            //    'items'=>$this->getStateList(), 'operation' => '=', 'field' => 'state'],

            ['id'=>'f_exchangerate_id','operation' => '=', 'field' => 'id'],

            ['id'=>'f_exchangerate_date_ratio_start', 'type'=>self::FILTER_DATETIME, 'operation' => '>=',
                'field' => 'date_ratio', 'label'=>Yii::t('currency', 'Date Ratio Start'),],

            ['id'=>'f_exchangerate_date_ratio_end', 'type'=>self::FILTER_DATETIME, 'operation' => '<=',
                'field' => 'date_ratio', 'label'=>Yii::t('currency', 'Date Ratio End'),]


        ];
    }

    /*public function getGridOperations() {

        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app', 'Copy'),
        ];
    }

    public function getGridOperationsOptions() {

        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before' => true, 'tab_name_sufix' => 'copy'],
        ];
    }*/
}

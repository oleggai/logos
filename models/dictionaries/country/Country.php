<?php

namespace app\models\dictionaries\country;

use app\models\dictionaries\tariff\ListTariffZone;
use Yii;
use yii\db\Query;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\dictionaries\currency\Currency;
use app\models\dictionaries\employee\Employee;
use app\models\common\Translator;
use app\models\common\CommonModel;
use app\models\common\Langs;
use app\models\dictionaries\address\ListRegion;

/**
 * Модель для стран
 * @author Richok FG
 * @category country
 *
 * @property string $id Укикальный идентификатор страны
 * @property integer $digital_code Код цифровой
 * @property string $alpha2_code Код АЛЬФА-2
 * @property string $alpha3_code Код АЛЬФА-3
 * @property integer $visible Видимость страны
 * @property integer $state Состояние страны
 * @property CountryInfo $countryInfo Модель дополнительной информации о стране
 * @property array operations Достуаные операции над страной
 * @property string nameShortEn Краткое название на английском
 * @property string nameShortUk Краткое название на украинском
 * @property string nameShortRu Краткое название на русском
 * @property string nameOfficialEn Оффициальное название на английском
 * @property string nameOfficialUk Оффициальное название на украинском
 * @property string nameOfficialRu Оффициальное название на русском
 * @property integer nationalDirector Национальный директор
 * @property string nationalDirectorText Имя национального директора
 * @property integer limitIndividualPerson Ограничение по стоимости таможенного декларирования для физ. лиц
 * @property integer currLimitIndividual Валюта ограничения (для физ. лиц)
 * @property string currLimitIndividualText Названия валюты ограничения (для физ. лиц)
 * @property integer limitLegal Ограничение по стоимости таможенного декларирования для юр. лиц
 * @property integer currLimitLegal Валюта ограничения (для юр. лиц)
 * @property string currLimitLegalText Названия валюты ограничения (для юр. лиц)
 * @property integer nationalCurrency Национальная валюта
 * @property string nationalCurrencyText Названия национальной валюты
 * @property string additionalInformation Дополнительная информация
 * @property array visibilityList Список видимостей
 * @property string visibilityText Название текущей видимости
 * @property LogCountry[] logs Логи операций над страной
 * @property mixed logCreation Запись лога о создании страны
 * @property LogCountry[] logLastUpdate Запись лога о последнем редактировании
 * @property array serviceData Служебная информация
 * @property string $tariff_zone Тарифная зона
 * @property ListTariffZone tariffZone
 */
class Country extends CommonModel
{

    /**
     * @var string кратное название на текущем языке
     */
    public $nameShort;
    /**
     * @var string массив кратких названий на всех языках
     */
    public $namesShort;
    /**
     * @var string оффициальное название на текущем языке
     */
    public $nameOfficial;
    /**
     * @var string массив оффициальных названий на всех языках
     */
    public $namesOfficial;
    /**
     * @var CountryInfo ссылка на модель связанной таблицы yii2_country_info
     */
    public $countryInfo;

    /*
     * integer ID страны украина
     */
    const CODE_UKRAINE = 828;

    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%country}}';
    }

    public static function translateTableName(){
        return '{{%country_translate}}';
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return array_merge(parent::rules(),
            [
                [['visible', 'state', 'tariff_zone'], 'integer'],
                [['digital_code'], 'integer', 'max' => 9999],
                [['alpha2_code'], 'required'],
                [['alpha2_code'], 'string', 'max' => 2],
                [['alpha3_code'], 'string', 'max' => 3],
                [['digital_code', 'alpha2_code'], 'unique'],
                [['nameShortEn', 'nameShortUk', 'nameShortRu'], 'required'],
                [['nameShortEn', 'nameShortUk', 'nameShortRu'], 'string', 'max' => 50],
                [['nameOfficialEn', 'nameOfficialUk', 'nameOfficialRu'], 'string', 'max' => 100],
                [['nameShortEn', 'nameOfficialEn'], 'match', 'pattern' => '/^[\w\s\W]+[^0-9А-яҐЄЇІґєїіЪЫЁЭъыёэ!@#\$%^*?=]+$/u'],
                [['nameShortUk', 'nameOfficialUk'], 'match', 'pattern' => '/^[А-яҐЄЇІіґєї\s\W\[\]]+[^A-z0-9ЪЫЁЭъыёэ!@#\$%\^\*\?=]+$/u'],    // [] - не работает
                [['nameShortRu', 'nameOfficialRu'], 'match', 'pattern' => '/^[А-яЁё\s\W\[\]]+[^A-zҐЄЇІґєїі0-9!@#\$%\^\*\?=]+$/u'],
                [['nationalDirector', 'currLimitIndividual', 'currLimitLegal', 'nationalCurrency'], 'integer'],
                [['limitIndividualPerson', 'limitLegalPerson'], 'integer', 'max' => 99999999],
                [['additionalInformation'], 'string', 'max' => 500],
                //[['stateText'], 'string']
            ]);
    }

    /**
     * Надписи для полей
     * @return array массив названий полей
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('country', 'ID'),
            'digital_code' => Yii::t('country', 'Digital Code'),
            'alpha2_code' => Yii::t('country', 'Alpha2 Code'),
            'alpha3_code' => Yii::t('country', 'Alpha3 Code'),
            'visible' => Yii::t('country', 'Availability of choice'),
            'visibilityText' => Yii::t('country', 'Availability of choice'),
            'state' => Yii::t('country', 'State'),
            'nameShort' => Yii::t('country', 'Name Short'),
            'nameShortEn' => Yii::t('country', 'Name Short (Eng)'),
            'nameShortUk' => Yii::t('country', 'Name Short (Ukr)'),
            'nameShortRu' => Yii::t('country', 'Name Short (Rus)'),
            'nameOfficial' => Yii::t('country', 'Name Official'),
            'nameOfficialEn' => Yii::t('country', 'Name Official (Eng)'),
            'nameOfficialUk' => Yii::t('country', 'Name Official (Ukr)'),
            'nameOfficialRu' => Yii::t('country', 'Name Official (Rus)'),
            'nationalDirector' => Yii::t('country', 'National director'),
            'nationalDirectorText' => Yii::t('country', 'National director'),
            'limitIndividualPerson' => Yii::t('country', 'Limitations on the value of the customs declaration for individuals'),
            'currLimitIndividual' => Yii::t('country', 'Currency restrictions (for individuals)'),
            'currLimitIndividualText' => Yii::t('country', 'Currency restrictions (for individuals)'),
            'limitLegalPerson' => Yii::t('country', 'Limitations on the value of the customs declaration for legal entities'),
            'currLimitLegal' => Yii::t('country', 'Currency restrictions (for legal entities)'),
            'currLimitLegalText' => Yii::t('country', 'Currency restrictions (for legal entities)'),
            'nationalCurrency' => Yii::t('country', 'National Currecy'),
            'nationalCurrencyText' => Yii::t('country', 'National Currecy'),
            'additionalInformation' => Yii::t('country', 'Additional information'),
            'name_short' => Yii::t('country', 'Name Short'),
            'stateText' => Yii::t('app', 'State'),
            'operation' => Yii::t('app', 'Operation'),
            'tariff_zone' => Yii::t('address', 'Tariff zone'),
        ];
    }

    /**
     * Формирование полей по-умолчанию, перед созданием новой страны
     */
    public function generateDefaults($params) {

        if ($this->hasErrors())
            return null;

        $this->state = CommonModel::STATE_CREATED;
        $this->countryInfo = new CountryInfo();

        if ($params['operation'] != null)
            $this->copyCountry($params);
    }

    public function copyCountry($params) {

        if($params['operation'] == self::OPERATION_COPY) {
            $country = Country::findOne(['id' => $params['id']]);
            if($country) {
                $this->attributes = $country->getAttributes();
                $this->namesShort = $country->namesShort;
                $this->namesOfficial = $country->namesOfficial;
                $this->countryInfo->attributes = $country->countryInfo->getAttributes();
            }
        }
    }

    /**
     * Получение ссылки на модель связанной таблицы yii2_country_info
     * @return CountryInfo модель связанной таблицы с информацией
     */
    public function getCountryInfo() {
        return ($this->countryInfo == null) ? new CountryInfo() : $this->countryInfo;
    }

    /**
     * получение краткого названия страны на английском
     * @return string краткое название на английском
     */
    public function getNameShortEn() {
        return $this->namesShort['en'];
    }

    /**
     * установка краткого названия страны на английском
     * @param string $value краткое название на английском
     */
    public function setNameShortEn($value) {
        $this->namesShort['en'] = $value;
    }

    /**
     * получение краткого названия страны на украинском
     * @return string краткое название на украинском
     */
    public function getNameShortUk() {
        return $this->namesShort['uk'];
    }

    /**
     * установка кратного названия страны на украинском
     * @param string $value краткое название на украинском
     */
    public function setNameShortUk($value) {
        $this->namesShort['uk'] = $value;
    }

    /**
     * получение краткого названия страны на русском
     * @return string краткое название на русском
     */
    public function getNameShortRu() {
        return $this->namesShort['ru'];
    }

    /**
     * установка кратного названия страны на русском
     * @param string $value краткое название на русском
     */
    public function setNameShortRu($value) {
        $this->namesShort['ru'] = $value;
    }

    /**
     * получение официального названия страны на английском
     * @return string официальное название на английском
     */
    public function getNameOfficialEn() {
        return $this->namesOfficial['en'];
    }

    /**
     * установка официального названия страны на английском
     * @param string $value официальное название на английском
     */
    public function setNameOfficialEn($value) {
        $this->namesOfficial['en'] = $value;
    }

    /**
     * получение официального названия страны на украинском
     * @return string официальное название на украинском
     */
    public function getNameOfficialUk() {
        return $this->namesOfficial['uk'];
    }

    /**
     * установка официального названия страны на украинском
     * @param string $value официальное название на украинском
     */
    public function setNameOfficialUk($value) {
        $this->namesOfficial['uk'] = $value;
    }

    /**
     * получение официального названия страны на русском
     * @return string официальное название на русском
     */
    public function getNameOfficialRu() {
        return $this->namesOfficial['ru'];
    }

    /**
     * установка официального названия страны на русском
     * @param string $value официальное название на русском
     */
    public function setNameOfficialRu($value) {
        $this->namesOfficial['ru'] = $value;
    }

    /**
     * получение id записи национального директора
     * @return int идентификатор национального директора
     */
    public function getNationalDirector() {
        if ($this->countryInfo != null)
            return $this->countryInfo->national_director;
    }

    /**
     * установка id записи национального директора
     * @param int $value идентификатор национального директора
     */
    public function setNationalDirector($value) {
        if ($this->countryInfo == null)
            $this->countryInfo = new CountryInfo();
        $this->countryInfo->national_director = $value;
    }

    /**
     * получение имени национального директора
     * @return string полное имя национального директора (surnameFull)
     */
    public function getNationalDirectorText() {
        if ($this->countryInfo != null) {
            $emp = Employee::getById($this->countryInfo->national_director);
            if ($emp != null)
                $emp->surnameFull;
        }
    }

    /**
     * получение ограничения по стоимости таможенного декларирования для физ. лиц
     * @return int ограничение по стоимости таможенного декларирования
     */
    public function getLimitIndividualPerson() {
        if ($this->countryInfo != null)
            return $this->countryInfo->lim_cdv_pperson;
    }

    /**
     * установка ограничения по стоимости таможенного декларирования для физ. лиц
     * @param int $value ограничение по стоимости таможенного декларирования
     */
    public function setLimitIndividualPerson($value) {
        if ($this->countryInfo == null)
            $this->countryInfo = new CountryInfo();
        $this->countryInfo->lim_cdv_pperson = $value;
    }

    /**
     * получение id валюты ограничения (для физ. лиц)
     * @return int идентификатор валюты
     */
    public function getCurrLimitIndividual() {
        if ($this->countryInfo != null)
            return $this->countryInfo->curr_lim_pperson;
    }

    /**
     * установка id валюты ограничения (для физ. лиц)
     * @param int $value идентификатор валюты
     */
    public function setCurrLimitIndividual($value) {
        if ($this->countryInfo == null)
            $this->countryInfo = new CountryInfo();
        $this->countryInfo->curr_lim_pperson = $value;
    }

    /**
     * получение названия валюты ограничения (для физ. лиц)
     * @return string название валюты
     */
    public function getCurrLimitIndividualText() {
        if ($this->countryInfo != null) {
            $curr = Currency::getById($this->countryInfo->curr_lim_pperson);
            if ($curr != null)
                return $curr->nameFull;
        }
    }

    /**
     * получение ограничения по стоимости таможенного декларирования для юр. лиц
     * @return int ограничение по стоимости таможенного декларирования
     */
    public function getLimitLegalPerson() {
        if ($this->countryInfo != null)
            return $this->countryInfo->lim_cdv_jperson;
    }

    /**
     * установка ограничения по стоимости таможенного декларирования для юр. лиц
     * @return int $value ограничение по стоимости таможенного декларирования
     */
    public function setLimitLegalPerson($value) {
        if ($this->countryInfo == null)
            $this->countryInfo = new CountryInfo();
        $this->countryInfo->lim_cdv_jperson = $value;
    }

    /**
     * получение id валюты ограничения (для юр. лиц)
     * @return int идентификтор валюты
     */
    public function getCurrLimitLegal() {
        if ($this->countryInfo != null)
            return $this->countryInfo->curr_lim_jperson;
    }

    /**
     * установка id валюты ограничения (для юр. лиц)
     * @param int $value идентификатор валюты
     */
    public function setCurrLimitLegal($value) {
        if ($this->countryInfo == null)
            $this->countryInfo = new CountryInfo();
        $this->countryInfo->curr_lim_jperson = $value;
    }

    /**
     * получение названия валюты ограничения (для юр. лиц)
     * @return string название валюты
     */
    public function getCurrLimitLegalText() {
        if ($this->countryInfo != null) {
            $curr = Currency::getById($this->countryInfo->curr_lim_jperson);
            if ($curr != null)
                return $curr->nameFull;
        }
    }

    /**
     * получение id модели национальной валюты
     * @return int идентификатор валюты
     */
    public function getNationalCurrency() {
        if ($this->countryInfo != null)
            return $this->countryInfo->national_currency;
    }

    /**
     * установка id национальной валюты
     * @param int $value идентификатор валюты
     */
    public function setNationalCurrency($value) {
        if ($this->countryInfo == null)
            $this->countryInfo = new CountryInfo();
        $this->countryInfo->national_currency = $value;
    }

    /**
     * получение названия национальной валюты
     * @return string название национальной валюты
     */
    public function getNationalCurrencyText() {
        if ($this->countryInfo != null) {
            $curr = Currency::getById($this->countryInfo->national_currency);
            if ($curr != null)
                return $curr->nameFull;
        }
    }

    /**
     * получение допольнительной информации
     * @return string дополнительная информация
     */
    public function getAdditionalInformation() {
        if ($this->countryInfo != null)
            return $this->countryInfo->additional_information;
    }

    /**
     * установка допольнительной информации
     * @param string $value дополнительная информация
     */
    public function setAdditionalInformation($value) {
        if ($this->countryInfo == null)
            $this->countryInfo = new CountryInfo();
        $this->countryInfo->additional_information = $value;
    }

    /**
     * получение страны по уникальному идентификатору (id)
     * @param int $id идентификатор страны
     * @return null|Country модель страны
     */
    public static function getById($id) {
        return static::findOne(['id' => $id]);
    }

    /**
     * получения списка стран (с заданием поля для отображения) (только видимые, только неудаленные)
     * @param string $field поле для отображение
     * @return array массив стран
     */
    public static function getList($field = 'nameOfficial') {
        $arr = Country::find()->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])->all();
        return ArrayHelper::map($arr, 'id', $field);
    }

    /**
     * ускоренный метод получения списка стран
     * @param string $field
     * @return array массив стран
     */
    public static function getListFast($field = 'name_official', $empty = false, $lang = null) {

        if (!$lang)
            $lang = Yii::$app->language;

        $result = (new Query())
            ->select(['id', $field])
            ->from(Country::tableName())
            ->leftJoin('{{%country_translate}}', 'country_id = id AND lang = "'.$lang.'"')
            ->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])
            ->all();

        $r = ArrayHelper::map($result, 'id', $field);
        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }
    
    /**
     * Получить логи манифеста
     */
    /*public function getLogs(){
        return $this
            ->hasMany(LogCountry::className(), ['id' => 'log_row_id'])
            ->viaTable('{{%arch_country}}', ['id' => 'id']);
    }*/

    /**
     * Получить лог создания
     */
    /*public function getLogCreation(){
        return $this
            ->hasOne(LogCountry::className(), ['id' => 'log_row_id'])
            ->where(['type' => LogCountry::OPERATION_CREATE])
            ->viaTable('{{%arch_country}}', ['id' => 'id']);
    }*/

    /**
     * Получить последний лог редактирования
     */
    /*public function getLogLastUpdate(){
        return $this
            ->hasOne(LogCountry::className(), ['id' => 'log_row_id'])
            ->where(['type' => LogCountry::OPERATION_UPDATE])
            ->orderBy('date DESC')
            ->viaTable('{{%arch_country}}', ['id' => 'id']);
    }*/

    /**
     * Получение служебной информации
     * @return array Массив с полями служебной информации
     */
    /*public function getServiceData(){

        $createArray = [];
        if ($this->logCreation)
            $createArray = $this->logCreation->toJson('create_');
        $lastUpdateArray = [];
        if ($this->logLastUpdate)
            $lastUpdateArray = $this->logLastUpdate->toJson('lastupdate_');

        return array_merge($createArray, $lastUpdateArray);
    }*/

    /**
     * Метод архивирования
     * @param $operation int Тип операции. LogCountry::OPERATION_
     */
    public function archive($operation) {
        $user_id = Yii::$app->user->id;
        $sql = "call archive_country($this->id, $operation, $user_id)";
        Yii::$app->db->createCommand($sql)->execute();
    }

    /**
     * метод вызывается после создания объекта модели и загрузки его данных из БД
     */
    public function afterFind() {
        // goga debug
        // загрузка переводов краткого и оффициального названия
        $this->namesShort = Translator::getAll('{{%country_translate}}', 'name_short', 'country_id', $this->id);
        $this->nameShort = $this->namesShort[Yii::$app->language];
        $this->namesOfficial = Translator::getAll('{{%country_translate}}', 'name_official', 'country_id', $this->id);
        $this->nameOfficial = $this->namesOfficial[Yii::$app->language];

        // получение ссылки на модель связанной таблицы yii2_country_info
        $this->countryInfo = CountryInfo::getByCountryId($this->id);
        // если нет, создаем новый объект
        if ($this->countryInfo == null)
            $this->countryInfo = new CountryInfo();
    }

    /**
     * метод вызывается после удаления сущности
     * @return bool флаг удаления
     */
    public function beforeDelete() {
        //Translator::delTranslation('{{%country_translate}}', 'country_id', $this->id);
        //$this->countryInfo->delete();
        return parent::beforeDelete();
    }

    /**
     * методы вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);

        // добавление в лог операций над сущностью
        $this->archive($insert ? LogCountry::OPERATION_CREATE : LogCountry::OPERATION_UPDATE);

        if ($this->operation == self::OPERATION_NONE || $this->operation == self::OPERATION_UPDATE) {
            //сохранение переводов полей и дополнительной инфы в таблцу yii2_country_info
            Translator::setAll('{{%country_translate}}', 'country_id', $this->id,
                ['name_short' => $this->namesShort, 'name_official' => $this->namesOfficial]);
            $this->countryInfo->country_id = $this->id;

            $this->countryInfo->save();
        }

        $this->operation = self::OPERATION_NONE;
    }

    public function getIdVisible(){
        return true;
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля=>значение

     */
    public function toJson($withSelectEntityWidget = false) {

        $lang = Yii::$app->language;

        $result = [
            'id'                     => $this->id,
            'name_short_ru'          => $this->getNameShortRu(),
            'name_official_ru'       => $this->getNameOfficialRu(),
            'name_short_en'          => $this->getNameShortEn(),
            'name_official_en'       => $this->getNameOfficialEn(),
            'name_short_uk'          => $this->getNameShortUk(),
            'name_official_uk'       => $this->getNameOfficialUk(),
            'digital_code'           => $this->digital_code,
            'alpha2_code'            => $this->alpha2_code,
            'alpha3_code'            => $this->alpha3_code,
            'state'                  => $this->state,
            'national_director'      => $this->getCountryInfo()->employee->surnameFull,
            'additional_information' => $this->getCountryInfo()->additional_information,
            'tariff_zone'            => $this->tariffZone->{"name_$lang"},
        ];
        
        if ($withSelectEntityWidget) {
            $result['uniq_id'] = $this->getUniqueId();
            $result['country_select_entity'] = $this->getSelectEntityWidget();
        }
        
        return $result;
    }
    
    public function getSelectEntityWidget()
    {
        $uniqId = $this->getUniqueId();
        $selectEntityWidgetName = 'select_entity_' . $uniqId;
        
        return 
            '<div class="select-entity-widget-container">' .
                '<input type="hidden" id="' . $uniqId . '_route_id" value="' . $this->id . '" class="change-country-status-id-trigger">' .
                '<input type="hidden" value="' . $uniqId . '" class="entity-uniq-id">' .
                '<span id="' . $uniqId . '_route_name" class="route-name">' . $this->nameOfficial . '</span>' .
                '<div class="choose_btn">' .
                    \app\widgets\SelectEntityWidget::widget([
                        'name' => $selectEntityWidgetName,
                        'model' => $this,
                        'parent_selector' => '#webix_grid_grid_historystatuses',
                        'linked_field'=>$uniqId . '_route_id', // связанное поле в которое будет записано значение при выборе
                        'with_creation'=>true,

                        'select_tab_title'=>Yii::t('tab_title', 'Country routes').': '.Yii::t('tab_title', 'search_command'), // надпись таба при выборе
                        'select_url'=>Url::to(['dictionaries/country/index']), // урл таба при выборе
                        'select_tab_uniqname'=>'findcountryroutes_' . $uniqId, // уникальное имя таба при выборе

                        'view_tab_title'=>Yii::t('tab_title', 'country_name').' {0} '.Yii::t('tab_title', 'view_command'), // надпись таба при просмотре
                        'view_url'=>Url::to(['dictionaries/country/view']), //  урл таба при просмотре выбранной сущности
                        'view_tab_uniqname'=>'countryroute_' . $uniqId, // уникальное имя таба при просмотре. вместо {0} будет подставлен id
                    ]) .
                '</div>' .
            '</div>';
    }

    public function getFilters(){

        return  [
            ['id'=>'f_country_state', 'type'=>self::FILTER_DROPDOWN,
             'items'=>$this->getStateList(true), 'operation' => '=', 'field' => 'state'],

            ['id'=>'f_country_lang', 'type'=>self::FILTER_DROPDOWN, 'value'=>Yii::$app->language,
                'items'=>Langs::$Names, 'operation' => '=', 'field' => 'lang'],

            ['id'=>'f_country_id', 'field' => 'id','operation' => '='],
            ['id'=>'f_country_nameshort', 'field' => 'name_short','operation' => 'starts'],
            ['id'=>'f_country_digital', 'field' => 'digital_code','operation' => '='],
            ['id'=>'f_country_alpha2', 'field' => 'alpha2_code','operation' => '='],
            ['id'=>'f_country_alpha3', 'field' => 'alpha3_code','operation' => '='],
            ['id'=>'f_country_visible', 'type'=>self::FILTER_DROPDOWN, 'value'=>'',
                'items'=>$this->getVisibilityList(true), 'operation' => '=', 'field' => 'visible'],
            ['id' => 'f_tariff_zone', 'type' => self::FILTER_SELECT2, 'value' => '',
                'items' => ListTariffZone::getList(null, true), 'operation' => '=', 'field' => 'tariff_zone',

                'use_select_widget' => true,
                'select_tab_title'=>Yii::t('tab_title', 'country_full_name').': '. Yii::t('tab_title', 'Tariff zone') .' '.Yii::t('tab_title', 'search_command'),
                'select_url'=>Url::to(['dictionaries/list-tariff-zone/index']),
                'select_tab_uniqname'=>'country_tariffzone',
                'view_tab_title'=> Yii::t('tab_title', 'Tariff zone') .' {0} '.Yii::t('tab_title', 'view_command'),
                'view_url'=>Url::to(['dictionaries/list-tariff-zone/view']),
                'view_tab_uniqname'=>'tariff_zone_{0}',
            ],

        ];
    }


    public function getEmployeeOptions(&$employees){

        $optEmpl = [];
        if ($this->countryInfo != null) {

            $nationalDirector = Employee::getById($this->nationalDirector);

            if ($nationalDirector != null && ($nationalDirector->visible == Employee::INVISIBLE || $nationalDirector->state == CommonModel::STATE_DELETED)) {
                $employees[$nationalDirector->id] = $nationalDirector->surnameFull;
                $optEmpl['options'][$nationalDirector->id] = ['hidden' => true];
            }
        }

        return $optEmpl;
    }

    public function getCurrencyOptions(&$currencies){
        $optCurr = [];

        if ($this->countryInfo != null) {
            $currLimitIndividual = Currency::getById($this->currLimitIndividual);
            $currLimitLegal = Currency::getById($this->currLimitLegal);
            $nationalCurrency = Currency::getById($this->nationalCurrency);

            if ($currLimitIndividual != null && ($currLimitIndividual->visible == Currency::INVISIBLE || $currLimitIndividual->state == CommonModel::STATE_DELETED)) {
                $currencies[$currLimitIndividual->id] = $currLimitIndividual->nameFull;
                $optCurr['options'][$currLimitIndividual->id] = ['hidden' => true];
            }
            if ($currLimitLegal != null && ($currLimitLegal->visible == Currency::INVISIBLE || $currLimitLegal->state == CommonModel::STATE_DELETED)) {
                $currencies[$currLimitLegal->id] = $currLimitLegal->nameFull;
                $optCurr['options'][$currLimitLegal->id] = ['hidden' => true];
            }
            if ($nationalCurrency != null && ($nationalCurrency->visible == Currency::INVISIBLE || $nationalCurrency->state == CommonModel::STATE_DELETED)) {
                $currencies[$nationalCurrency->id] = $nationalCurrency->nameFull;
                $optCurr['options'][$nationalCurrency->id] = ['hidden' => true];
            }
        }

        return $optCurr;
    }

    public function getGridOperations() {

        return parent::getGridOperations() + [
            self::OPERATION_COPY => Yii::t('app', 'Copy'),
        ];
    }

    public function getGridOperationsOptions() {

        return parent::getGridOperationsOptions() + [
            self::OPERATION_COPY => ['url' => Url::to(['create']),  'separator_before' => true, 'tab_name_sufix' => 'copy'],
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListRegions()
    {
        return $this->hasMany(ListRegion::className(), ['country' => 'id']);
    }


    /**
     * Получение модели тарифконй зоны
     * @return \yii\db\ActiveQuery
     */
    public function getTariffZone() {
        return $this->hasOne(ListTariffZone::className(), ['id' => 'tariff_zone']);
    }
}

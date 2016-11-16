<?php

namespace app\models\dictionaries\address;

use app\models\common\CommonModel;
use Yii;

/**
 *  Справочник населеных пунктов (контактная информация)
 *
 * @property string $city Ссылка на справочник нас.пунктов
 * @property string $office_addr_en Адрес офиса англ.
 * @property string $office_addr_ru Адрес офиса рус.
 * @property string $office_addr_uk Адрес офиса укр.
 * @property string $office_addr_short_en Адрес офиса (короткий) англ.
 * @property string $office_addr_short_ru Адрес офиса (короткий) рус.
 * @property string $office_addr_short_uk Адрес офиса (короткий) укр.
 * @property string $phone_sales_department Телефон отдела продаж
 * @property string $phone_courier Телефон вызова курьера
 * @property string $email_sales_department E-mail отдела продаж
 * @property string $street
 * @property string $buildingtype_level1
 * @property string $number_level1
 * @property string $buildingtype_level2
 * @property string $number_level2
 * @property string $buildingtype_level3
 * @property string $number_level3

 *
 * @property ListCity $cityModel
 * @property ListAdressKind addressKindModel
 * @property integer addressKind
 * @property ListStreet streetModel
 * @property mixed buildingtypeLevel1
 * @property mixed buildingtypeLevel2
 * @property mixed buildingtypeLevel3
 */
class ListCityContact extends CommonModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%list_city_contact}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge (parent::rules(),
        [
            [['city'], 'required'],
            [['city','street', 'buildingtype_level1', 'buildingtype_level2', 'buildingtype_level3'], 'integer'],
            [['phone_sales_department', 'phone_courier', 'email_sales_department','number_level1','number_level2', 'number_level3'], 'string', 'max' => 50],
            [['main_office_indexes'], 'string', 'max' => 20]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'city' => Yii::t('app', 'City'),
            'office_addr_en' => Yii::t('address', 'Office Addr En'),
            'office_addr_ru' => Yii::t('address', 'Office Addr Ru'),
            'office_addr_uk' => Yii::t('address', 'Office Addr Uk'),
            'office_addr_short_en' => Yii::t('address', 'Office Addr Short En'),
            'office_addr_short_ru' => Yii::t('address', 'Office Addr Short Ru'),
            'office_addr_short_uk' => Yii::t('address', 'Office Addr Short Uk'),
            'phone_sales_department' => Yii::t('address', 'Phone Sales Department'),
            'phone_courier' => Yii::t('address', 'Phone Courier'),
            'email_sales_department' => Yii::t('address', 'Email Sales Department'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCityModel()
    {
        return $this->hasOne(ListCity::className(), ['id' => 'city']);
    }


    public function getAddressKind() {
        return 1;
    }

    public function getAddressKindModel() {
        return ListAdressKind::findOne(['id' => $this->addressKind]);
    }

    public function getStreetModel(){
        return $this->hasOne(ListStreet::className(), ['id' => 'street']);
    }

    public function getOffice_addr_short_en(){
        return $this->getAddressShort('en');
    }

    public function getOffice_addr_short_uk(){
        return $this->getAddressShort('uk');
    }

    public function getOffice_addr_short_ru(){
        return $this->getAddressShort('ru');
    }

    public function getOffice_addr_en(){
        return $this->getAdress_full('en');
    }

    public function getOffice_addr_uk(){
        return $this->getAdress_full('uk');
    }

    public function getOffice_addr_ru(){
        return $this->getAdress_full('ru');
    }

    public function getAdress_full($lang)
    {
        if (!$this->streetModel)
            return '';

        if(!$lang)
            $lang = Yii::$app->language;

        $result = '';

        // страна
        $result.= $this->streetModel->cityModel->regionModel->countryModel->namesOfficial[$lang];

        // регионы
        $result.= ', '.$this->streetModel->cityModel->regionModel->parent->{"name_$lang"}.' '.$this->streetModel->cityModel->regionModel->{"name_$lang"};


        // город
        $result.= ', '. $this->streetModel->cityModel->{"name_$lang"};

        // короткий адрес
        $result .= ', '.$this->getAddressShort($lang);

        return $result;
    }

    /**
     * Получить краткий адрес
     * @param string $lang
     * @return string
     */
    public function getAddressShort($lang=null) {

        if ($lang)
            $lang = Yii::$app->language;

        $addr = $this->streetModel->streetTypeModel->{"name_$lang"} . ' ' . $this->streetModel->{"name_$lang"} . ' ' .
            $this->buildingtypeLevel1->{"name_$lang"} . ' ' . $this->number_level1;

        if ($this->buildingtypeLevel2)
            $addr .= ' ' . $this->buildingtypeLevel2->{"name_$lang"} . ' ' . $this->number_level2;
        if ($this->buildingtypeLevel3)
            $addr .= ' ' . $this->buildingtypeLevel3->{"name_$lang"} . ' ' . $this->number_level3;

        return $addr;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuildingtypeLevel1()
    {
        return $this->hasOne(ListBuildingType::className(), ['id' => 'buildingtype_level1']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuildingtypeLevel2()
    {
        return $this->hasOne(ListBuildingType::className(), ['id' => 'buildingtype_level2']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuildingtypeLevel3()
    {
        return $this->hasOne(ListBuildingType::className(), ['id' => 'buildingtype_level3']);
    }
}

<?php

namespace app\models\dictionaries\country;

use app\models\dictionaries\currency\Currency;
use Yii;
use yii\db\ActiveRecord;
use app\models\dictionaries\employee\Employee;

/**
 * Модель для дополнительной информации о стране
 * @author Richok FG
 * @category country
 *
 * @property integer $country_id
 * @property integer $national_director
 * @property integer $lim_cdv_pperson
 * @property integer $curr_lim_pperson
 * @property integer $lim_cdv_jperson
 * @property integer $curr_lim_jperson
 * @property integer $national_currency
 * @property string $additional_information
 *
 * @property Country $country
 * @property Employee $nationalDirector
 * @property Currency $currLimPperson
 * @property Currency $currLimJperson
 * @property Currency $nationalCurrency
 */
class CountryInfo extends ActiveRecord
{
    /**
     * Возвращает имя таблицы в базе данных
     * @return string имя таблицы
     */
    public static function tableName()
    {
        return '{{%country_info}}';
    }

    /**
     * Правила для полей
     * @return array массив правил
     */
    public function rules()
    {
        return [
            [['country_id'], 'required'],
            [['country_id', 'national_director', 'lim_cdv_pperson', 'curr_lim_pperson', 'lim_cdv_jperson', 'curr_lim_jperson', 'national_currency'], 'integer'],
            [['additional_information'], 'string', 'max' => 500]
        ];
    }

    /**
     * Получение объекта с дополнительной информацие по id страны
     * @param int $id идентификатор записи с дополнительной информацией
     * @return CountryInfo модель дополнительной информации
     */
    public static function getByCountryId($id) {
        return static::findOne(['country_id' => $id]);
    }
    public function getEmployee() {

        return $this->hasOne(Employee::className(), ['id' => 'national_director']);
    }
}

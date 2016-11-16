<?php
/**
 * В файле описан класс модели для параметров печати инвойса
 *
 * @author Гайдаенко О.В.
 * @category Setup
 */
namespace app\models\common;
/**
 * Модель манифеста
 *
 * @property integer $user_id Уникальный идентификатор юзера
 * @property integer $print_delivery_cost Идентификатор настройки печати стоимости доставки
 * @property integer $print_third_party Идентификатор настройки печати стоимости третей стороны
 * @property integer $print_actual_weight Идентификатор настройки печати фактического веса
 * @property integer $print_disp_actual_weight
 * @property integer $print_disp_third_party
 * @property integer $print_disp_delivery_cost
 */

class PrintSetupUser extends \app\models\common\CommonModel {

    public $defaultPrintSetup = [
        'print_third_party' => 2,
        'print_delivery_cost' => 5,
        'print_actual_weight' => 8,

        'print_disp_third_party' => 0,
        'print_disp_delivery_cost' => 0,
        'print_disp_actual_weight' => 0
    ];

    public static function tableName() {
        return '{{%params_users}}';
    }

    public function rules() {
        return [
            [['print_delivery_cost', 'print_third_party', 'print_actual_weight',
                'print_disp_actual_weight', 'user_id', 'print_disp_third_party', 'print_disp_delivery_cost'], 'integer']
        ];
    }

    public static function getListOptionsThirdParty() {
        return [
            1 => \Yii::t('setup', 'Всегда запрашивать при печати/ выгрузке'),
            2 => \Yii::t('setup', 'Всегда отображать 3-ю сторону'),
            3 => \Yii::t('setup', 'Никогда не отображать 3-ю сторону'),

        ];
    }

    public static function getListOptionsDeliveryCost() {
        return [
            4 => \Yii::t('setup', 'Всегда запрашивать при печати/ выгрузке'),
            5 => \Yii::t('setup', 'Всегда отображать стоимость доставки'),
            6 => \Yii::t('setup', 'Никогда не отображать стоимость доставки'),

        ];
    }

    public static function getListActualWeight() {
        return [
            7 => \Yii::t('setup', 'Всегда запрашивать при печати/ выгрузке'),
            8 => \Yii::t('setup', 'Всегда отображать вес'),
            9 => \Yii::t('setup', 'Никогда не отображать общий вес'),

        ];
    }

    public function attributeLabels() {
        return [
            'print_third_party'   => \Yii::t('setup', 'Третья сторона'),
            'print_delivery_cost' => \Yii::t('setup', 'Стоимость доставки'),
            'print_actual_weight' => \Yii::t('setup', 'Общий вес')
        ];
    }

    public function beforeSave($insert) {
        if(parent::beforeSave($insert)) {
            $this->user_id = \Yii::$app->user->id;
            return true;
        }
        else {
            return false;
        }
    }

    public function afterSave() {
        \Yii::$app->session->set('printSetupUserModel', serialize($this));
    }
}
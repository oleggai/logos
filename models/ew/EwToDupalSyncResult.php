<?php

namespace app\models\ew;

use app\models\common\CommonModel;
use Yii;


/**
 * Модель резултатов синхронизации накладной с друпалом
 *
 * @property integer $ew_id Номер накладной
 * @property string $datesync Дата синхронизации
 * @property string $message Сообщение (ok или ошибка)
 */
class EwToDupalSyncResult extends CommonModel
{
    /**
     * Полечение имени таблицы в базе данных
     */
    public static function tableName()
    {
        return '{{%ew_to_dupal_sync_result}}';
    }

    /**
     * Правила для полей
     */
    public function rules()
    {
        return [
            [['ew_id'], 'required'],
            [['ew_id'], 'integer'],
            [['datesync'], 'safe'],
            [['message'], 'string', 'max' => 255]
        ];
    }

    /**
     * Надписи для полей
     */
    public function attributeLabels()
    {
        return [
            'ew_id' => Yii::t('ew', 'Ew ID'),
            'datesync' => Yii::t('ew', 'Datesync'),
            'message' => Yii::t('ew', 'Message'),
        ];
    }

    /**
     * Метод определения был ли результат успешным или произошла ошибка
     */
    public function isError(){
        return $this->message != 'ok';
    }
}

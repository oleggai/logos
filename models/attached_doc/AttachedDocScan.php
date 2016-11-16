<?php

/**
 * Файл класса AttachedDocScan
 * Сканы для прикрепленного документа
 */

namespace app\models\attached_doc;

use app\models\common\DateFormatBehavior;
use app\models\common\sys\SysFiles;
use app\models\dictionaries\access\User;
use Yii;
use app\models\common\CommonModel;

/**
 * Класс AttachedDocScan
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 *
 * @property integer $attacheddoc_id. Ссылка на прикрепленный документ
 * @property integer $files_id. Ссылка на
 * @property integer $cr_user_id. Пользователь добавивший - Ссылка на справочник пользователей
 * @property string $cr_date. Дата добавления
 * @property AttachedDoc $attachedDoc. Прикрепленный документ
 * @property User $crUser. Пользователь добавивший - Ссылка на справочник пользователей
 */
class AttachedDocScan extends CommonModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%attached_doc_scans}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['attacheddoc_id', 'files_id', 'cr_user_id'], 'required'],
            [['attacheddoc_id', 'files_id', 'cr_user_id'], 'integer'],
            [['_cr_date'], 'validateDate'],
        ];
    }

    /**
     * @return array
     */
    function behaviors() {
        return [
            [
                'class' => DateFormatBehavior::className(),
                'attributes' => [
                    '_cr_date' => 'cr_date',
                ]
            ],
        ];
    }

    /**
     * Проверка формата даты времени
     * @param $attribute string Имя атрибута даты
     * @internal param $params
     */
    public function validateDate($attribute) {
        if (!DateFormatBehavior::validate($_POST['AttachedDocFile'][$attribute]))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'attacheddoc_id' => Yii::t('attach-document', 'Id'),
            'files_id' => Yii::t('attach-document', 'Id'),
            '_cr_date' => Yii::t('attach-document', 'Doc Date'),
            'cr_user_id' => Yii::t('attach-document', 'User'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDoc() {
        return $this->hasOne(AttachedDoc::className(), ['id' => 'attacheddoc_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCrUser() {
        return $this->hasOne(User::className(), ['id' => 'cr_user_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSysFile() {
        return $this->hasOne(SysFiles::className(), ['files_id' => 'id']);
    }
}

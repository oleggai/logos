<?php

/**
 * Файл класса AttachedDocFile
 * Файлы для прикрепленного документа
 */

namespace app\models\attached_doc;

use app\models\common\DateFormatBehavior;
use app\models\common\sys\SysFiles;
use app\models\dictionaries\access\User;
use Yii;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * Класс AttachedDocFile
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 *
 * @property integer $attacheddoc_id. Ссылка на прикрепленный документ
 * @property integer $files_id. Ссылка на
 * @property SysFiles $sysFile. Обьект файла
 * @property string $doc_name. Название файла пользователем
 * @property integer $cr_user_id. Пользователь добавивший - Ссылка на справочник пользователей
 * @property integer $cr_date. Дата добавления
 * @property AttachedDoc $attachedDoc. Прикрепленный документ
 * @property User $crUser. Пользователь добавивший - Ссылка на справочник пользователей
 */
class AttachedDocFile extends AttachedDocCommon {

    /**
     * @var string $entityName. Требуеться знать в какую таблицу сохранять данные
     */
    public $entityName = '';

    /**
     * @var string $entityId. Ид сохраняемой сущности
     */
    public $entityId = '';

    public function __construct($entityName = '', $entityId = '') {
        parent::__construct();
        $this->entityName = $entityName;
        $this->entityId   = $entityId;
    }

    /**
     * @var UploadedFile file attribute
     */
    public $file;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%attached_doc_files}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['attacheddoc_id', 'files_id', 'cr_user_id'], 'required'],
            [['attacheddoc_id', 'files_id', 'cr_user_id'], 'integer'],
            [['_cr_date'], 'validateDate'],
            [['doc_name'], 'string', 'max' => 50],
            [['file'], 'file'],
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

    public function getState() {
        return self::STATE_CREATED;
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
            'doc_name' => Yii::t('attach-document', 'Doc name'),
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
        return $this->hasOne(User::className(), ['user_id' => 'cr_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSysFile() {
        return $this->hasOne(SysFiles::className(), ['id' => 'files_id']);
    }

    /**
     * Метод вызывается после сохранения сущности
     */
    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);
        $this->saveSServiceData($insert);
    }

    public function toJson() {

        return [
            'id' => $this->id,
            'id_file'   => $this->sysFile->id,
            'doc_name' => $this->doc_name,
            'user_name' => $this->crUser->employee->surnameFull,
            'file_name' => $this->sysFile->file_name,
            'file_path' => $this->sysFile->file_path,
            'upload_date' => $this->cr_date,
            'country' => $this->crUser->employee->country->nameShort,
            'city' => $this->crUser->employee->city,
            'warehouse' => $this->crUser->employee->warehouseModel ? $this->crUser->employee->warehouseModel->name : '' ,
            'url_download_file' => Url::to(['site/get-file', 'attached_doc_file_id' => $this->id]),
        ];
    }
}

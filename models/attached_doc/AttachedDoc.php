<?php

/**
 * Файл класса AttachedDoc
 * Прикрепленные документы
 */

namespace app\models\attached_doc;

use app\models\common\DateFormatBehavior;
use app\models\counterparty\Counterparty;
use app\models\counterparty\CounterpartyContract;
use app\models\ew\ExpressWaybill;
use app\models\manifest\Manifest;
use Yii;
use app\models\common\CommonModel;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * Класс AttachedDoc
 *
 * @author Гайдаенко Олег
 * @category Attach Document
 *
 * @property integer $id
 * @property string $doc_num. Номер документа
 * @property string $doc_date. Дата документа
 * @property string $doc_issued. Выдан
 * @property string $addition_info. Дополнительная информация
 * @property ListAttachDocType $docType. Тип документа - Ссылка на Справочник типов прикрепленных документов
 * @property AttachedDocFile[] $attachedDocFiles. Массив обьектов файлов
 */
class AttachedDoc extends AttachedDocCommon {

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

    public function getEntityName() {
        return $this->entityName;
    }

    public function getEntityId() {
        return $this->entityId;
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%attached_doc}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['doc_type'], 'required'],
            [['id', 'doc_type'], 'integer'],
            [['_doc_date'], 'validateDate'],
            [['doc_issued'], 'string', 'max' => 100],
            [['addition_info'], 'string', 'max' => 200],
            [['doc_num'], 'string', 'max' => 50]
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
                    '_doc_date' => 'doc_date',
                ]
            ],
        ];
    }

    public function afterSave($insert, $changedAttributes) {

        parent::afterSave($insert, $changedAttributes);
        $this->saveSServiceData($insert);

        switch($this->entityName) {
            case ExpressWaybill::ENTITY_NAME:
                //
                if($insert) {
                    $ewAttachedDoc = new EwAttachedDoc();
                    $ewAttachedDoc->ew_id = $this->entityId;
                    $ewAttachedDoc->attdoc_id = $this->id;
                    $ewAttachedDoc->save();
                }
                break;
            case Manifest::ENTITY_NAME:
                //
                if($insert) {
                    $mnAttachedDoc = new MnAttachedDoc();
                    $mnAttachedDoc->mn_id = $this->entityId;
                    $mnAttachedDoc->attdoc_id = $this->id;
                    $mnAttachedDoc->save();
                }
                break;
            case Counterparty::ENTITY_NAME:
                //
                if($insert) {
                    $cpAttachedDoc = new CpAttachedDoc();
                    $cpAttachedDoc->cnt_id = $this->entityId;
                    $cpAttachedDoc->attdoc_id = $this->id;
                    $cpAttachedDoc->save();
                }
                break;
            case CounterpartyContract::ENTITY_NAME:
                //
                if($insert) {
                    $cpContractAttachedDoc = new CpContractAttachedDoc();
                    $cpContractAttachedDoc->cntcontr_id = $this->entityId;
                    $cpContractAttachedDoc->attdoc_id = $this->id;
                    $cpContractAttachedDoc->save();
                }
                break;
        }
    }

    /**
     * Получение списка типов прикрепленныъ документов в виде ассоциативного массива, где ключ - id, значение - значение поля переданного параметром ('nameFull' по-умолчанию)
     * @param bool $empty первое пустое
     * @return array массив типов прикрепленных документов
     */
    public static function getListTypeAttachedDoc($empty = false) {
        $field = 'name_'.Yii::$app->language;
        $arr = ListAttachDocType::find()->where('visible = :visible AND state != :state', [':visible' => self::VISIBLE, ':state' => CommonModel::STATE_DELETED])->all();
        $r = ArrayHelper::map($arr, 'id', $field);

        if ($empty)
            $r = [null => ''] + $r;
        return $r;
    }

    /**
     * Возвращает модель связи между сущьностью и ПД
     * @param string $entityId
     * @param string $attDocId
     * @param string $entityName
     * @return null|static
     * @throws NotFoundHttpException
     */
    public static function getEntityModel($entityId = '', $attDocId = '', $entityName = '') {
        switch($entityName) {
            case ExpressWaybill::ENTITY_NAME:
                //
                if (($model = EwAttachedDoc::findOne(['ew_id' => $entityId, 'attdoc_id' => $attDocId])) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
            case Manifest::ENTITY_NAME:
                //
                if (($model = MnAttachedDoc::findOne(['mn_id' => $entityId, 'attdoc_id' => $attDocId])) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
            case Counterparty::ENTITY_NAME:
                //
                if (($model = CpAttachedDoc::findOne(['cnt_id' => $entityId, 'attdoc_id' => $attDocId])) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
            case CounterpartyContract::ENTITY_NAME:
                //
                if (($model = CpContractAttachedDoc::findOne(['cntcontr_id' => $entityId, 'attdoc_id' => $attDocId])) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
            default:

                throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                break;
        }
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
        if (!DateFormatBehavior::validate($_POST['AttachedDoc'][$attribute]))
            $this->addError($attribute, Yii::t('app', 'Date format error'));
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('attach-document', 'Id'),
            'doc_type' => Yii::t('attach-document', 'Doc Type'),
            '_doc_date' => Yii::t('attach-document', 'Doc Date'),
            'doc_issued' => Yii::t('attach-document', 'Doc Issued'),
            'addition_info' => Yii::t('attach-document', 'Addition Info'),
            'doc_num' => Yii::t('attach-document', 'Doc Num'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocType() {
        return $this->hasOne(ListAttachDocType::className(), ['id' => 'doc_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachedDocFiles() {
        return $this->hasMany(AttachedDocFile::className(), ['attacheddoc_id' => 'id']);
    }

    /**
     * Проверка наличия файлов, прикрепленных к ПД
     * @return bool
     */
    public function checkHasFiles() {
        return ($this->getAttachedDocFiles()->count() > 0);
    }

    /**
     * Получение полей модели в виде массива для json
     * @return array Массив имя_поля => значение
     */
    public function toJson() {
        $path = Yii::$app->request->baseUrl.Yii::getAlias('@pathToPictures').'/attached_doc/file.png';
        return [
            'id' => $this->id,
            'doc_num' => $this->doc_num,
            'doc_date' => $this->doc_date,
            'doc_issued' => $this->doc_issued,
            'addition_info' => $this->addition_info,
            'doc_title' => $this->docType->name,
            'file_img' => $this->checkHasFiles() ? $path : ''
        ];
    }
}

<?php

namespace app\controllers\common;

use app\controllers\CommonController;
use app\models\attached_doc\AttachedDoc;
use app\models\attached_doc\AttachedDocFile;
use app\models\counterparty\Counterparty;
use app\models\counterparty\CounterpartyContract;
use app\models\ew\ExpressWaybill;
use app\models\manifest\Manifest;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class AttachedDocController extends CommonController {


    /**
     * @return int|string
     */
    public function actionGetFileList() {
        $result = [];
        $attachedDocId = \Yii::$app->request->get('attached_doc_id');
        if(!$attachedDocId) {
            return 0;
        }
        $attachedDocFiles = AttachedDocFile::findAll(['attacheddoc_id' => $attachedDocId]);
        foreach($attachedDocFiles as $attachedDocFile) {
            $result[] = $attachedDocFile->toJson();
        }
        return Json::encode($result);
    }

    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreateFile() {
        $getParams = \Yii::$app->request->get();

        $attachedDocId = $getParams['attachedDocId'];
        // Ид прикрепленного документа
        $entityName = $getParams['entityName'];
        // Ид сущности
        $entityId = $getParams['entityId'];

        $attachedDocFileObj = new AttachedDocFile($entityName, $entityId);

        if(\Yii::$app->request->isPost) {

            // $entityObj это модель ЕН, манифеста или контрагента или ище какая-то сущность
            $entityObj = $this->findModel($entityId, $entityName);
            $attachedDocFileObj->file = UploadedFile::getInstance($attachedDocFileObj, 'file');

            if($attachedDocFileObj->file) {

                $attachedDocFileObj->doc_name = \Yii::$app->request->post('AttachedDocFile')['doc_name'];
                $attachedDocFileObj->attacheddoc_id = $attachedDocId;

                $contentFile = file_get_contents($attachedDocFileObj->file->tempName);
                $attDocData['data'] = $contentFile;
                $attDocData['fileName'] = $attachedDocFileObj->file->name;

                $attachedDocFileObj = $entityObj->addFilesToAttachedDoc($attachedDocFileObj, $attDocData);
                if($attachedDocFileObj->files_id) {
                    return $this->redirect(['common/attached-doc-file/view',
                        'id' => $attachedDocFileObj->id,
                        'entityName' => $entityName,
                        'entityId' => $entityId
                    ]);
                }
            }
        }
        return $this->render('create-file', ['model' => $attachedDocFileObj]);
    }


    /**
     * @return string
     */
    public function actionCreateAttachedDoc() {
        // Информация о том, в какую таблицу сохранять данные (ид_сущности, ид_новосозданного_прикрепленного_документа)
        $entityName = \Yii::$app->request->get('entityName');
        $entityId = \Yii::$app->request->get('entityId');

        $model = new AttachedDoc($entityName, $entityId);
        if(\Yii::$app->request->isPost) {
            $model->load(\Yii::$app->request->post());
            if($model->save()) {
                return $this->redirect(['view',
                    'id' => $model->id,
                    'entityName' => $entityName,
                    'entityId' => $entityId
                ]);
            }
        }
        return $this->render('form', ['model' => $model]);
    }

    /**
     * Поиск моделей по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param string $entityName идентификатор сущности
     * @param string $entityId идентификатор сущности
     * @return AttachedDoc
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($entityId = '', $entityName = '')
    {
        switch($entityName) {
            case ExpressWaybill::ENTITY_NAME:
                //
                if (($model = ExpressWaybill::findOne($entityId)) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
            case Manifest::ENTITY_NAME:
                //
                if (($model = Manifest::findOne($entityId)) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
            case Counterparty::ENTITY_NAME:
                //
                if (($model = Counterparty::findOne($entityId)) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
            case CounterpartyContract::ENTITY_NAME:
                //
                if (($model = CounterpartyContract::findOne($entityId)) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
            default:
                //
                if (($model = AttachedDoc::findOne($entityId)) !== null) {
                    return $model;
                } else {
                    throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
                }
                break;
        }
    }
}
<?php

namespace app\controllers\common;

use app\controllers\CommonController;
use app\models\attached_doc\AttachedDocFile;
use yii\web\NotFoundHttpException;

class AttachedDocFileController extends CommonController {

    public function getForm() {
        return 'view-file';
    }

    /**
     * @return string
     */
    public function actionSaveFileInfo() {
        $getParams = \Yii::$app->request->get();
        $idFile = $getParams['id'];
        $entityName = $getParams['entityName'];
        $entityId = $getParams['entityId'];
        $attachedDocFile = AttachedDocFile::findOne(['id' => $idFile]);
        if($entityName && $entityId) {
            $attachedDocFile->entityName = $entityName;
            $attachedDocFile->entityId = $entityId;
        }
        if(\Yii::$app->request->isPost) {
            if($attachedDocFile->load(\Yii::$app->request->post()) && $attachedDocFile->validate()) {
                if($attachedDocFile->save()) {
                    return $this->redirect(['view',
                        'id' => $attachedDocFile->id,
                        'entityName' => $entityName,
                        'entityId' => $entityId
                    ]);
                }
            }
        }
        return $this->render('view-file', ['model' => $attachedDocFile]);
    }

    /**
     * Поиск моделей по первичному ключу
     * Если модель не найдена генерируется 404 HTTP исключение
     * @param integer $id идентификатор сущности
     * @return AttachedDocFile, загруженная модель файла для прикрепленного документа
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id) {
        if (($model = AttachedDocFile::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
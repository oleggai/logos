<?php

namespace app\classes;

use yii\base\Behavior;

class DocumentStorage extends Behavior {

/*    public $a = 10;

    public function getEwNum() {
        return $this->owner->ew_num;
    }*/

    private $storageConnector;

    public function __construct() {
        parent::__construct();
        $this->storageConnector = new StorageConnector();
    }

    public function getAttachDoc($attdoc_id) {

    }

    public function getFileAttachDoc($attdoc_id, $file_id) {

    }

    public function addFilesToAttachedDoc($attachedDocFileObj, array $attDocData) {
        $userId = \Yii::$app->user->id;
        $className = $this->owner->className();
        $entityName = $className::ENTITY_NAME;
        $idRes = $this->storageConnector->saveFile($userId, $attDocData['fileName'], $attDocData['data'], ['entity_id' => $this->owner->id, 'entity_code' => $entityName]);
        if($idRes) {
            $attachedDocFileObj->files_id = $idRes;
            $attachedDocFileObj->cr_user_id = $userId;
            if($attachedDocFileObj->validate()) {
                $attachedDocFileObj->save();
            }
        }
        return $attachedDocFileObj;
    }

    public function getList() {

    }
}
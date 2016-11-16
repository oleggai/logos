<?php
/**
 * В файле описан класс виджета, для верхней панели управоения сущностью
 *
 * @author Richok FG
 * @category Интрерфейс
 */

namespace app\widgets;

use app\models\common\CommonModel;
use app\models\common\DateFormatBehavior;
use app\models\common\sys\SysEntity;
use Yii;
use yii\base\Widget;
use yii\helpers\Url;

/**
 * Класс виджета для верхней панели управления сущностью
 * @property mixed usersHtml
 */
class EntityControl extends Widget {

    /**
     * @var string Урль вызова акшина удаления
     */
    public $deleteUrl = null;
    /**
     * @var string Урль вызова акшина закрытия
     */
    public $closeUrl = null;
    /**
     * @var string Урль вызова ашина восстановления
     */
    public $restoreUrl = null;
    /**
     * @var string Урль вызова просмотра сущности
     */
    public $viewUrl = null;
    /**
     * @var string Урль вызова редактирования сущности
     */
    public $updateUrl = null;
    /**
     * @var string Урль вызова акшина редактирования статуса ЭН
     */
    public $editStatusUrl = null;
    /**
     * @var string Урль вызова акшина редактирования причины недоставки ЭН
     */
    public $editNondeliveryUrl = null;
    /**
     * @var string Текст вопроса подтверждения
     */
    public $deleteConfirm;
    /**
     * @var CommonModel модель сущности
     */
    public $model;
    /**
     * @var CommonForm форма редактирования сущности
     */
    public $form;

    public function run() {
        if ($this->deleteUrl == null)
            $this->deleteUrl = Url::to(['delete', 'id' => $this->model->getIdentity(), 'current_operation' => CommonModel::OPERATION_DELETE]);
        if ($this->restoreUrl == null)
            $this->restoreUrl = Url::to(['restore', 'id' => $this->model->getIdentity(), 'current_operation' => CommonModel::OPERATION_CANCEL]);
        if ($this->viewUrl == null)
            $this->viewUrl = Url::to(['view', 'id' => $this->model->getIdentity(), 'current_operation' => CommonModel::OPERATION_VIEW]);
        if ($this->updateUrl == null)
            $this->updateUrl = Url::to(['update', 'id' => $this->model->getIdentity(), 'current_operation' => CommonModel::OPERATION_UPDATE]);
        if ($this->closeUrl == null)
            $this->closeUrl = Url::to(['close', 'id' => $this->model->getIdentity(), 'current_operation' => CommonModel::OPERATION_CLOSE]);
        if ($this->editStatusUrl == null)
            $this->editStatusUrl = Url::to(['edit-status', 'id' => $this->model->getIdentity(), 'current_operation' => CommonModel::OPERATION_CHANGE_STATUS]);
        if ($this->editNondeliveryUrl == null)
            $this->editNondeliveryUrl = Url::to(['edit-nondelivery', 'id' => $this->model->getIdentity(), 'current_operation' => CommonModel::OPERATION_CHANGE_NONDELIVERY]);

        return $this->render('EntityControlWidget', ['widget' => $this]);
    }

    public function getUsersHtml(){

        if (!$this->model || $this->model->operation == CommonModel::OPERATION_VIEW)
            return '';

        $users = SysEntity::getEditingUsers($this->model->getEntityCode(),$this->model->getIdentity());
        $result = '';
        $added_users = [];
        $dateBehavior = new DateFormatBehavior();

        if (sizeof($users)){

            $result .= Yii::t('app','User Operation Date:').'<br>'; // можно убрать

            foreach ($users as $user){

                if (in_array($user->user->user_id, $added_users))
                    continue;

                $result .=

                    BtnCreateTab::widget([
                    'btn_classes'=>'show_user_href btn btn-link',
                    'tab_title'=> Yii::t('tab_title', 'employee_full_name').' '.$user->user_id.' '.Yii::t('tab_title', 'view_command'),
                    'btn_title' => $user->user->employee->surnameShort
                        ." ".Yii::t('operations','operation_'.$user->operation)
                        ." ".$dateBehavior->convertFromStoredFormat($user->operation_date),
                    'ifr_url'=>Url::to(['dictionaries/employee/view', 'id'=>$user->user->employee_id]),
                    ]).'<br>';

                $added_users[] = $user->user->user_id;
            }
        }

        return $result;
    }
}
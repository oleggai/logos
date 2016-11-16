<?php
namespace app\models\common;

use Yii;
use yii\db\ActiveQuery;
use app\models\common\CommonModel;

/**
 * Переопределенный ActiveQuery
 * @author Aleksey Samokhvalov
 */

class CommonQuery extends ActiveQuery
{
    /**
     * Добавляет условие для возврата не удаленных объектов
     * @return ActiveQuery
     */
    public function notDeleted()
    {
        return $this->andWhere(['<>', 'state', CommonModel::STATE_DELETED]);
    }
}

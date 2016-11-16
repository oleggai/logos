<?php
namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\common\MenuItem;
/**
 * Виджет отображения меню
 */
class MenuWidget extends Widget
{
    public $items;

    public function init()
    {
        parent::init();

        $this->items = MenuItem::find()
            ->orderBy('lft')
            ->notDeleted()
            ->all();
    }

    public function run()
    {
        return $this->render('menu-item/widget', [
            'items' => $this->items,
        ]);
    }
}

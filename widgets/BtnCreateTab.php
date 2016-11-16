<?php
namespace app\widgets;

use yii\base\Widget;
use yii\helpers\Url;


class BtnCreateTab extends Widget
{

    public $btn_title;
    public $tab_title;
    public $ifr_url;
    public $unique_tab_id;
    public $return_data_to;
    public $btn_classes;
    public $atributes;
    public $trigger_el_id;

    public function init()
    {
        parent::init();

        if ($this->unique_tab_id === null) {
            $this->unique_tab_id = 'false';
        } else {
            $this->unique_tab_id = $this->unique_tab_id;
        }

        if ($this->return_data_to === null) {
            $this->return_data_to = 'false';
        } else {
            $this->return_data_to = $this->return_data_to;
        }

        if ($this->btn_classes === null)
            $this->btn_classes = '';

        if ($this->atributes === null) $this->atributes = [];

        if ($this->trigger_el_id === null) {
            $this->trigger_el_id = 'false';
        }

    }

    public function run()
    {
        return $this->render('button_create_tab', [
            'btn_classes' => $this->btn_classes,
            'btn_title' => $this->btn_title,
            'tab_title' => $this->tab_title,
            'ifr_url' => Url::to($this->ifr_url),
            'unique_tab_id' => $this->unique_tab_id,
            'return_data_to' => $this->return_data_to,
            'trigger_el_id' => $this->trigger_el_id,
            'atributes' => $this->atributes,

        ]);
    }


    public static function createLink($title, $tab_title, $ifr_url, $btn_classes, $unique_tab_id = 'false') {
        return "<a href=\"javascript:void(0)\" class=\"$btn_classes;\" tab_title=\"$tab_title\" ifr_url=\"$ifr_url\"".
               "unique_tab_id=\"$unique_tab_id\" onclick=\"app_add_new_tab_from_iframe(this);\" oncontextmenu=\"return false;\" \>".
                "$title".
               "</a>";
    }
}
?>
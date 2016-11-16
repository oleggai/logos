<?php

namespace app\classes;

use \yii\helpers\Html;

/**
 * Расширение класса yii\helpers\Html
 * @author Дмитрий Чеусов
 * @category classes
 * 
 */
class CommonHtml extends Html {

    /**
     * Выпадающий список чекбоксов
     * Переписал из parent::checkboxList()
     * @return string the generated checkbox list
     */
    public static function checkboxListDropdown($id, $selection = null, $items = [], $options = []) {
        if (substr($name, -2) !== '[]') {
            $name = $id . '[]';
        } else $name = $id;

        $formatter = isset($options['item']) ? $options['item'] : null;
        $itemOptions = isset($options['itemOptions']) ? $options['itemOptions'] : [];
        $encode = !isset($options['encode']) || $options['encode'];
        $lines = [];
        $index = 0;

        $top = '<dl class="dropdown"><dt>'
                . '<a class="clearablecheckbox" href="#" id="'.$id.'">'
                . '<p class="multiSel multiSel_check_'.$id.'"></p></a>'
                . '<div class="clearablecheckbox_hida hida_check_'.$id.'" id="'.$id.'"></div></dt><dd>'
                . '<div class="mutliSelect"><ul class="'.$id.'">';
        foreach ($items as $value => $label) {
            $checked = $selection !== null &&
                    (!is_array($selection) && !strcmp($value, $selection) || is_array($selection) && in_array($value, $selection));
            if ($formatter !== null) {
                $lines[] = call_user_func($formatter, $index, $label, $name, $checked, $value);
            } else {

                $lines[] = '<li class="dropdown_label"><label class="dropdown_label"><input type="checkbox" value="'.$value.'" id="check_'.$id.'" class="check_'.$id.'" />'
                        .$label.'</label></li>';
            }
            $index++;
        }
        $bottom = '</ul></div></dd></dl>';

        if (isset($options['unselect'])) {
            // add a hidden field so that if the list box has no option being selected, it still submits a value
            $name2 = substr($name, -2) === '[]' ? substr($name, 0, -2) : $name;
            $hidden = static::hiddenInput($name2, $options['unselect']);
        } else {
            $hidden = '';
        }
        $separator = isset($options['separator']) ? $options['separator'] : "\n";

        $tag = isset($options['tag']) ? $options['tag'] : 'div';
        unset($options['tag'], $options['unselect'], $options['encode'], $options['separator'], $options['item'], $options['itemOptions']);

        return $hidden . static::tag($tag, $top . implode($separator, $lines) . $bottom, $options);
    }

}

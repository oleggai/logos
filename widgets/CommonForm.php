<?php
/**
 * Created by PhpStorm.
 * User: goga
 * Date: 08.04.2015
 * Time: 13:41
 */

namespace app\widgets;

use app\models\common\CommonModel;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

class CommonForm extends ActiveForm {
    /**
     * @param array $config
     * @param CommonModel $model
     * @return static
     */
    public static function begin($config = [], $model=null)
    {

        // отображение ошибок в виде модального окна
        if ($model!=null && $model->hasErrors()){




            $errors = $model->getErrors();
            $alertText = '';
            $is_critical = $model->hasErrors(CommonModel::CRITICAL_ATTRIBUTE);
            foreach($errors as $key => $val) {
                $alertText .= ($is_critical ? $val[0] : Html::encode($val[0])).'<br>';
            }

            // это ошибка при одновременной работе, отображение немного по другому
            if ($is_critical){
                Yii::$app->view->registerJs(
                    "parent.show_app_alert_with_close(
                    '".Yii::t('app', 'Attention!')."',
                    '$alertText',
                    '".Yii::t('app', 'OK')."',
                    '".Yii::t('app', 'Cancel')."'
                    )",
                    \yii\web\View::POS_END);
            }
            else {
                Yii::$app->view->registerJs("parent.show_app_alert('".Yii::t('app', 'Errors')."', '$alertText', '".Yii::t('app', 'OK')."')", \yii\web\View::POS_END);
            }

        }




        $selfOptions = [
            'options' => ['autocomplete' => 'off'],
            'enableClientValidation' => false,
            'enableAjaxValidation' => false,
            'fieldConfig' => $model->disableEdit ? ['inputOptions' => ['readonly' => true]] : []
        ];
            //'fieldConfig' => ['template' => "{label}\n{beginWrapper}\n{input}\n{hint}\n{endWrapper}"]];


        $f = parent::begin(array_merge($selfOptions, $config));

        // test
        if ($model && array_key_exists ('state', $model->attributes))
            echo $f->field($model, 'state')->hiddenInput()->label(false);

        if ($model && $model->disableEdit){
            $f->view->registerJs('$("select[readonly]").prop("disabled",true);');
            $f->view->registerJs('$(":checkbox").prop("disabled",true);');
            //$f->view->registerJs('var disableEdit = ' . $model->disableEdit . ';');
        }

        return $f;
    }

    public function field($model, $attribute, $options = []){

        $field = parent::field($model, $attribute, $options);


        // это поле подчиненной модели. поля c именем вида 'submodel[submodel_attribute]'. $matches[3] не пустой
        // для таких полей yii не хочет подсвечивать поля с ошибкой, пробуем сделать это самостоятельно
        if (preg_match('/(^|.*\])([\w\.]+)(\[.*|$)/', $attribute, $matches) && $matches[3]) {
            if ($model->hasErrors($attribute)) {
                $field->options['class'] =  $field->options['class'].' '.$this->errorCssClass;
            }
        }

        return $field;
    }
}
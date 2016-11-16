<?php
    use app\assets\BtnCreateTabAsset;
    
    BtnCreateTabAsset::register($this);
?>

<div class='<?=$btn_classes;?>' tab_title='<?=$tab_title?>' ifr_url='<?=$ifr_url?>' unique_tab_id='<?=$unique_tab_id?>' return_data_to='<?=$return_data_to?>' trigger_el_id='<?=$trigger_el_id?>'
     <?php
       foreach ($atributes as $k=>$v) {echo $k."='".$v."' ";}
     ?>
         onclick='app_add_new_tab_from_iframe(this);' >
        <?=$btn_title?>
</div>
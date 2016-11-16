<?php
    use yii\helpers\Url;
    use yii\helpers\Html;
    use app\models\common\MenuItem;
    use app\assets\MenuAsset;
    
    /* @var $this \yii\web\View */
    
    MenuAsset::register($this);
    $local_menu_color = Yii::$app->params['local_menu_color'];
?>

<nav class="navbar-blue <?php if (isset($local_menu_color) && $local_menu_color != '') echo 'fix-bg-collor-'.$local_menu_color; ?> navbar-fixed-top">
    <div class="container-fluid">

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class='nav navbar-nav'>
                <?php $depth = 1; // root несуществует, поэтому начинаем с 1 уровня ?>
                
                <?php foreach($items as $n => $item): ?>
                    <?php
                        /* @var $item MenuItem */
                    
                        /**
                         * Проверить, имеет ли текущий пункт меню подпункты
                         */
                        $haveItems = false;
                        if (isset($items[$n+1]) && $items[$n+1]->depth > $item->depth) {
                            $haveItems = true;
                        }
                    ?>

                    <?php if ($item->depth == $depth): ?>
                        </li>
                    <?php elseif ($item->depth > $depth): ?>
                        <ul class="dropdown-menu">
                    <?php else: ?>
                        </li>
                        <?php for($i = $depth - $item->depth; $i; $i--): ?>
                            </ul>
                            </li>
                        <?php endfor; ?>
                    <?php endif; ?>
                            
                    <?php if ($item->class == 'divider'): // просто линия ?>
                        <li class="divider">
                    <?php elseif ($item->depth == 1): // корневой элемент (раздел) ?>
                        <li class="dropdown">
                            <a data-toggle="dropdown" class="<?= $item->class; ?> dropdown-toggle">
                                <?= Html::encode($item->name); ?>
                            </a>
                    <?php elseif ($haveItems): // есть подпункты ?>
                        <li class="dropdown-submenu">
                            <a data-toggle="dropdown" class="dropdown-toggle">
                                <?= Html::encode($item->name); ?>
                            </a>
                    <?php else: // просто пункт меню (по клику откроет таб) ?>
                        <li>
                            <a onclick="application_create_new_tab('<?= $item->name; ?>','<?= Url::to([$item->url]); ?>');">
                                <?= Html::encode($item->name); ?>
                            </a>
                    <?php endif; ?>

                    <?php $depth = $item->depth; ?>

                <?php endforeach; ?>

                <?php for($i = $depth; $i; $i--): ?>
                    </li>
                    </ul>
                <?php endfor; ?>
            </ul>

            <ul class="nav pull-right">

            </ul>

        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>






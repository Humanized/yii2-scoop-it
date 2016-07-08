<?php

use kartik\helpers\Html;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$span = isset($model->source->image_medium) ? 8 : 12;
?>
<div class ="well">
    <div class ="row">
        <?php
        if ($span != 12) {
            ?>
            <div class="col-md-4">
                <?= Html::img($model->source->image_medium, []); ?>
            </div>
            <?php
        }
        ?>
        <div class="col-md-<?= $span ?>">
            <h2><?= $model->source->title ?></h2>
            <i><b>Published On: </b> <?= date('d M Y', $model->date_published) . "\n" ?></i>
            <p><?= $model->source->description_raw ?></p>
            <div class="news-item-button">
                <a class="btn btn-primary" target="_blank" href="<?= $model->source->url ?>"<"role="button">Read More</a>
                <?= call_user_func($buttonCallback, $model) ?>

            </div>
        </div>
    </div>
</div>

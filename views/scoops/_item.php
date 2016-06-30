<?php

use kartik\helpers\Html;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div class ="well">
    <div class ="row">
        <div class="col-md-4">
            <?= Html::img($model->source->image_medium); ?>
        </div>
        <div class="col-md-8">
            <h2><?= $model->source->title ?></h2>
            <i><b>Published On: </b> <?= date('d M Y H:i:s', $model->date_published) . "\n" ?></i>
            <p><?= $model->source->description_raw ?></p>
            <div class="news-item-button">
                <a class="btn btn-primary" target="_blank" href="<?= $model->source->url ?>"<"role="button">Read More</a>
     
            </div>
        </div>
    </div>
</div>

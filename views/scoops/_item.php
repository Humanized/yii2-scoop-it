<?php

use kartik\helpers\Html;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$span = isset($model->source->image_medium) ? 8 : 12;

$dataBuffer = [
    'topics' => $model->source->topics,
    'tags' => $model->tags,
];

if (isset($dataBufferCallback)) {
    $dataBuffer = array_merge($dataBuffer, $dataBufferCallback($model));
}
?>
<div class ="well news-item">
    <i><?= date('d M Y', $model->date_published) ?></i><?= isset($headerContentCallback) ? $headerContentCallback($model, $dataBuffer) : NULL ?>
    <h2><?= $model->source->title ?></h2>


    <div class ="row news-item-body">
        <?php
        if ($span != 12) {
            ?>
            <div class="col-md-4 scoop-img-col">
                <div class="scoop-img-container">
                    <?= Html::img($model->source->image_medium, ['class' => 'scoop-img']); ?>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="news-item-inner col-md-<?= $span ?>">
            <div class="news-item-buttons-outer">
                <div class="news-item-buttons-inner">
                    <a class="btn btn-primary" target="_blank" href="<?= $model->source->url ?>"<"role="button">Read More</a>
                    <?= call_user_func($buttonCallback, $model) ?>
                </div>
            </div>
            <p><?= $model->source->description_raw ?></p>
            <table>
                <?php
                if (!empty($dataBuffer['sectors'])) {
                    echo '<tr><td class="related-news-label">Related Subsector(s)</td><td>';
                    $out = '';
                    foreach ($dataBuffer['sectors'] as $sector) {
                        if ($out != '') {
                            $out .=', ';
                        }
                        $out.= $sector->code;
                    }
                    echo $out . '</td></tr>';
                }
                if (!empty($dataBuffer['subjects'])) {
                    echo '<tr><td class="related-news-label">Related Organisation(s)</td>';
                    $out = '';
                    foreach ($dataBuffer['subjects'] as $subject) {
                        if ($out != '') {
                            $out .=', ';
                        }
                        $out.= $subject->name;
                    }
                    echo '<td>' . $out . '</td></tr>';
                }
                if (!empty($dataBuffer['keywords'])) {
                    echo '<tr><td class="related-news-label">Keywords</td><td>';
                    $out = '';
                    foreach ($dataBuffer['keywords'] as $keyword) {
                        if ($out != '') {
                            $out .=', ';
                        }
                        $out.= $keyword->name;
                    }
                    echo $out . '</td></tr>';
                }
                ?>
            </table>
        </div>


    </div>
</div>

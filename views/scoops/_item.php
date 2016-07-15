<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model humanized\scoopit\models\Scoop */

$relatedNewsBuffer = [];
$excludedResults = [$model->id];
$dataBuffer = [
    'topics' => $model->source->topics,
    'tags' => $model->tags,
];
if (isset($dataBufferCallback)) {
    $dataBuffer = array_merge($dataBufferCallback($model), $dataBuffer);
}

$getScoopId = function($scoop) {
    return $scoop->id;
};

$getRelatedNewsLink = function($model) {
    return ['label' => $model->title, 'url' => $model->url];
};

foreach ($dataBuffer as $key => $data) {
    if (!empty($data)) {
        $relatedNewsBuffer[$key] = [];
        foreach ($data as $relatedModel) {
            $relatedModel->excludedNewsItems = $excludedResults;
            $relatedNews = $relatedModel->relatedNews;
            $relatedNewsBuffer[$key][$relatedModel->id] = array_map($getRelatedNewsLink, $relatedNews);
            $excludedResults = array_merge($excludedResults, array_map($getScoopId, $relatedNews));
        }
    }
}

$span = isset($model->source->image_medium) ? 8 : 12;
?>
<div class ="well news-item">
    <i><?= date('d M Y', $model->source->date_retrieved) ?>

    </i>
    <?= isset($headerContentCallback) ? $headerContentCallback($model, $dataBuffer) : NULL ?>
    <h2><?= $model->source->title ?></h2>
    <?= 'By: ' . Html::a(parse_url($model->source->url, PHP_URL_HOST), "http://" . parse_url($model->source->url, PHP_URL_HOST), ['target' => '_blank', 'style' => 'text-decoration:none']) ?>

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
                    <?=
                    isset($relatedNewsItems) ?
                            \yii\bootstrap\ButtonDropdown::widget([
                                'label' => 'Related News',
                                'options' => ['class' => 'btn btn-primary'],
                                'dropdown' => [
                                    'items' => is_callable($relatedNewsItems) ? call_user_func($relatedNewsItems, $relatedNewsBuffer['topics']) : $relatedNewsItems
                        ]]) : ''
                    ?>
                </div>
            </div>
            <p><?= $model->source->description_raw ?></p>
            <?= isset($bodyContentCallback) ? call_user_func($bodyContentCallback, ['model' => $model, 'data' => $dataBuffer, 'related' => $relatedNewsBuffer]) : '' ?>
        </div>


    </div>
</div>

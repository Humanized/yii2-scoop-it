<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model humanized\scoopit\models\Scoop */

/**
 * Visualisation of a news entry for ListView

 * * Outputs result using the following scheme:
 * 
 * ============================================================================
 * News Item Header
 * 
 * {Publication-Date} {:headerContentCallback}
 * {title}
 * {source} 
 * ============================================================================
 * Image (Optional) | News Item Body
 *                  |
 *                  | {read-more}{related-topic-news}
 *                  | {description}{:bodyContentCallback}
 * ============================================================================
 * 
 */
/*
 * Helper functions to used for mapping purposes during in intialisation process
 * Todo: Should be more global
 * 
 */
$getScoopId = function($scoop) {
    return $scoop->id;
};

$getRelatedNewsLink = function($model) {
    return ['label' => $model->title, 'url' => $model->url, 'template' => '<a href="{url}" target="_blank">{label}</a>'
    ];
};


/**
 * Data buffer initialisation
 * 
 * A data-buffer, a container storing related model attributes is created at runtime.
 * It is available to be passed as a parameter, to other interface callbacks.
 * 
 * The data-buffer is intitialised to contain following entries:
 * 
 * - topics: an array of souce-topics related to the source of the result
 * - tags: an array of  scoop-tags related to the result
 */
$dataBuffer = [
    'topics' => $model->source->topics,
    'tags' => $model->tags,
];
/**
 * The databuffer can be customised or further initialised using the dataBufferCallback,
 * a function taking the current result model as parameter 
 * E.g.
 *   function setupDatabuffer($model){
 *      return ['my-index-1'=>[...],'my-index-2'];
 *  }
 */
if (isset($dataBufferCallback)) {
    $dataBuffer = array_merge($dataBuffer, $dataBufferCallback($model));
}

/**
 * Related news buffer initialisation
 * 
 * The interface provides various mechanisms for displaying related news-items for a given result
 * For this, A seperate buffer is created, mapped according to the attribute data-buffer.
 * 
 */
$relatedNewsBuffer = [];

/**
 * 
 * To prevent a single related-news entry from being listed multiple times per entry, 
 * the id's of the already retrieved related news items are stored in a flat list.
 * 
 * On intialisation, the list contains the model-id of the current result
 * 
 * The list can then be passed as parameter, to the individual search processes, 
 * excluding previously retrieved results
 *  
 */
$excludedResults = [$model->id];


/**
 * 
 * Related news buffer creation
 * 
 * A buffer instance, along with the entry formats, as exemplified by default:
 * 
 * [
 *  'topics'=> [
 *          topic-news-id-1 => ['label'=>'topic-news-label-1',url=>'topic-news-url-1'],
 *          topic-news-id-2 => ['label'=>'topic-news-label-2',url=>'topic-news-url-2'],
 *  ],
 *  'tags' => [
 *          tag-news-id-1 => ['label'=>'tag-news-label-1',url=>'tag-news-url-1'],
 *          tag-news-id-2 => ['label'=>'tag-news-label-2',url=>'tag-news-url-2'],
 *  ]
 * ] 
 * 
 * 
 * 
 */
foreach ($dataBuffer as $key => $data) {
    if (!empty($data)) {
        $relatedNewsBuffer[$key] = [];
        foreach ($data as $relatedModel) {
            //Todo: Skip if excludeNewsItems variable and relatedNews function are not defined
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
    <i><?= date('d M Y', $model->source->date_retrieved) ?></i>
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

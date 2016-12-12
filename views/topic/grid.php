<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel humanized\scoopit\models\gui\TopicSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
Pjax::begin();
?>    <?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'topic.name',
        ['label' => 'label', 'attribute' => 'name'],
        ['class' => 'yii\grid\ActionColumn', 'template' => '{delete}',],
    ],
]);
?>
<?php Pjax::end(); ?></div>

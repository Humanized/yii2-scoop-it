<?php

use yii\grid\GridView;

//* @var $this yii\web\View */
/* @var $searchModel humanized\scoopit\models\ScoopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

echo 'yaya';

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'source.title',

    ],
]);

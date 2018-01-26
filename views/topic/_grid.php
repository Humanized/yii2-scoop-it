<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel humanized\scoopit\models\TopicSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$gridColumns = [
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        'id',
        'name',
        'label',
    //   'position',
    ],
];
?>

<?php Pjax::begin(); ?>    <?=

GridView::widget($gridColumns);
?>
<?php Pjax::end(); ?>


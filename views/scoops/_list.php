<?php

use yii\widgets\ListView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel humanized\scoopit\models\ScoopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<?php // echo $this->render('_search', ['model' => $searchModel]);  ?>


<?php Pjax::begin(); ?>    <?=

ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'item'],
    'itemView' => '_item',
])
?>
<?php Pjax::end(); ?>
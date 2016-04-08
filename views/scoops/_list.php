<?php

use yii\widgets\ListView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel humanized\scoopit\models\ScoopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>

<?php  echo $this->render('_search', ['model' => $model]);  ?>


<?php Pjax::begin(['id'=>'news-list']); ?>    <?=

ListView::widget([
    'dataProvider' => $dataProvider,
    'options' => [
        'class' => '.list-view',
    ],
    'itemView' => '_item',
    'summary' => false,


])
?>
<?php Pjax::end(); ?>
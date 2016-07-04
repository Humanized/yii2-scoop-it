<?php

use yii\widgets\ListView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel humanized\scoopit\models\ScoopSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>


<?php Pjax::begin(['id' => 'news-list']); ?>    <?=

ListView::widget([
    'dataProvider' => $dataProvider,
    'itemOptions' => ['class' => 'item'],
    'itemView' => '_item',
    'viewParams' => ['buttonCallback' => $buttonCallback],
    'summary' => false,
    'pager' => [
        'class' => \kop\y2sp\ScrollPager::className(),
        'triggerOffset' => 100,
    ]
])
?>
<?php Pjax::end(); ?>
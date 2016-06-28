<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model humanized\scoopit\models\ScoopSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="scoop-search">

    <?php
    $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
    ]);
    ?>

    <?= $form->field($model, 'title') ?>
    <?php
    // Normal select with ActiveForm & model
    echo $form->field($model, 'keywords')->widget(Select2::classname(), [
        'id' => 'search-label',
        'data' => (\humanized\scoopit\models\gui\SearchLabel::getSelectData()),
        'options' => ['placeholder' => 'Select keywords ...'],
        'pluginOptions' => [
            'multiple' => true,
            'allowClear' => true,
            'minimumInputLength' => 2,
        ],
    ]);
    echo DateRangePicker::widget([
        'model' => $model,
        'attribute' => 'date_published',
        'startAttribute' => 'datetime_min',
        'endAttribute' => 'datetime_max',
        'convertFormat' => true,
        'locale' => [
            'format' => 'd M Y',
            //  'prefix' => 'Published Between ',
            'separator' => ' to ',
        ],
            ]
    );
    ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
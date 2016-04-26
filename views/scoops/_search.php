<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\daterange\DateRangePicker;

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
    echo $form->field($model, 'date_published', [
        'addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
        'options' => ['class' => 'drp-container form-group']
    ])->widget(DateRangePicker::classname(), [
        'useWithAddon' => true,
        'presetDropdown' => true,
        'pluginOptions' => [
            'locale' => [
                'format' => 'd/M/Y',
              //  'prefix' => 'Published Between ',
                'separator' => ' to ',
            ],
            'opens' => 'left'
        ],
        'convertFormat' => true,
    ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
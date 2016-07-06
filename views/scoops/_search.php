<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model humanized\scoopit\models\ScoopSearch */
/* @var $form yii\widgets\ActiveForm */
/*
 * 
 * 
 */
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
        //     'minimumInputLength' => 2,
        ],
    ])->label(false);

    echo $form->field($model, 'date_published', [
        'addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
        'options' => ['class' => 'drp-container form-group']
    ])->widget(DateRangePicker::classname(), [
        'useWithAddon' => true,
        'convertFormat' => true,
        'presetDropdown' => true,
        'pluginOptions' => [
            'locale' => [
                'format' => 'd M Y',
                //  'prefix' => 'Published Between ',
                'separator' => 'to ',
            ],
            'opens' => 'left'
        ],
            /*
              'startAttribute' => 'pub_range_start',
              'endAttribute' => 'pub_range_stop',
             * 
             */
    ]);

    /*
      echo '<label class="control-label">Date Range</label>';
      echo '<div class="drp-container">';
      echo DateRangePicker::widget([
      'model' => $model,
      // 'callback' => false,
      'attribute' => 'date_published',
      'convertFormat' => true,
      'presetDropdown' => true,
      'hideInput' => true,
      'startAttribute' => 'pub_range_start',
      'endAttribute' => 'pub_range_stop',
      'pluginEvents' => [
      //        "show.daterangepicker" => "function() { log(\"show.daterangepicker\"); }",
      //    "hide.daterangepicker" => "function() { log(\"hide.daterangepicker\"); }",
      //    "apply.daterangepicker" => "function() { log(\"apply.daterangepicker\"); }",
      //    "cancel.daterangepicker" => "function() { log(\"cancel.daterangepicker\"); }",
      ],
      'pluginOptions' => [
      // 'autoUpdateInput' => false,
      'locale' => [
      'format' => 'd F Y',
      'prefix' => 'Published Between ',
      'separator' => ' to ',
      ],
      'addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
      'opens' => 'right'
      ],
      ]);
      echo '</div>';
     * 
     */
    /*
      echo $form->field($model, 'date_published', [
      'addon' => ['prepend' => ['content' => '<i class="glyphicon glyphicon-calendar"></i>']],
      'options' => ['class' => 'drp-container form-group']
      ])->widget(DateRangePicker::classname(), [
      'useWithAddon' => true,
      'presetDropdown' => true,
      'pluginOptions' => [
      'locale' => [
      'format' => 'd M Y',
      //  'prefix' => 'Published Between ',
      'separator' => ' to ',
      ],
      'opens' => 'left'
      ],
      'convertFormat' => true,
      ]);
     * 
     */
    ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\widgets\Select2;
?>

<div class="scoop-search">

    <?php
    $form = ActiveForm::begin([
                'method' => 'get',
    ]);
    ?>

    <?= $form->field($model, 'title')->label(false)->textInput(['placeholder' => 'freetext search']); ?>

    <?php
    if (isset($afterSearchTextCallback)) {
        call_user_func($afterSearchTextCallback, $this, $form, $model);
    }
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
                'separator' => ' to ',
            ],
            'opens' => 'left'
        ],
    ]);
    if (isset($afterSearchCalendarCallback)) {
        call_user_func($afterSearchCalendarCallback, $this, $form, $model);
    }
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

    if (isset($afterSearchKeywordCallback)) {
        call_user_func($afterSearchKeywordCallback, $this, $form, $model);
    }
    ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CreateEvent */
/* @var $form ActiveForm */

?>



<?php $form = ActiveForm::begin(['action' => $url]); ?>
    <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'model_key')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'model_key')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'text')->textarea() ?>

    <div class="form-group">
        <?= Html::submitButton('Save the event', ['class' => 'btn btn-success']) ?>
    </div>
<?php ActiveForm::end(); ?>

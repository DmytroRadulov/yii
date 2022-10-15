<?php

use common\models\Company;
use common\models\User;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Company */

$this->title = Yii::t('app', 'Update Company: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Companies'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="company-update row">
    <div class="col-lg-6 col-md-6">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
    <div class="col-lg-4 col-md-4">
        <div class="row">
            <div class="col-md-12" style="font-size: 18px;">
                <?=
                DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => 'Ships',
                            'format' => 'raw',
                            'value' => function (Company $model) {
                                $text = '';
                                if ($model->ships) {
                                    foreach ($model->ships as $ship) {
                                        $text .= Html::a($ship->name, ['ship/index', 'ShipSearch[name]' => $ship->name], ['target' => '_blank', 'data-pjax' => 0]) . '<br/>';
                                    }
                                }
                                return $text;
                            },
                        ],
                    ]
                ]);
                ?>
            </div>
            <div class="col-md-12" style="font-size: 18px;">
                <?= ($model->created_at ? '<b>Created by </b>' . ($model->createdBy ? $model->createdBy->username : 'Cron') . ' ' . Yii::$app->formatter->asDatetime($model->created_at) . '<br>' : ''); ?>
                <?php if ($model->lastUpdatedUsers && (Yii::$app->user->identity->role === User::ROLE_SYS_ADMIN || Yii::$app->user->identity->role === User::ROLE_ADMIN)) : ?>
                    <b>Last updated: </b><br>
                    <?php foreach ($model->lastUpdatedUsers as $lastUpdatedUser) : ?>
                        <?= '<b>User: </b>' . ($lastUpdatedUser->user ? $lastUpdatedUser->user->username : "Cron") . ' <b>Date: </b>' . Html::a(Yii::$app->formatter->asDatetime($lastUpdatedUser->created_at), ['change-log/index', 'ChangeLogSearch[id]' => $lastUpdatedUser->id], ['target' => '_blank']); ?>
                        <br>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="col-md-12">
                <?= Html::button('Create Event', ['id' => 'test', 'class' => 'btn btn-primary'
                    , 'data-toggle' => 'modal', 'data-target' => '#myModal']); ?>
                <?php foreach ($event as $value) :?>
                    <hr>
                    <div><?= Yii::$app->formatter->asDatetime($value->created_at);?></div>
                    <div><?= $value->text; ?></div>
                <?php endforeach; ?>
                <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Create event</h4>
                            </div>
                            <div class="modal-body">
                                <?= $this->render('@backend/views/forms/create_event', ['model' => $createEventModel,
                                    'url' => \yii\helpers\Url::to(['company/save-event'])]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
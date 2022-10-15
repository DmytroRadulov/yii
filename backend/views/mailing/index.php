<?php

use common\models\Mailing;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\MailingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mailings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mailing-index">

    <h2><?= Html::encode($this->title) ?></h2>
    <div style="margin: 10px 0">
        <?= Yii::$app->user->identity->canRoute('mailing/create') ? Html::a('Create New Mailing', ['create'], ['class' => 'btn btn-success']) : ''; ?>
        <div style="float: right">
            <?= Yii::$app->user->identity->canRoute('mailing/update') ? Html::a('Pause all mailings', ['stop-all'], ['class' => 'btn btn-danger']) : ''; ?>
            <?= Yii::$app->user->identity->canRoute('mailing/update') ? Html::a('Resume all mailings', ['run-all'], ['class' => 'btn btn-warning']) : ''; ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
			'template_name',
            'template_label',
            [
                'attribute'  => 'subject',
				'contentOptions' => ['style' => ['white-space' => 'normal']],
				'headerOptions' => ['style' => ['min-width' => '175px']],
            ],
            [
                'attribute'  => 'emails',
				'headerOptions' => ['style' => ['min-width' => '175px']],
				'format'     => 'html',
                'filter'     => false,
                'value'      => function($model) {
                    return $model->emails ? str_replace([';', ' '], ['<br>', ''], $model->emails) : '';
                }
            ],
            [
                'attribute'  => 'ships',
				'headerOptions' => ['style' => ['min-width' => '175px']],
				'contentOptions' => ['style' => ['white-space' => 'normal']],
				'filter'     => false,
                'value'      => function($model) {
                    return $model->ships ? implode(', ', $model->ships) : '';
                }
            ],
            [
                'attribute'  => 'companies',
				'headerOptions' => ['style' => ['min-width' => '175px']],
                'contentOptions' => ['style' => ['white-space' => 'normal']],
				'filter'     => false,
                'value'      => function($model) {
                    return $model->companies ? implode(', ', $model->companies) : '';
                }
            ],
			[
				'attribute'  => 'company_type',
				'headerOptions' => ['style' => ['min-width' => '175px']],
				'contentOptions' => ['style' => ['white-space' => 'normal']],
				'filter'     => false,
				'value'      => function($model) {
					return $model->getCompanyType();
				}
			],
            [
                'attribute'  => 'status',
                'filter'     => false,
                'format'     => 'raw',
                'value'      => function($model) {
                    return '<span class="'. ($model->status == Mailing::STATUS_RUN ? 'text-success' : 'text-danger' ) .'">' . ($model->status == Mailing::STATUS_RUN ? 'Run' : 'Pause') . '</span>';
                }
            ],
            [
                'attribute'  => 'send_period',
                'label' => 'Period',
                'filter' => $searchModel->getSendPeriodList(),
				'headerOptions' => ['style' => ['max-width' => '1750px', 'min-width' => '140px']],
                'contentOptions' => ['style' => ['white-space' => 'normal']],
                'value'      => function($model) {
                    return $model->getSendPeriod();
                }
            ],
			[
				'headerOptions'	 => ['style' => ['width' => '160px']],
				'attribute'		 => 'first_send',
				'format'		 => 'date',
				'filterType'			 => GridView::FILTER_DATE,
				'filterWidgetOptions'	 => [
					'type'			 => DatePicker::TYPE_COMPONENT_APPEND,
					'pickerButton'	 => false,
					'convertFormat'  => true,
					'pluginOptions'	 => [
						'autoclose'		 => true,
						'todayHighlight' => true,
						'todayBtn'		 => 'linked',
					]
				],
			],
			[
				'headerOptions'	 => ['style' => ['width' => '160px']],
				'attribute'		 => 'next_send',
				'format'		 => 'date',
				'filterType'			 => GridView::FILTER_DATE,
				'filterWidgetOptions'	 => [
					'type'			 => DatePicker::TYPE_COMPONENT_APPEND,
					'pickerButton'	 => false,
					'convertFormat'  => true,
					'pluginOptions'	 => [
						'autoclose'		 => true,
						'todayHighlight' => true,
						'todayBtn'		 => 'linked',
					]
				],
			],
            [
				'headerOptions'	 => ['style' => ['width' => '160px']],
				'attribute'		 => 'last_send',
				'format'		 => 'date',
				'filterType'			 => GridView::FILTER_DATE,
				'filterWidgetOptions'	 => [
					'type'			 => DatePicker::TYPE_COMPONENT_APPEND,
					'pickerButton'	 => false,
					'convertFormat'  => true,
					'pluginOptions'	 => [
						'autoclose'		 => true,
						'todayHighlight' => true,
						'todayBtn'		 => 'linked',
					]
				],
			],
            [
                'class' => 'kartik\grid\ActionColumn',
				'template' => '{view} '
                    . (Yii::$app->user->identity->canRoute('mailing/update') ? ' {update} ' : '')
					. (Yii::$app->user->identity->canRoute('mailing/create') ? ' {copy} ' : '')
                    . (Yii::$app->user->identity->canRoute('mailing/delete') ? ' {delete} ' : ''),
				'buttons'		 => [
					'view' => function ($url, $model, $key) {
						return Html::a('<span class="glyphicon glyphicon-eye-open" title="Preview"></span>', ['view', 'id' => $model->id],
							['class' => 'JS__load_in_modal']);
					},
                    'copy' =>  function ($url, $model, $key) {
						return Html::a('<span class="glyphicon glyphicon-duplicate" title="Create mailing list by copying"></span>', ['copy', 'id' => $model->id]);
					}
				],
            ],
        ],
    ]); ?>
</div>

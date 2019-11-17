<?

use app\components\Str;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this \yii\web\View */
/* @var $model \app\models\Items */

$this->params['breadcrumbs'][] = $this->context->title;
$this->params['menuSide']['create'] = true;

echo GridView::widget([
	'dataProvider' => $model->search(),
	'filterModel' => $model,
	'pjax' => true,
	'toolbar' => [
		[
			'content' => Html::a('<i class="glyphicon glyphicon-repeat"></i>', '#', [
								'class' => 'btn btn-default helping',
								'title' => 'Обновить',
							]), 
		],
	],
	'panel' => [
		'type' => GridView::TYPE_DEFAULT,
	],
	'rowOptions' => function ($data) {
		if ($data->now != $data->new) {
			return ['class' => 'info'];
		}
	},
	'columns' => [
		[
			'attribute' => 'id',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->id;
			},
			'width' => '36px',
			'filter' => false,
		],
		[
			'attribute' => 'title',
			'format' => 'raw',
			'value' => function ($data) {
				$link = $data->link;
				if ($data->id_template == 4) {
					$link = 'https://www.youtube.com/channel/' . $data->link . '/videos';
				}
				return Html::a($data->title, $link, ['target' => '_blank']);
			},
			'filterInputOptions' => [
				'autocomplete' => 'off',
				'class' => 'form-control'
			],
		],
		[
			'attribute' => 'id_template',
			'format' => 'raw',
			'value' => function ($data) {
				return $data::templateList()[$data->id_template]['name'];
			},
			'width' => '200px',
			'filterType' => GridView::FILTER_SELECT2,
			'filter' => ArrayHelper::map($model::templateList(), 'id', 'name'), 
			'filterWidgetOptions' => [
				'pluginOptions' => ['placeholder' => '',  'allowClear' => true],
			],
			'filterInputOptions' => ['multiple' => true],
		],
		[
			'attribute' => 'now',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->now;
			},
			'filter' => false,
		],
		[
			'attribute' => 'new',
			'format' => 'raw',
			'value' => function ($data) {
				$out = '';
				if ($data->new) {
					$fullLink = 'https://' . $data::templateList()[$data->id_template]['name'] . $data->link_new;
					if ($data->id_template == 2) {
						$fullLink = $data->link_new;
					}
					if ($data->id_template == 3) {
						$fullLink .= '#page=1';
					}
					$out = Html::a($data->new, $fullLink, ['target' => '_blank']);
				}
				return $out;
			},
			'filter' => false,
		],
		[
			'attribute' => 'dt_update',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->dt_update ? Str::dateEngToRu(date('d F H:i', $data->dt_update)) : '';
			},
			'filter' => false,
			'width' => '125px',
		],
		[
			'class' => yii\grid\ActionColumn::className(),
			'buttons' => [
				'check' => function ($url, $model) {
					$button = '';
					if ($model->now != $model->new) {
						$button = Html::a('<span class="glyphicon glyphicon-film text-success"></span> <span class="glyphicon glyphicon-ok text-success"></span>', '#', [
							'class' => 'check',
							'title' => 'Check',
							'data-id' => $model->id,
						]) . '<br>';
					}
					return $button;
				},
			],
			'template' => '{check} {update} {delete}',
			'options' => [
				'width' => '55px',
			]
		]
	]
]);
<?

use app\components\Str;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

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
			'attribute' => 'id_telegram',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->id_telegram;
			},
		],
		[
			'attribute' => 'title',
			'format' => 'raw',
			'value' => function ($data) {
				$link = $data->link;
				if ($data->id_template == 5) {
					$link = 'https://www.vk.com/club' . mb_substr($data->link, 1);
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
				$text = StringHelper::truncate(nl2br($data->now), 100, '...', null, true);
				$tooltip = Html::tag('span', $text, [
					'title' => $data->now,
					'data-toggle' => 'tooltip',
				]);
				return $tooltip;
			},
			'filter' => false,
		],
		[
			'attribute' => 'new',
			'format' => 'raw',
			'value' => function ($data) {
				$out = '';
				if ($data->new) {
					$text = StringHelper::truncate(nl2br($data->new), 100, '...', null, true);
					$tooltip = Html::tag('span', $text, [
						'title' => $data->new,
						'data-toggle' => 'tooltip',
					]);
					$out = Html::a($tooltip, $data::getFullLink($data->link_new, $data->id_template), ['target' => '_blank']);
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
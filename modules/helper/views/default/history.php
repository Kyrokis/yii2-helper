<?

use app\components\Str;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use app\models\Items;
use app\modules\template\models\Template;

/* @var $this \yii\web\View */
/* @var $model \app\models\ItemsHistory */

$this->params['breadcrumbs'][] = ['label' => $this->context->title, 'url' => ['default/index']];
$this->params['breadcrumbs'][] =  'History';
$this->params['menuSide']['create'] = true;

echo GridView::widget([
	'dataProvider' => $model->search(),
	'filterModel' => $model,
	'pjax' => true,
	'hover' => true,
	'striped' => false,
	'toolbar' => [
		[
			'content' => Html::a('<i class="glyphicon glyphicon-remove"></i>', [''], [
								'class' => 'btn btn-default',
								'title'=> 'Сбросить фильтр',
							]), 
		],
	],
	'panel' => [
		'type' => GridView::TYPE_DEFAULT,
	],
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
			'attribute' => 'item_id',
			'label' => 'Название',
			'width' => '400px',
			'format' => 'raw',
			'value' => function ($data) use ($model) {
				return  Html::a($data->item->title, ['', 'ItemsHistory' => ['item_id' => $data->item_id, 'dt_start' => $model->dt_start, 'dt_end' => $model->dt_end]]);
			},
			'filterType' => GridView::FILTER_SELECT2,
			'filter' => Items::all($model->item_id), 
			'filterWidgetOptions' => [
				'pluginOptions' => ['placeholder' => '',  'allowClear' => true],
			],
			'filterInputOptions' => ['multiple' => true],
		],
		[
			'attribute' => 'now',
			'format' => 'raw',
			'value' => function ($data) {
				$out = '';
				if ($data->now) {
					$text = nl2br(StringHelper::truncate($data->now, 100, '...', null, true));
					$tooltip = Html::tag('span', $text, [
						'title' => $data->now,
						'data-toggle' => 'tooltip',
					]);
					if ($data->link) {
						$out = Html::a($tooltip, $data->link, ['target' => '_blank']);
					} else {
						$out = $tooltip;
					}
				}
				return $out;
			},
			'filter' => false,
		],
		[
			'attribute' => 'dt',
			'format' => 'raw',
			'value' => function ($data) {
				return Str::dateEngToRu(date('d F H:i', $data->dt));
			},
			'filterType' => GridView::FILTER_DATE_RANGE,
			'filterWidgetOptions' => [
				'hideInput' => true,
				'convertFormat' => true,
				'presetDropdown' => false,
				'startAttribute' => 'dt_start',
				'endAttribute' => 'dt_end',
				'pluginOptions' => [
					 'locale' => [
						'format' => 'd.m.Y',
					],
					'opens' => 'left',
					'ranges' => [
						'Сегодня' => ["moment().startOf('day')", "moment()"],
						'Вчера' => ["moment().startOf('day').subtract(1,'days')", "moment().endOf('day').subtract(1,'days')"],
						'Последние 7 дней' => ["moment().endOf('day').subtract(7,'days')", "moment().startOf('day')"],
						'Эта неделя' => ["moment().startOf('week')", "moment().endOf('week')"],
						'Этот месяц' => ["moment().startOf('month')", "moment().endOf('month')"],
					]
				],
			],
			'width' => '220px',
		]
	]
]);
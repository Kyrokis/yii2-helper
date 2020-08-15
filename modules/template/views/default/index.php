<?

use app\components\Str;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use app\models\User;
use app\modules\template\models\Template;

/* @var $this \yii\web\View */
/* @var $model \app\modules\template\models\Template */

$this->params['breadcrumbs'][] = $this->context->title;
$this->params['menuSide']['create'] = true;

echo GridView::widget([
	'dataProvider' => $model->search(),
	'filterModel' => $model,
	'pjax' => true,
	'hover' => true,
	'striped' => false,
	'toolbar' => [
		[
			'content' => Html::a('<i class="glyphicon glyphicon-remove"></i>', ['index'], [
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
			'attribute' => 'user_id',
			'label' => 'Пользователь',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->user_id ? $data->user->login : 'Общий';
			},
			'width' => '150px',
			'filterType' => GridView::FILTER_SELECT2,
			'filter' => User::all(), 
			'filterWidgetOptions' => [
				'pluginOptions' => ['placeholder' => '',  'allowClear' => true],
			],
			'filterInputOptions' => ['multiple' => true, /*'disabled' => !\Yii::$app->user->identity->admin*/],
		],
		[
			'attribute' => 'type',
			'format' => 'raw',
			'value' => function ($data) {
				return Template::typeList()[$data->type];
			},
			'width' => '150px',
			'filterType' => GridView::FILTER_SELECT2,
			'filter' => Template::typeList(), 
			'filterWidgetOptions' => [
				'pluginOptions' => ['placeholder' => '',  'allowClear' => true],
			],
			'filterInputOptions' => ['multiple' => true],
		],
		[
			'attribute' => 'name',
			'format' => 'raw',
			'value' => function ($data) {
				return $data->name;
			},
			'filterInputOptions' => [
				'autocomplete' => 'off',
				'class' => 'form-control'
			],
		],
		[
			'attribute' => 'title',
			'format' => 'raw',
			'value' => function ($data) {
				return json_encode($data->title->getValue());
			},
			'filter' => false,
		],
		[
			'attribute' => 'new',
			'format' => 'raw',
			'value' => function ($data) {
				return json_encode($data->new->getValue());
			},
			'filter' => false,
		],
		[
			'attribute' => 'link_new',
			'format' => 'raw',
			'value' => function ($data) {
				return json_encode($data->link_new->getValue());
			},
			'filter' => false,
		],
		[
			'attribute' => 'link_img',
			'format' => 'raw',
			'value' => function ($data) {
				return json_encode($data->link_img->getValue());
			},
			'filter' => false,
		],		
		[
			'class' => yii\grid\ActionColumn::className(),
			'buttons' => [
				'copy' => function ($url, $model) {
					$button = '';
					if (\Yii::$app->user->identity->copying == '1') {
						$button = Html::a('<span class="glyphicon glyphicon-film"></span> <span class="glyphicon glyphicon-plus"></span>', '#', [
							'class' => 'copy',
							'title' => 'Copy',
							'data-id' => $model->id,
						]) . '<br>';
					}
					return $button;
				},
			],
			'template' => '{copy} {update} {delete}',
			'options' => [
				'width' => '55px',
			],
			
		]
	]
]);
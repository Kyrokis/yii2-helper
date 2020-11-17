<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;
use app\modules\template\models\Template;

$user = Yii::$app->user;
$readonly = $this->context->action->id == 'view';
$form = ActiveForm::begin(['options' => ['autocomplete' => 'off'], 'disabled' => $readonly]);
$attributes = [
	'link' => [],
	'id_template' => [
		'type' => Form::INPUT_DROPDOWN_LIST,
		'items' => Template::all(),
	],
	'offset' => [],
	'title' => [],
	'link_img' => [],
	'link_new' => [],
	'now' => [],
	'new' => [],
];
if ($user->identity->admin) {
	$attributes['user_id'] = ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => User::all()];
	$attributes['error'] = ['type' => Form::INPUT_CHECKBOX];
}
echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 2,
	'attributes' => $attributes,
]);
if (!$readonly) {
	echo Html::button('Загрузить', ['type' => 'button', 'class' => 'btn btn-info get-data']);
	echo Html::button('Сохранить', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
} else {
	echo Html::button('Копировать', ['type' => 'submitButton', 'class' => 'btn btn-primary']);
}
ActiveForm::end();
?>
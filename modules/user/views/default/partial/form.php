<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

$form = ActiveForm::begin();
$attributes = [
		'login' => [],
		'id_telegram' => [],
		'password' => [],
	];
if (Yii::$app->user->identity->admin) {
	$attributes['admin'] = ['type' => Form::INPUT_CHECKBOX];
}
echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 2,
	'attributes' => $attributes,
]);

echo Html::button('Сохранить', ['type'=>'submitButton', 'class'=>'btn btn-primary']);
ActiveForm::end();
?>
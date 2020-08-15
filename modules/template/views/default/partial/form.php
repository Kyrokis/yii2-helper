<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User;
use app\modules\template\models\Template;

$form = ActiveForm::begin(['options' => ['autocomplete' => 'off']]);
$attributes = [
	'type' => [
		'type' => Form::INPUT_DROPDOWN_LIST,
		'items' => Template::typeList(),
	],
	'name' => [],
	'title1' => [],
	'title2' => [],
	'new1' => [],
	'new2' => [],
	'link_new1' => [],
	'link_new2' => [],
	'link_img1' => [],
	'link_img2' => [],
	'full_link1' => [],
	'full_link2' => [],
];
if (Yii::$app->user->identity->admin) {
	$attributes['user_id'] = ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => User::all(), 'options' => ['prompt' => '---']];
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
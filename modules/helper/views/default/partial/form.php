<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\Items;
use app\models\User;

$form = ActiveForm::begin();
$attributes = [
	'link' => [],
	'id_template' => [
		'type' => Form::INPUT_DROPDOWN_LIST,
		'items' => ArrayHelper::map(Items::templateList(), 'id', 'name'),
	],
	'offset' => [],
	'title' => [],
	'link_img' => [],
	'link_new' => [],
	'now' => [],
	'new' => [],
];
if (Yii::$app->user->identity->admin) {
	$attributes['user_id'] = ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => User::all()];
}
echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 2,
	'attributes' => $attributes, 
]);

echo Html::button('Загрузить', ['type'=>'button', 'class'=>'btn btn-info get-data']);
echo Html::button('Сохранить', ['type'=>'submitButton', 'class'=>'btn btn-primary']);
ActiveForm::end();
?>
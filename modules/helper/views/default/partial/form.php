<?

use kartik\form\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\models\Items;

$form = ActiveForm::begin();
echo Form::widget([
	'model' => $model,
	'form' => $form,
	'columns' => 2,
	'attributes' => [
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
	], 
]);

echo Html::button('Загрузить', ['type'=>'button', 'class'=>'btn btn-info get-data']);
echo Html::button('Сохранить', ['type'=>'submitButton', 'class'=>'btn btn-primary']);
ActiveForm::end();
?>
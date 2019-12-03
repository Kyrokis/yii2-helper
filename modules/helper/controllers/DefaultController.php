<?
namespace app\modules\helper\controllers;

use Yii;
use yii\web\Controller;
use yii\httpclient\Client;
use app\models\Items;
use app\components\thread\Thread;
use QL\QueryList;
use VK\Client\VKApiClient;
use app\components\Str;


/**
 * Controller for Helper app
 */
class DefaultController extends Controller {

	public $title = 'Helper';

	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action) {		
		if ($action->id == 'helping') {
			$this->enableCsrfValidation = FALSE;
			$this->layout = FALSE;
		}

		return parent::beforeAction($action);
	}

	/**
	 * Index
	 * @return json
	 */
	public function actionIndex() {
		$model = new Items();
		$model->setScenario(Items::SCENARIO_SEARCH);
		$model->load(\Yii::$app->request->get());
		return $this->render('index', ['model' => $model]);
	}

	/**
	 * Get Data
	 * @return json
	 */
	public function actionGetData($link, $id_template, $offset = 0) {
		if ($id_template == 4) {
			$template = Items::templateList($offset)[$id_template];
			$client = new Client();
			$response = $client->get($link)->send();
			$content = $response->content;
			$items = [
				'title' => Str::explode($template['title'], $content),
				'now' => Str::explode($template['now'], $content),
				'link_img' => Str::explode($template['link_img'], $content),
				'link_new' => Str::explode($template['link_new'], $content)
			];	
		} else if ($id_template == 5) {
			$vk = new VKApiClient();
			$post = $vk->wall()->get(\Yii::$app->params['vkApiKey'], [
							'owner_id' => $link,
							'offset' => $offset,
							'count' => 1,
							'filter' => 'owner',
							'extended' => 1
						]);
			$items = [
				'title' => $post['groups'][0]['name'],
				'link_img' => $post['groups'][0]['photo_200'],
				'link_new' => '/wall' . $link . '_' . $post['items'][0]['id'],
			];	
			if ($post['items'][0]['copy_history']) {
				$items['now'] = $post['items'][0]['copy_history'][0]['text'];
			} else {
				$items['now'] = $post['items'][0]['text'];
			}
		} else {
			$template = Items::templateList($offset)[$id_template];
			$items = QueryList::get($link)->rules([ 
							'title' => $template['title'],
							'now' => $template['now'],
							'link_img' => $template['link_img'],
							'link_new' => $template['link_new']
						])
						->query()->getData()->all()[0];
		}
		return json_encode($items);
	}


	/**
	 * Check selected item
	 * @return bool
	 */
	public function actionCheck($id) {
		$model = $this->findModel($id);
		$model->now = $model->new;
		return $model->save();
	}

	/**
	 * Helping items
	 * @return json
	 */	
	public function actionHelping($id = null, $id_telegram = null) {
		$items = Items::find()->andFilterWhere(['id' => $id, 'id_telegram' => $id_telegram])->all();
		$Thread = new Thread();
		foreach ($items as $value) {
			$Thread->Create(function() use($value) {
				if ($value->id_template == 4) {
					$template = \app\models\Items::templateList($value->offset)[$value->id_template];
					$client = new \yii\httpclient\Client();
					$response = $client->get($value->link)->send();
					$content = $response->content;
					$new = [
						'now' => \app\components\Str::explode($template['now'], $content),
						'link_new' => \app\components\Str::explode($template['link_new'], $content)
					];	
				} else if ($value->id_template == 5) {
					$vk = new \VK\Client\VKApiClient();
					$post = $vk->wall()->get(\Yii::$app->params['vkApiKey'], [
									'owner_id' => $value->link,
									'offset' => $value->offset,
									'count' => 1,
									'filter' => 'owner',
									'extended' => 1
								]);
					$new['link_new'] = '/wall' . $value->link . '_' . $post['items'][0]['id'];
					if ($post['items'][0]['copy_history']) {
						$new['now'] = $post['items'][0]['copy_history'][0]['text'];
					} else {
						$new['now'] = $post['items'][0]['text'];
					}
				} else {
					$template = \app\models\Items::templateList($value->offset)[$value->id_template];
					$new = \QL\QueryList::get($value->link)->rules([
								'now' => $template['now'], 
								'link_new' => $template['link_new']
							])
							->query()->getData()->all()[0];
				}
				if (($new['now'] != $value->now && $new['now'] != $value->new) || ($new['now'] == $value->now && $new['now'] != $value->new)) {
					if (($model = \app\models\Items::findOne($value->id)) !== null) {
						$model->new = $new['now'];
						$model->link_new = $new['link_new'];
						$model->dt_update = time();
						$model->save();
						return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $new['now'], 'link_new' => $new['link_new']];
					}
				} else if ($new['now'] != $value->now && $new['now'] == $value->new) {
					return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $value->new, 'link_new' => $value->link_new];
				}
			});
		}
		return json_encode($Thread->Run());
	}	

	/**
	 * Creates a new Items model.
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new Items();

		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else if (!$model->offset) {
			$model->offset = 0;
		}

		return $this->render('create', ['model' => $model]);
	}

	/**
	 * Updates an existing Items model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate($id) {
		$model = $this->findModel($id);

		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else if (!$model->offset) {
			$model->offset = 0;
		}

		return $this->render('update', ['model' => $model]);
	}

	/**
	 * Deletes an existing Items model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id) {
		$this->findModel($id)->delete();

		return $this->redirect(['index']);
	}

	/**
	 * Finds the Items model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Items the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id) {
		if (($model = Items::findOne($id)) !== null) {
			return $model;
		}
		
		throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
	}
}
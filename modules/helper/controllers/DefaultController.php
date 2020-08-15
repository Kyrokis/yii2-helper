<?
namespace app\modules\helper\controllers;

use Yii;
use yii\web\Controller;
use yii\httpclient\Client;
use app\models\Items;
use app\models\User;
use app\modules\template\models\Template;
use app\components\thread\Thread;
use QL\QueryList;
use VK\Client\VKApiClient;
use app\components\Str;


/**
 * Controller for Helper module
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
		$user = Yii::$app->user;
		if ($action->id == 'helping') {
			$this->enableCsrfValidation = FALSE;
			$this->layout = FALSE;
		} else if ($user->isGuest) {
			$this->redirect($user->loginUrl);
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
		$model->user_id = Yii::$app->user->id;
		$model->load(\Yii::$app->request->get());
		return $this->render('index', ['model' => $model]);
	}

	/**
	 * Get Data
	 * @return json
	 */
	public function actionGetData($link, $id_template, $offset = 0) {
		$template = Template::findOne($id_template);
		if ($template->type == 1) {
			$client = new Client();
			$response = $client->get($link . '?disable_polymer=1')->send();
			$content = $response->content;
			$items = [
				'title' => Str::explode($template->title, $content),
				'now' => Str::explode($template->new, $content),
				'link_new' => Str::explode($template->link_new, $content),
				'link_img' => Str::explode($template->link_img, $content),
			];	
		} else if ($template->type == 2) {
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
			$items = QueryList::get($link)->rules([ 
							'title' => $template->title,
							'now' => $template->new,
							'link_new' => $template->link_new,
							'link_img' => $template->link_img,
						])
						->query()->getData()->all();
		}
		$items['now'] = ($items['now'] != '') ? $items['now'] : $items['link_new'];
		return json_encode($items);
	}


	/**
	 * Check selected item
	 * @return bool
	 */
	public function actionCheck($id) {
		$model = $this->findModel($id);
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
		}
		$model->now = $model->new;
		return $model->save();
	}

	/**
	 * Helping items
	 * @return json
	 */	
	public function actionHelping($id = null, $user_id = null) {
		$items = Items::find()->andFilterWhere(['id' => $id, 'user_id' => $user_id, 'del' => '0'])->all();
		if ($user_id) {
			$user = User::findOne($user_id);
			$user->dt_helping = time();
			$user->save(FALSE, ['dt_helping']);	
		}
		$Thread = new Thread();
		foreach ($items as $value) {
			$Thread->Create(function() use($value) {
				$template = app\modules\template\models\Template::findOne($value->id_template);
				if ($template->type == 1) {
					$client = new \yii\httpclient\Client();
					$response = $client->get($value->link . '?disable_polymer=1', [], ['timeout' => 10])->send();
					$content = $response->content;
					$new = [
						'now' => \app\components\Str::explode($template->new, $content),
						'link_new' => \app\components\Str::explode($template->link_new, $content)
					];
				} else if ($template->type == 2) {
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
					$new = \QL\QueryList::get($value->link, [], ['timeout' => 10])->rules([
								'now' => $template->new, 
								'link_new' => $template->link_new
							])
							->query()->getData()->all();
				}
				if ($new['link_new'] == '' && $new['now'] == '') {
					if (($model = \app\models\Items::findOne($value->id)) !== null) {
						$model->error = '1';
						//$model->dt_update = time();
						$model->save(FALSE, ['error', 'dt_update']);
						return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $value->now, 'link_new' => $value->link_new, 'error' => '1'];
					}
				}
				$new['now'] = ($new['now'] != '') ? $new['now'] : $new['link_new'];
				if (($new['now'] != $value->now && $new['now'] != $value->new) || ($new['now'] == $value->now && $new['now'] != $value->new) || ($value->error == '1')) {
					if (($model = \app\models\Items::findOne($value->id)) !== null) {
						if ($new['now'] != $value->new) {
							$model->new = $new['now'];
							$model->link_new = $new['link_new'];
							$model->dt_update = time();
						}
						$model->error = '0';
						$model->save();
						return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $new['now'], 'link_new' => $new['link_new'], 'error' => $model->error];
					}
				} else if ($new['now'] != $value->now && $new['now'] == $value->new) {
					return ['id' => $value->id, 'id_template' => $value->id_template, 'title' => $value->title, 'new' => $value->new, 'link_new' => $value->link_new, 'error' => $value->error];
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
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
		}
		if ($model->load(\Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['index']);
		} else if (!$model->offset) {
			$model->offset = 0;
		}

		return $this->render('update', ['model' => $model]);
	}

	/**
	 * Copies an existing Items model to user.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionCopy($id) {
		$model = $this->findModel($id);
		$user = Yii::$app->user;
		$newModel = new Items();
		$newModel->load($model->attributes, '');
		unset($newModel->id);
		$newModel->user_id = $user->id;
		return $newModel->save();
	}

	/**
	 * Deletes an existing Items model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete($id) {
		$model = $this->findModel($id);
		$user = Yii::$app->user;
		if (!$user->identity->admin && $user->id != $model->user_id) {
			throw new \yii\web\ForbiddenHttpException('У Вас нет прав на это действие');
		}
		$model->delete();

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

	public function actionTest() {
	}
}
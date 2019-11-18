<?
namespace app\modules\helper\controllers;

use Yii;
use yii\web\Controller;
use app\models\Items;
use app\components\thread\Thread;
use QL\QueryList;
use Google_Client;
use Google_Service_YouTube;


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
			$client = new Google_Client(['developer_key' => Yii::$app->params['googleApiKey']]);
			$service = new Google_Service_YouTube($client);
			$video = $service->search->listSearch('snippet', [
													'channelId' => $link,
													'maxResults' => 1,
													'fields' => 'items(id(videoId), snippet(channelTitle, title))',
													'order' => 'date',
												])->getItems()[0];
			$items = [
				'title' => $video->snippet->channelTitle,
				'now' => $video->snippet->title,
				'link_new' => '/watch?v=' . $video->id->videoId,
			];
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
	public function actionHelping($id = null) {
		$items = Items::find()->andFilterWhere(['id' => $id])->all();
		$Thread = new Thread();
		foreach ($items as $value) {
			$Thread->Create(function() use($value) {
				if ($value->id_template == 4) {
					$client = new Google_Client(['developer_key' => \Yii::$app->params['googleApiKey']]);
					$service = new Google_Service_YouTube($client);
					$video = $service->search->listSearch('snippet', [
															'channelId' => $value->link,
															'maxResults' => 1,
															'fields' => 'items(id(videoId), snippet(title))',
															'order' => 'date',
														])->getItems()[0];
					$new = [
						'now' => $video->snippet->title,
						'link_new' => '/watch?v=' . $video->id->videoId,
					];
				} else {
					$template = frontend\models\Items::templateList($value->offset)[$value->id_template];
					$new = QL\QueryList::get($value->link)->rules([
																	'now' => $template['now'], 
																	'link_new' => $template['link_new']
																])
																->query()->getData()->all()[0];
				}
				if (($new['now'] != $value->now && $new['now'] != $value->new) || ($new['now'] == $value->now && $new['now'] != $value->new)) {
					if (($model = frontend\models\Items::findOne($value->id)) !== null) {
						$model->new = $new['now'];
						$model->link_new = $new['link_new'];
						$model->dt_update = time();
						$model->save();
						return ['id' => $value->id, 'new' => $new['now'], 'link_new' => $new['link_new']];
					}
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

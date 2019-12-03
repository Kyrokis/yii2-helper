<?
namespace app\modules\telegram\controllers;

use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use aki\telegram\Telegram;
use app\models\Items;
use app\models\TelegramForm;
use yii\httpclient\Client;
use QL\QueryList;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Exception;

/**
 * Controller for telegram app
 */
class DefaultController extends Controller {

	public $title = 'Telegram helper';

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action) {		
		if ($action->id == 'webhook-page') {
			$this->enableCsrfValidation = FALSE;
			$this->layout = FALSE;
		}

		return parent::beforeAction($action);
	}

	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

	/**
	 * Index
	 * @return json
	 */
	public function actionIndex() {
		$model = new TelegramForm;
		return $this->render('index', ['model' => $model]);
	}

	/**
	 * Send message
	 * @return json
	 */
	public function actionSendMessage($chat_id = 197239226, $text = 'test') {
		$result = Yii::$app->telegram->sendMessage([
					'chat_id' => $chat_id,
					'text' => $text,
				]); 
		return json_encode($result);
	}

	/**
	 * Get updates in selected chat
	 * @return json
	 */
	public function actionGetUpdates($chat_id = 197239226) {
		Yii::$app->telegram->deleteWebhook(); 
		$updates = Yii::$app->telegram->getUpdates([
			'chat_id' => $chat_id,
		]); 

		$result = Yii::$app->telegram->sendMessage([
			'chat_id' => $chat_id,
			'text' => json_encode($updates),
		]); 
		Yii::$app->telegram->setWebhook(['url' => '']); // insert here your webhook url 
		return json_encode($result);
	}


	/**
	 * Activate webhook on url
	 * @return json
	 */
	public function actionSetWebhook($url) {
		$result = Yii::$app->telegram->setWebhook(['url' => $url]); 
		return json_encode($result);
	}

	/**
	 * Webhook page
	 * @return json
	 */
	public function actionWebhookPage() {
		$json = file_get_contents('php://input');
		$response = json_decode($json);

		if (!isset($response->message->text)) {
			return false;
		}
		$message = $response->message->text;
		$idTelegram = $response->message->from->id;
		$anilibria = 'https://www.anilibria.tv';
		$googleDrive = 'https://drive.google.com';
		$romantica = 'https://sovetromantica.com';
		$nyaasi = 'https://nyaa.si';
		$urls = [];
		if ($message == '/update') {
			$items = json_decode(Yii::$app->runAction('helper/default/helping', ['id_telegram' => $idTelegram]), true);
			$out = '';
			foreach ($items as $item) {
				if ($item) {
					$linkText = Html::a($item['new'], Items::getFullLink($item['link_new'], $item['id_template']));
					$out .= "<b>$item[title]</b>: $linkText\n";
				}
			}
			Yii::debug($out);
			if ($out) {
				$result = Yii::$app->telegram->sendMessage([
					'chat_id' => $idTelegram,
					'text' => $out,
					'parse_mode' => 'HTML',
					'disable_web_page_preview' => true
				]);
			} else {
				$result = Yii::$app->telegram->sendMessage([
					'chat_id' => $idTelegram,
					'text' => 'Ничего нового',
				]);
			}
			Yii::debug($result);
			return json_encode($result);
		}
		if (isset($response->message->entities)) {
			foreach ($response->message->entities as $entity) {
				if ($entity->type == 'url') {
					$urls[] = mb_substr($message, $entity->offset, $entity->length);
				}
				if ($entity->type == 'text_link') {
					$urls[] = $entity->url;
				}
			}
		}
		foreach ($urls as $url) {
			if (mb_stripos($url, '.torrent') !== false || (mb_stripos($url, $nyaasi) !== false && basename($url) == 'torrent')) {
				Yii::$app->telegram->sendDocument([
					'chat_id' => $idTelegram,
					'document' => $this->loadFile($url),
					'caption' => 'It\'s a me, Torrent file',
				]);
			} else if (mb_stripos($url, $googleDrive) !== false || mb_stripos($url, $romantica) !== false) {
				if (mb_stripos($url, $romantica) !== false) {
					$url = QueryList::get($url)->find('.animeTorrentDownload')->attrs('href')->all()[0];
				}
				$idPos = strrpos($url, '?id=');
				if ($idPos !== false) {
					$folderId = mb_substr($url, $idPos + 4, strlen($url));
				} else {
					$folderId = basename(parse_url($url, PHP_URL_PATH));
				}
				$client = new Google_Client(['developer_key' => Yii::$app->params['googleApiKey']]);
				$service = new Google_Service_Drive($client);
				try {
					$folders = $service->files->listFiles([
						'fields' => 'files(id, name, mimeType)',
						'q' => "mimeType = 'application/vnd.google-apps.folder' and '$folderId' in parents and (name contains 'СУБТИТРЫ' or name contains 'sub' or name contains 'ТОРРЕНТ' or name contains 'torrent')",
					]);
				}
				catch (Google_Service_Exception $e) {
					Yii::error($e->getMessage());
					continue;
				}
				foreach ($folders->getFiles() as $folder) {
					$subFolderId = $folder->getId();
					try {
						$files = $service->files->listFiles([
							'fields' => 'files(id, name, webContentLink)',
							'q' => "'$subFolderId' in parents",
							'orderBy' => 'name desc',
							'pageSize' => 1
						]);
					}
					catch (Google_Service_Exception $e) {
						Yii::error($e->getMessage());
						continue;
					}
					if ($files->getFiles()) {
						$file = $files->getFiles()[0];
						$newName = $file->getName();
						$document = $this->loadFile($file->getWebContentLink(), $newName);
						Yii::debug('Попытка отправить файл: ' . $newName);
						$result = Yii::$app->telegram->sendDocument([
							'chat_id' => $idTelegram,
							'document' => $document,
							'caption' => $newName
						]);
						Yii::debug(json_encode($result));
						if (!$result) {
							Yii::debug('Что-то пошло не так и отправляю ссылку');
							$result = Yii::$app->telegram->sendMessage([
								'chat_id' => $idTelegram,
								'text' => $file->getWebContentLink(),
							]); 
						}
					}
				}
			} else if (mb_stripos($url, $anilibria) !== false) {
				$items = QueryList::get($url)->rules([ 
													'title' => ['.torrentcol1', 'text'],
													'link' => ['.torrentcol4 > a', 'href']
												])
												->query()->getData()->all();
				foreach ($items as $item) {
					Yii::$app->telegram->sendDocument([
						'chat_id' =>$idTelegram,
						'document' => $this->loadFile($anilibria . $item['link']),
						'caption' => $item['title']
					]);
				}
			}
		}
	}

	private function loadFile($url, $filename = null) {
		if (!$filename) {
			$filename = basename($url);
		}
		$file = Yii::$app->basePath . '/uploads/' . $filename;
		if (mb_stripos($file, '.torrent') === false && mb_stripos($file, '.ass') === false) {
			$file .= '.torrent';
		}
		$fp = fopen($file, 'w+');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		if (file_exists($file)) {
			return $file;
		}
		return false;
	}
}

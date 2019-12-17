<?

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\User;

/**
 * This is the model class for table "items".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $link
 * @property string $link_img
 * @property string $link_new
 * @property string $now
 * @property string $new
 * @property integer $id_template
 * @property integer $offset
 * @property integer $dt_update
 * @property string $del
 */
class Items extends ActiveRecord {

	const SCENARIO_SEARCH = 'search';

	/**
	 * @return string
	 */
	public static function tableName() {
		return 'items';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['id', 'user_id', 'id_template', 'offset', 'dt_update'], 'integer'],
			[['title', 'link', 'link_img', 'link_new', 'now', 'new', 'del'], 'string'],
			[['title', 'link', 'id_template'], 'required'],
			[['title', 'link'], 'safe', 'on' => self::SCENARIO_SEARCH],
		];
	}

	/**
	 * User
	 * @return \app\models\User
	 */
	public function getUser() {
		return $this->hasOne(User::className(), ['id' => 'user_id']);
	}

	/**
	 * @return array
	 */
	public static function templateList($offset = 0) {
		return [
			[
				'id' => 0,
				'name' => 'anilibria.tv',
				'title' => ['.release-title', 'text'],
				'now' => ['.torrentcol1:eq(' . $offset . ')', 'text'],
				'link_img' => ['.detail_torrent_pic', 'src'],
				'link_new' => ['.torrentcol4:eq(' . $offset . ') > a', 'href']
			],
			[
				'id' => 1,
				'name' => 'mangarock.com',
				'title' => ['._13gHt', 'text'],
				'now' => ['._1A2Dc.rZ05K', 'text'],
				'link_img' => ['.EB2Aw._eoev', 'src'],
				'link_new' => ['._1A2Dc.rZ05K', 'href']
			],
			[
				'id' => 2,
				'name' => 'sovetromantica.com',
				'title' => ['.anime-name > div', 'text'],
				'now' => ['.episodes-slick_item:last > a > div > span', 'text'],
				'link_img' => ['#poster', 'data-src'],
				'link_new' => ['.animeTorrentDownload', 'href']
			],
			[
				'id' => 3,
				'name' => 'manga-chan.me',
				'title' => ['.title_top_a', 'text'],
				'now' => ['.manga2 > a', 'text'],
				'link_img' => ['#cover', 'src'],
				'link_new' => ['.manga2 > a', 'href']
			],
			[
				'id' => 4,
				'name' => 'youtube.com',
				'title' => ['<meta property="og:title" content="', '">'],
				'now' => [' rel="nofollow">', '</a>'],
				'link_img' => ['<link rel="image_src" href="', '">'],
				'link_new' => ['feature=c4-videos-u" href="', '" ']
			],
			[
				'id' => 5,
				'name' => 'vk.com'
			],
			[
				'id' => 6,
				'name' => 'animevost.org',
				'title' => ['.shortstoryContent h4', 'text'],
				'now' => ['.shortstoryHead h1', 'text'],
				'link_img' => ['.imgRadius', 'src'],
				'link_new' => ['#items > div:last', 'text']
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'user_id' => 'ID юзера',
			'title' => 'Название',
			'link' => 'Ссылка',
			'link_img' => 'Ссылка на постер',
			'link_new' => 'Ссылка на новинку',
			'now' => 'Сейчас',
			'new' => 'Новый',
			'id_template' => 'Шаблон',
			'offset' => 'Смещение',
			'dt_update' => 'Дата новинки',
			'del' => 'Удален',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert) {
		if ($insert) {
			if ($this->new == '') {
				$this->new = $this->now;
			}
			$this->user_id = Yii::$app->user->id;
		}
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function delete() {
		$this->del = '1';
		return $this->save(FALSE, ['del']);
	}

	/**
	 * Create DataProvider for GridView.
	 * @return \yii\data\ActiveDataProvider
	 */
	public function search() {
		//$user_id = Yii::$app->user->identity->admin ? $this->user_id : Yii::$app->user->id;
		$user_id =  $this->user_id;
		$query = self::find()->andFilterWhere([
					'id' => $this->id,
					'link' => $this->link,
					'user_id' => $user_id,
					'id_template' => $this->id_template,
					'del' => '0',
				])
				->andFilterWhere(['ILIKE', 'title', $this->title])
				->orderBy('user_id, (dt_update is null), dt_update desc, id');

		return new \yii\data\ActiveDataProvider(['query' => $query, 'sort' => false]);
	}

	/**
	 * Count $this->search() entries.
	 * @return int
	 */
	public function count() {
		$model = new self;
		return $model->search()->query->count();
	}

	/**
	 * Get full link
	 * @return string
	 */
	public static function getFullLink($link_new, $id_template) {
		$fullLink = 'https://' . self::templateList()[$id_template]['name'] . $link_new;
		if ($id_template == 2) {
			$fullLink = $link_new;
		} else if ($id_template == 3) {
			$fullLink .= '#page=1';
		}
		return $fullLink;
	}

}

<?

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "items".
 *
 * @property integer $id
 * @property string $title
 * @property string $link
 * @property string $link_img
 * @property string $link_new
 * @property string $now
 * @property string $new
 * @property integer $id_template
 * @property integer $offset
 * @property integer $dt_update
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
			[['id', 'id_template', 'offset', 'dt_update'], 'integer'],
			[['title', 'link', 'link_img', 'link_new', 'now', 'new'], 'string'],
			[['title', 'link', 'id_template'], 'required'],
			[['title', 'link'], 'safe', 'on' => self::SCENARIO_SEARCH],
		];
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
				'name' => 'youtube.com'
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'title' => 'Название',
			'link' => 'Ссылка',
			'link_img' => 'Ссылка на постер',
			'link_new' => 'Ссылка на новинку',
			'now' => 'Сейчас',
			'new' => 'Новый',
			'id_template' => 'Шаблон',
			'offset' => 'Смещение',
			'dt_update' => 'Дата новинки',
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
		}
		return parent::beforeSave($insert);
	}

	/**
	 * Create DataProvider for GridView.
	 * @return \yii\data\ActiveDataProvider
	 */
	public function search() {
		$query = self::find()->andFilterWhere([
					'id' => $this->id,
					'title' => $this->title,
					'link' => $this->link,
					'id_template' => $this->id_template,
				])
				->orderBy('(dt_update is null), dt_update desc, id');

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

}

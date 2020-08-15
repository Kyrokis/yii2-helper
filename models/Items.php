<?

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\models\User;
use app\modules\template\models\Template;

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
 * @property string $error
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
			[['title', 'link', 'link_img', 'link_new', 'now', 'new', 'error', 'del'], 'string'],
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
	 * Template
	 * @return \app\modules\template\models\Template
	 */
	public function getTemplate() {
		return $this->hasOne(Template::className(), ['id' => 'id_template']);
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
			'error' => 'Ошибка',
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
					'error' => $this->error,
					'del' => '0',
				])
				->andFilterWhere(['ILIKE', 'title', $this->title])
				->orderBy('user_id, (dt_update is null), (now != new) desc, dt_update desc, id');

		return new \yii\data\ActiveDataProvider(['query' => $query, 'pagination' => false, 'sort' => false]);
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
		if ($id_template == 2 || $id_template == 7) {
			$fullLink = $link_new;
		} else if ($id_template == 3) {
			$fullLink .= '#page=1';
		} else if ($id_template == 6) {
			$fullLink = 'https://www.lostfilm.tv/v_search.php?a=' . $link_new;
		} else if ($id_template == 8) {
			$id = explode('episode_', $link_new);
			$fullLink = 'https://rarbgmirror.org/tv.php?ajax=1&tvepisode=' . $id[1];
		}
		return $fullLink;
	}

}

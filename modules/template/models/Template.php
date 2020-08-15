<?

namespace app\modules\template\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\models\User;

/**
 * This is the model class for table "template".
 *
 * @property integer $id
 * @property string $name
 * @property string $title
 * @property string $new
 * @property string $link_new
 * @property string $link_img
 * @property string $full_link
 * @property integer $user_id
 * @property string $type
 * @property string $del
 */
class template extends ActiveRecord {

	const SCENARIO_SEARCH = 'search';

	/**
	 * @var string Title text
	 */
	public $title1;

	/**
	 * @var string Title text
	 */
	public $title2;

	/**
	 * @var string New text
	 */
	public $new1;

	/**
	 * @var string New text
	 */
	public $new2;

	/**
	 * @var string Link new
	 */
	public $link_new1;

	/**
	 * @var string Link new
	 */
	public $link_new2;

	/**
	 * @var string Link img
	 */
	public $link_img1;

	/**
	 * @var string Link img
	 */
	public $link_img2;


	/**
	 * @var string Link prefix
	 */
	public $full_link1;

	/**
	 * @var string Link postfix
	 */
	public $full_link2;
	

	/**
	 * @return string
	 */
	public static function tableName() {
		return 'template';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['id', 'user_id'], 'integer'],
			[['name', 'type', 'del'], 'string'],
			[['title1', 'title2', 'link_new1', 'link_new2', 'link_img1', 'link_img2', 'new1', 'new2', 'full_link1', 'full_link2', 'type', 'del'], 'string'],
			[['name', 'type'], 'required'],
			[['title', 'link_new', 'link_img', 'new', 'full_link'], 'each', 'rule' => ['string']],
			[['title1', 'title2', 'link_new1', 'link_new2', 'link_img1', 'link_img2', 'new1', 'new2'], 'required', 'when' => function($model) {return $model->type != 2;}, 
																													'whenClient' => "function (attribute, value) { return $('#template-type').val() != '2'; }"],
			[['title'], 'safe', 'on' => self::SCENARIO_SEARCH],
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
	public static function typeList() {
		return [
			'QueryList', 
			'Explode', 
			'Api'
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'ID',
			'name' => 'Название',
			'title' => 'Заголовок',
			'title1' => 'Заголовок 1',
			'title2' => 'Заголовок 2',
			'new' => 'Новый',
			'new1' => 'Новый 1',
			'new2' => 'Новый 2',
			'link_new' => 'Ссылка на новинку',
			'link_new1' => 'Ссылка на новинку 1',
			'link_new2' => 'Ссылка на новинку 2',
			'link_img' => 'Ссылка на постер',
			'link_img1' => 'Ссылка на постер 1',
			'link_img2' => 'Ссылка на постер 2',
			'full_link' => 'Полная ссылка',
			'full_link1' => 'Префикс ссылки',
			'full_link2' => 'Постфикс ссылки',
			'user_id' => 'ID юзера',
			'type' => 'Тип парсинга',
			'del' => 'Удален',
		];
	}

	/**
	 * @inheritdoc
	 */
	public function beforeValidate() {
		$this->title = [$this->title1, $this->title2];
		$this->new = [$this->new1, $this->new2];
		$this->link_new = [$this->link_new1, $this->link_new2];
		$this->link_img = [$this->link_img1, $this->link_img2];
		$this->full_link = [$this->full_link1, $this->full_link2];
		return parent::beforeValidate();
	}

	/**
	 * @inheritdoc
	 */
	public function load($data, $formName = null) {
		$flag = parent::load($data, $formName);
		if (!$data) {
			if ($this->title) {
				$this->title1 = $this->title[0];
				$this->title2 = $this->title[1];
			}
			if ($this->new) {
				$this->new1 = $this->new[0];
				$this->new2 = $this->new[1];
			}
			if ($this->link_new) {
				$this->link_new1 = $this->link_new[0];
				$this->link_new2 = $this->link_new[1];
			}
			if ($this->link_img) {
				$this->link_img1 = $this->link_img[0];
				$this->link_img2 = $this->link_img[1];
			}
			if ($this->full_link) {
				$this->full_link1 = $this->full_link[0];
				$this->full_link2 = $this->full_link[1];
			}	
		}
		return $flag;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert) {
		if ($insert) {
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
		$user_id = $this->user_id;
		$query = self::find()->andFilterWhere([
					'id' => $this->id,
					'type' => $this->type,
					'del' => '0',
				])
				->andFilterWhere(['ILIKE', 'name', $this->name])
				->orderBy('(user_id is null), user_id, id');
		if ($user_id) {
			$query->andWhere(['OR', ['user_id' => $user_id], ['IS', 'user_id', null]]);
		}
		return new \yii\data\ActiveDataProvider(['query' => $query, 'pagination' => false, 'sort' => false]);
	}

	/**
	 * Получить список вида id => name
	 * @return array
	 */
	public static function all() {
		return ArrayHelper::map(self::find()->andWhere(['OR', ['user_id' => Yii::$app->user->id], ['IS', 'user_id', null]])->andWhere(['del' => '0'])->all(), 'id', 'name');
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
		$template = self::findOne($id_template);
		return $template->full_link[0] . $link_new . $template->full_link[1];
	}
}

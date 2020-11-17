<?

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\modules\user\models\User;
use app\models\ItemsHistory;
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
	 * @return \app\modules\user\models\User
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
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getHistory() {
		return $this->hasMany(ItemsHistory::className(), ['item_id' => 'id'])->orderBy('dt desc');
	}

	/**
	 * ItemsHistory
	 * @return \app\models\ItemsHistory
	 */
	public function getPrevValue() {
		return $this->hasOne(ItemsHistory::className(), ['item_id' => 'id'])->offset(1)->orderBy('dt desc');
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
				$this->dt_update = time();
			}
			$this->user_id = Yii::$app->user->id;
		}
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($insert, $changedAttributes) {
		if ($insert || $changedAttributes['new'] || $changedAttributes['link_new']) {
			ItemsHistory::add($this->id, $this->new, Template::getFullLink($this->link_new, $this->id_template));
		}
		return parent::afterSave($insert, $changedAttributes);
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
	 * List of id => title
	 * @return array
	 */
	public static function all($item_id = null) {
		//$user_id = Yii::$app->user->identity->admin ? null : Yii::$app->user->id;
		$user_id = Yii::$app->user->id;
		return ArrayHelper::map(self::find()->select(['id', 'title'])->where(['del' => '0'])->andFilterWhere(['OR', ['user_id' => $user_id], ['id' => $item_id]])->orderBy('id')->all(), 'id', 'title');
	}

	/**
	 * Get estimate time for update
	 * @return integer
	 */
	public function getEstimate() {
		$dates = ArrayHelper::getColumn(ItemsHistory::find()->select(['dt'])->where(['item_id' => $this->id])->orderBy('dt desc')->all(), 'dt');
		if (count($dates) > 1) {
			for ($i = 0; $i < count($dates) - 1; $i++) { 
				$ranges[] = $dates[$i] - $dates[$i + 1];
			}
			$count = count($ranges);
			if ($count > 0) {
				while (true) {
					$avg = round(array_sum($ranges) / $count);
					$variance = [];
					foreach ($ranges as $range) {
						$variance[] = pow($range - $avg, 2);
					}
					$deviation = round(sqrt(array_sum($variance) / $count));
					if ($deviation < $avg || $count == 1) {
						return [$avg - $deviation, $avg + $deviation];
					}
					unset($ranges[array_search(max($variance), $variance)]);
					$ranges = array_values($ranges);
					$count--;
				}

			}
		}
		return 0;
	}
}

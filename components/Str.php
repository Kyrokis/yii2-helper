<?

namespace app\components;

/**
 * Helper working with strings
 */
class Str {

	/**
	 * Translate date months
	 * @param string $dt
	 * @return string
	 */
	public static function dateEngToRu($dt) {
		$monthEn = [
			'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December',
			'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
		];
		
		$monthRu = [
			'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря',
			'янв', 'фев', 'мар', 'апр', 'мая', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'
		];

		return str_replace($monthEn, $monthRu, $dt);
	}
}
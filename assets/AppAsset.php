<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [
		// Font Awesome
		'lib/css/font-awesome/css/font-awesome.min.css',
		// Theme style
		'lib/alte/dist/css/AdminLTE.min.css',
		// Admin Skin
		'lib/alte/dist/css/skins/skin-blue.min.css',
		'lib/css/site.css',
	];
	public $js = [
		'lib/js/site.js',
		// Admin App
		'lib/alte/dist/js/adminlte.min.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
		'yii\bootstrap\BootstrapAsset',
		'yii\bootstrap\BootstrapPluginAsset',
		'kartik\select2\Select2Asset', // use in select2modify
		'kartik\select2\ThemeKrajeeAsset', // use in select2modify
	];
}

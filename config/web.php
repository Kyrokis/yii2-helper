<?php

$db = require __DIR__ . '/db.php';

$config = [
	'id' => 'Helper',
	'name' => 'Telegram helper',
	'basePath' => dirname(__DIR__),
	'language' => 'ru-RU',
	'timeZone' => 'Europe/Moscow',
	'bootstrap' => ['debug', 'log', 'gii'],
	'aliases' => [
		'@bower' => '@vendor/bower-asset',
		'@npm'   => '@vendor/npm-asset',
	],
	'defaultRoute' => 'helper',
	'components' => [
		'request' => [
			// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
			'cookieValidationKey' => 'elzzkBqIHE4jXvMgIj70RQsau4KSD0dz',
		],
		'cache' => [
			'class' => 'yii\caching\FileCache',
		],
		'user' => [
			'identityClass' => 'app\models\User',
			'enableAutoLogin' => true,
			'loginUrl' => '/user/default/login',
		],
		'errorHandler' => [
			'errorAction' => 'site/error',
		],
		'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
			]
		],
		'telegram' => [
			'class' => 'aki\telegram\Telegram',
			'botToken' => '', // Insert here your telegram bot token
		],
		'db' => $db,
	],
	'modules' => [
		'helper' => 'app\modules\helper\HelperModule',
		'telegram' => 'app\modules\telegram\TelegramModule',
		'user' => 'app\modules\user\UserModule',
		'debug' => [
			'class' => 'yii\debug\Module',
			'allowedIPs' => ['*'],
		],	
		'gii' => [
			'class' => 'yii\gii\Module',
			'allowedIPs' => ['*'],
		],
		'gridview' => '\kartik\grid\Module',
	],
	'params' => [
		'googleApiKey' => '', // Insert here your google api key
		'vkApiKey' => '', // Insert here your vk api key
	],
];

return $config;

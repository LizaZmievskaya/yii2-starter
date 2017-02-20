<?php

$params = require(__DIR__ . '/params.php');
$routes = \yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/routes.php'),
    require(__DIR__ . '/../modules/mailTemplate/config/routes.php'),
    require(__DIR__ . '/../modules/user/config/routes.php'),
    require(__DIR__ . '/../modules/page/config/routes.php'),
    require(__DIR__ . '/../modules/feedback/config/routes.php')
);
$clients = require(__DIR__ . '/clients.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'option'],
    'aliases' => [
        '@bower' => '@vendor/bower',
    ],
    'language' => 'en-US',
    'components' => [
        'assetManager' => [
            'linkAssets' => YII_ENV_DEV ? true : false,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'bLuSJS-EnGQOxVJRARHg9WzzV8RhMeAe',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\modules\user\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => require(__DIR__ . '/mailer.php'),
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => $routes,
        ],
        'i18n' => [
            'translations' => [
                'site' => [
                    'class' => 'yii\i18n\GettextMessageSource',
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en_US',
                ],
            ],
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => $clients,
        ],
        'formatter' => [
            'dateFormat' => 'd-M-Y',
            'datetimeFormat' => 'php:Y-m-d H:i:s',
            'timeFormat' => 'H:i:s',
            'locale' => 'en-US',
            'defaultTimeZone' => 'Europe/Kiev',
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'app\modules\user\Module',
        ],
        'mailTemplate' => [
            'class' => 'app\modules\mailTemplate\Module',
        ],
        'page' => [
            'class' => 'app\modules\page\Module',
        ],
        'option' => [
            'class' => 'app\modules\option\Module',
        ],
        'feedback' => [
            'class' => 'app\modules\feedback\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['192.168.10.1'],
    ];
}

return $config;

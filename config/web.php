<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@uploads' => '@app/web/uploads/',
    ],
    'components' => [
        'request' => [
            'baseUrl' => '/api',
            'parsers' => [
                'multipart/form-data' => 'yii\web\MultipartFormDataParser',
                'application/json' => 'yii\web\JsonParser',
            ],
            'cookieValidationKey' => 'ALJKtzZN3ZNYRZaW2nzrhg4dlXYlnRc2',
            'enableCsrfValidation' => false,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => true,
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
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                'login' => 'user/login',
                'register' => 'user/register',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'user',
                    'patterns' => [
                        'POST {id}' => 'patch',
                        'GET {id}' => 'view',
//                        'GET {id}/adverts' => 'adverts',

                    ],
                ],
//                'POST v1/avatars' => 'avatar/upload',
//                'POST <module:[\w-]+>/profiles/<id:\d+>' => '<module>/profile',
//                ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'advert',
                    'patterns' => [
                        'POST' => 'create',
                        'POST {id}' => 'patch',
                        'PATCH {id}' => 'close',
                        'GET {id}' => 'view',
                        'GET' => 'index',
                        'GET {id}/images' => 'images'
                    ],
                ],
                'GET users/<id\d+>/adverts' => 'advert/user-adverts',
                'GET cities' => 'city',
                'GET cities/<id\d+>' => 'city/view',
                'GET categories' => 'category',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;

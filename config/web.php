<?php

$params = require(__DIR__ . '/params.php');
$params['teamMembers'] = require(__DIR__ . '/team.php');
$params['languages'] = require(__DIR__ . '/languages.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', \akiraz2\blog\Bootstrap::class],
    'components' => [
    'httpClient' => [
        'class' => 'yii\httpclient\Client',
    ],
    'session' => [
        // store session in cache
        'class' => 'yii\web\CacheSession',
    ],
    'assetManager' => [
        'bundles' => [
            'yii\web\JqueryAsset' => [
                'jsOptions' => ['position' => \yii\web\View::POS_HEAD],
                // 'jsOptions' => [ 'position' => \yii\web\View::POS_HEAD, 'type' => 'text/javascript' ],
                // 'js' => [ 'https://code.jquery.com/jquery-3.5.1.min.js' ],
                ],
            'yii\bootstrap\BootstrapAsset' => [
                'depends' => ['yii\jui\JuiAsset'],
                ],
            ],
            'appendTimestamp' => true,
    ],
    'solr' => [
        'class' => 'sammaye\solr\Client',
        'options' => [
            'endpoint' => [
                'solr1' => [
                    'host' => $params['solrHost'],
                    'core' => 'prod',
                    'port' => '8983',
                    'path' => '/solr',
                    'timeout' => 30,
                ],
            ]
        ]
    ],
    'request' => [
        // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
        'cookieValidationKey' => $params['cookieValidationKey'],
    ],
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'bipstring' => [
        'class' => 'app\components\BipStringHelper',
    ],
    'viewregister' => [
        'class' => 'app\components\ViewRegister',
    ],
    'view' => [
        'theme' => [
            'pathMap' => [
                '@akiraz2/yii2-blog/views/frontend/default' => '@app/views/site/blog',
            ],
        ],
    ],
        'pyramidchart' => [
            'class' => 'app\components\PyramidChart',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
    ],
    'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            //Use this on athena network
            'transport' => [
                'class' => 'Swift_SmtpTransport',

                //ON ATHENA
                'host' => $params['mailHost'],
                'port' => '25',

                //Keep this for all configurations
                'encryption' => 'tls',
                 'streamOptions' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ],
            ],
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
        'db' => require(__DIR__ . '/db.php'),
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'search/<space_url_suffix>' => 'site/index',
                'search' => 'site/index',
                'compare' => 'site/comparison',
                'details/<local_identifier>' => 'site/details-by-local-id',
                'details' => 'site/details',

                // Rewrite rules for author page
                'author/<author>/<ordering>/<page:\d+>' => 'site/author',
                'author/<author>/<ordering:(popularity|influence)>' => 'site/author',
                'author/<author>/<page:\d+>' => 'site/author',
                'author/<author>' => 'site/author',
                'site/auto-complete-journals/<expansion>/<max_num>' => 'site/auto-complete-journals',
                'site/auto-complete-concepts/<expansion>/<max_num>' => 'site/auto-complete-concepts',

                // annotation pages
                '<space_url_suffix>/annotation/<annotation_id>' => 'site/annotation',

                // scholar pages
                'scholar' => 'scholar/index',
                'scholar/profile/<orcid>' => 'scholar/profile',
                'scholar/profile/<orcid>/<template_url_name>' => 'scholar/profile',

                'scholar/myprofile' => 'scholar/myprofile',
                'scholar/myprofile/<template_url_name>' => 'scholar/myprofile',

                // readings pages
                'readings' => 'readings/index',
                'readings/list/<reading_list_id>' => 'readings/list',

                // spaces pages
                'spaces' => 'spaces/index',

                // blog pages
                'blog/<id:\d+>-<slug:[^/]+>' => 'blog/default/view',
                'blog/<id:\d+>' => 'blog/default/view',

                'site/blog/create' => 'blog/default/create',
                'site/blog/update/<id:\d+>' => 'blog/default/update',
                'site/blog/<tag:[^/]+>' => 'blog/default/index',
                'site/blog' => 'blog/default/index',
            ],
        ],
    ],
    'modules' => [
        'blog' => [
            'class' => 'akiraz2\blog\Module',
            'controllerMap' => [
                'default' => 'app\controllers\BlogController',
            ],
            'controllerNamespace' => 'akiraz2\blog\controllers\frontend',
            'userModel' => 'app\models\User',
            'userPK' => 'id',
            'userName' => 'username',
            'blogPostPageCount' => 9,
            'blogCommentPageCount' => 10,
            'enableComments' => false,
            'imgFilePath' => '@app/web/img/blog/',
            'imgFileUrl' => '/img/blog/',
        ],
        'gridview' => [
            'class' => 'kartik\grid\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
       'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;

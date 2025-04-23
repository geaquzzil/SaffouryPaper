<?php
return [
    'settings' => [
        // 'determineRouteBeforeAppMiddleware' => false,
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../Templates/',
        ],

        'redis' => [
            'enabled' => $_SERVER['REDIS_ENABLED'],
            'host' => $_SERVER['REDIS_URL'],
            'port'   => 6379,
            'scheme' => 'tcp',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Level::Debug
        ],
        'db' => [
            'driver' => 'mysql',
            'host' => $_SERVER['DB_HOST'],
            'name' => $_SERVER['DB_NAME'],
            'user' => $_SERVER['DB_USER'],
            'pass' => $_SERVER['DB_PASS'],
            'port' => $_SERVER['DB_PORT'],
            'charset' => $_SERVER['charset'],
            'collation' => $_SERVER['collation'],
        ],
        'app' => [
            'domain' => $_SERVER['APP_DOMAIN'] ?? '',
            'secret' => $_SERVER['SECRET_KEY'],
        ],
    ]
];


// return [
//     'settings' => [
//         'displayErrorDetails' => filter_var(
//             $_SERVER['DISPLAY_ERROR_DETAILS'],
//             FILTER_VALIDATE_BOOLEAN
//         ),
//         'db' => [
//             'host' => $_SERVER['DB_HOST'],
//             'name' => $_SERVER['DB_NAME'],
//             'user' => $_SERVER['DB_USER'],
//             'pass' => $_SERVER['DB_PASS'],
//             'port' => $_SERVER['DB_PORT'],
//         ],
//         'redis' => [
//             'enabled' => $_SERVER['REDIS_ENABLED'],
//             'url' => $_SERVER['REDIS_URL'],
//         ],
//         'app' => [
//             'domain' => $_SERVER['APP_DOMAIN'] ?? '',
//             'secret' => $_SERVER['SECRET_KEY'],
//         ],
//     ],
// ];

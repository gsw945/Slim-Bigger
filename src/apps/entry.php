<?php

return [
    'api' => [
        'prefix' => '/api',
        'urls' => require __DIR__ . '/api/urls.php'
    ],
    'admin' => [
        'prefix' => '/admin',
        'urls' => require __DIR__ . '/admin/urls.php'
    ],
    'inner' => [
        'prefix' => '/inner',
        'urls' => require __DIR__ . '/inner/urls.php'
    ],
    'home' => [
        'prefix' => '',
        'urls' => [
            '/[home[/]]' => [
                'get' => [
                    'handler' => function(\Slim\Http\Request $request, \Slim\Http\Response $response, $args=[]) {
                        $response->getBody()->write("home");
                        return $response;
                    },
                    'name' => 'site_home',
                    'methods' => ['GET', 'POST']
                ],
            ],
        ]
    ],
    'auth' => [
        'prefix' => '/auth',
        'urls' => require __DIR__ . '/auth/urls.php'
    ],
    'db' => [
        'prefix' => '/db',
        'urls' => [
            '/up[/[{table:\w+}[/]]]' => [
                'get' => [
                    'handler' => '\App\Dev\Init\DBMigration:up',
                    'name' => 'db_up',
                    'auth' => true
                ]
            ],
            '/down[/[{table:\w+}[/]]]' => [
                'get' => [
                    'handler' => '\App\Dev\Init\DBMigration:down',
                    'name' => 'db_down',
                    'auth' => true
                ]
            ],
        ]
    ],
];

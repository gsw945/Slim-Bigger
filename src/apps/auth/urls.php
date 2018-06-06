<?php

return [
    '[/]' => [
        'get' => [
            'handler' => 'App\Views\AuthView:login',
            'name' => 'auth_login_index'
        ],
    ],
    '/login[/]' => [
        'post' => [
            'handler' => '\App\Controllers\AuthController:login',
            'name' => 'auth_login'
        ],
        'get' => [
            'handler' => 'App\Views\AuthView:login',
            'name' => 'auth_login_view'
        ],
    ],
    '/logout[/]' => [
        'post' => [
            'handler' => '\App\Controllers\AuthController:logout',
            'name' => 'auth_logout',
            'auth' => true
        ],
        'get' => [
            'handler' => 'App\Views\AuthView:logout',
            'name' => 'auth_logout_view'
        ],
    ],
    '/retrieve_password[/]' => [
        'post' => [
            'handler' => '\App\Controllers\AuthController:retrieve_password',
            'name' => 'auth_retrieve_password'
        ],
        'get' => [
            'handler' => 'App\Views\AuthView:retrieve_password',
            'name' => 'auth_retrieve_password_view'
        ],
    ],
];
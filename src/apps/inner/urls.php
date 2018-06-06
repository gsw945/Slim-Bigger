<?php

return [
    '/token/encode[/]' => [
        'map' => [
            'handler' => '\App\Controllers\TokenController:token_encode',
            'name' => 'inner_token_encode',
            'methods' => ['GET', 'POST']
        ],
    ],
    '/token/decode[/]' => [
        'map' => [
            'handler' => function(\Slim\Http\Request  $request, \Slim\Http\Response  $response, $args=[]) {
                $secret = $this->get('settings')['secret'];
                $response->getBody()->write($secret);
                return $response;
            },
            'name' => 'inner_token_decode',
            'methods' => ['GET', 'POST']
        ],
    ],
];
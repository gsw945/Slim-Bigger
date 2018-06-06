<?php

$api_part_urls = require __DIR__ . '/api_part_urls.php';
$ping_urls = [
    '/ping[/]' => [
        'map' => [
            'handler' => function(\Slim\Http\Request  $request, \Slim\Http\Response  $response, $args=[]) {
                $response->getBody()->write("pong");
                return $response;
            },
            'name' => 'api_ping',
            'methods' => ['GET', 'POST']
        ],
    ],
    '/auth-ping[/]'=>[
        'get'=>[
            'handler' => function(\Slim\Http\Request  $request, \Slim\Http\Response  $response, $args=[]) {
                $response->getBody()->write("auth pong");
                return $response;
            },
            'name'=>'auth_ping',
            'auth'=>true,
            'op_name'=>'auth_ping',
        ]
    ],
    '/token-ping[/]'=>[
        'get'=>[
            'handler' => function(\Slim\Http\Request  $request, \Slim\Http\Response  $response, $args=[]) {
                $authed_token = $request->getAttribute('authed_token');
                return $response->withJson($authed_token);
                // $response->getBody()->write("token pong");
                // return $response;
            },
            'name'=>'token_ping',
            'token'=>true,
            'op_name'=>'token_ping',
        ],
        'put'=>[
            'handler' => function(\Slim\Http\Request  $request, \Slim\Http\Response  $response, $args=[]) {
                $params = $request->getParams();
                $parsed = $request->getParsedBody();
                $authed_token = $request->getAttribute('authed_token');
                return $response->withJson([
                    'params' => $params,
                    'parsed' => $parsed,
                    'Content-Type' => $request->getHeaderLine('Content-Type'),
                    'is_put' => $request->isPut(),
                    'authed' => $authed_token
                ]);
                // $response->getBody()->write("token pong");
                // return $response;
            },
            'name'=>'token_ping_pug',
            'token'=>true,
            'op_name'=>'token_ping_put',
        ]
    ]
];

return array_merge(
    $ping_urls,
    $api_part_urls,
    // TODO: another api part urls
);

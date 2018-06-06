<?php

namespace App\Middlewares;

/**
 * token调用
 */
class TokenCall extends Base {

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, callable  $next) {
        $route = $request->getAttribute('route');
        if(isset($route)) {
            $not_xhr = ($request->isGet() && $request->isXhr() == false);
            // 获取当前路由
            $name = $route->getName();
            // 仅供token访问的路由列表
            $token_route_list = $this->container->get('globals')->get('tokenRouteList');
            if(in_array($name,  $token_route_list)) {
                $secret = $this->container->get('settings')['secret'];
                $verified = static::verify_access($request, $secret);
                if(!$verified['ok']) {
                    if($not_xhr) {
                        $base_url = base_path($request);
                        if(empty($base_url)) {
                            $base_url = '/';
                        }
                        return $this->container->get('twig')->render(
                            $response->withStatus(403),
                            '_error/403.twig',
                            [
                                'base_url' => $base_url
                            ]
                        );
                    }
                    else {
                        $ret = [
                            'error' => 1,
                            'desc' => $verified['error']
                        ];
                        $response->withStatus(403)->getBody()->write(json_encode($ret));
                        return $response;
                    }
                }
                else {
                    $authed_token = $verified['data'];
                    $request = $request->withAttribute('authed_token', $authed_token);
                }
            }
        }

        $response = $next($request, $response);
        return $response;
    }

    // 验证访问(并返回数据)
    private static function verify_access($request, $secret) {
        // 获取access-token
        $params = $request->getParams();
        $access_token = $request->getHeaderLine('X-Token');
        if(empty($access_token)) {
            $access_token = array_get($params, 'token');
        }
        $ok = false;
        $error = null;
        $data = null;
        if(empty($access_token)) {
            $error = '缺少参数';
        }
        else {
            // base64格式的数据，带有斜杠(/)和等号(=)等特殊字符，
            // 在传输过程中，需要编码(js: encodeURIComponent)，
            // 在使用参数时需要先解码(php: rawurldecode)
            $access_token = rawurldecode($access_token);
            $parsed = \App\Controllers\TokenController::parse_token($secret, $access_token);
            if(is_array($parsed) && array_key_exists('open_id', $parsed) && array_key_exists('union_id', $parsed)) {
                $open_id = $parsed['open_id'];
                $union_id = $parsed['union_id'];
                $sign = $parsed['sign'];
                $data = [
                    'open_id' => $open_id,
                    'union_id' => $union_id,
                    'sign' => $sign
                ];
                $ok = true;
            }
            else {
                $error = 'token　无效';
            }
        }
        return [
            'ok' => $ok,
            'error' => $error,
            'data' => $data
        ];
    }
}
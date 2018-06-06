<?php

namespace App\Middlewares;

/**
 * 内部调用
 */
class InnerCall extends Base {

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, callable  $next) {
        $route = $request->getAttribute('route');
        if(isset($route)) {
            $not_xhr = ($request->isGet() && $request->isXhr() == false);
            // 获取当前路由
            $name = $route->getName();
            // 仅供内部访问的路由列表
            $inner_list = $this->container->get('globals')->get('innerList');
            if(in_array($name,  $inner_list)) {
                // 判断是否是内部访问(暂时仅通过 header 判断)
                $cfg_callback = $this->container->get('settings')['callback'];
                $header_key = $cfg_callback['header'];
                $req_callback = $request->getHeaderLine($header_key);
                $passed = $req_callback == $cfg_callback['value'];
                if(!$passed) {
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
                            'desc' => '没有访问权限'
                        ];
                        $response->withStatus(403)->getBody()->write(json_encode($ret));
                        return $response;
                    }
                }
            }
        }

        $response = $next($request, $response);
        return $response;
    }
}

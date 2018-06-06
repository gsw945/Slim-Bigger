<?php

namespace App\Views;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * 认证视图
 */
class AuthView extends \App\Controllers\ControllerBase {
    /**
     * 登录页面
     */
    public function login(Request $request, Response $response, $args=[]) {
        $params = [];
        $logined_user = $request->getAttribute('logined_user');
        if(is_array($logined_user)) {
            $router = $this->ci->get('router');
            $admin_url = $router->pathFor('admin_index');
            $response->getBody()->rewind();
            $uri = $request->getUri()->withPath($admin_url);
            // $rand = sprintf('?t=%.5f', microtime(true));
            // $uri = $uri->withQuery($rand);
            return $response->withRedirect($uri, 301);
        }

        return $this->ci->get('twig')->render($response, 'auth/pages/login.twig', $params);
    }

    /**
     * 注销
     */
    public function logout(Request $request, Response $response, $args=[]) {
        $params = [];

        if(isset($_SESSION['logined_user'])) {
            unset($_SESSION['logined_user']);
        }

        return $this->ci->get('twig')->render($response, 'auth/pages/logout.twig', $params);
    }

    /**
     * 找回密码
     */
    public function retrieve_password(Request $request, Response $response, $args=[]) {
        $params = [];

        return $this->ci->get('twig')->render($response, 'auth/pages/retrieve-password.twig', $params);
    }
}
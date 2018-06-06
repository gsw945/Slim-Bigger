<?php

namespace App\Middlewares;

/**
 * 模板分配中间件
 */
class TemplateAssign extends Base {

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, callable  $next) {
        $router = $this->container->get('router');
        $view = $this->container->get('twig');
        $vars = [];
        $path = $request->getUri()->getPath();

        if(starts_with($path, '/')) {
            $path = substr($path, 1);
        }
        $tmp = explode('/', $path);
        $template_urls = [
            'logout_url' => 'auth_logout'
        ];
        switch ($tmp[0]) {
            case '':
            case 'auth':
                $auth_urls = [
                    'login_url' => 'auth_login'
                ];
                $template_urls = array_merge($template_urls, $auth_urls);
                break;
            case 'admin':
                // if(count($tmp) > 1 && in_array($tmp[1], ['login', 'logout'])) {
                //     break;
                // }
                // 设置 sidebar 导航地址
                $admin_urls = [
                    // 'logout_url' => 'admin_logout',
                    // 'changepwd_url' => 'admin_changepwd',
                    // 'admin_index' => 'admin_index'
                ];
                $template_urls = array_merge($template_urls, $admin_urls);
                // 设置用户名
                // $vars['logined_user'] = $this->container->get('session')->get('logined_user');
                break;
            default:
                break;
        }

        foreach ($template_urls as $url_key => $name) {
            $rand = '';
            if($url_key == 'logout_url') {
                // 增加时间戳，防止缓存
                $rand = sprintf('?t=%.5f', microtime(true));
            }
            $vars[$url_key] = $router->pathFor($name) . $rand;
        }
        $vars['site_base_url'] = base_path($request);

        $vars['platform'] = [
            'name' => '管理平台'
        ];

        $settings = $this->container->get('settings');
        if($settings['debug'] && $settings['tracy_debug']) {
            $vars['tracy_debug'] = true;
        }

        if($settings['can_add_role']) {
            $vars['can_add_role'] = true;
        }

        $logined_user = @$_SESSION['logined_user'];
        if(is_array($logined_user) && array_key_exists('username', $logined_user)) {
            $route = $request->getAttribute('route');
            if(!is_null($route)) {
                $name = $route->getName();
                if($name != 'auth_logout_view') {
                    $vars['logined_user'] = $logined_user;
                }
            }
        }

        foreach ($vars as $key => $value) {
            // $view->getEnvironment()->addGlobal($key, $value); // assign variable(style 1)
            $view->offsetSet($key, $value); // assign variable(style 2)
        }

        $response = $next($request, $response);
        return $response;
    }
}

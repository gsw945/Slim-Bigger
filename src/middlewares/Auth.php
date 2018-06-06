<?php

namespace App\Middlewares;

use App\Models\OperationRoleMap;

/**
 * 认证
 */
class Auth extends Base {

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, callable  $next) {
        $route = $request->getAttribute('route');
        
        $logined_user = @$_SESSION['logined_user'];
        $has_logout = false;
        if(isset($route)) {
            // 获取当前路由
            $name = $route->getName();
            if($name != 'auth_logout_view') {
                // 需要认证后才能访问的路由列表
                $auth_list = $this->container->get('globals')->get('authList');
                if(in_array($name,  $auth_list)) {
                    // 1. 登录状态验证
                    $passed = is_array($logined_user) && array_key_exists('role', $logined_user);
                    if(!$passed) {
                        return $this->abort_403($request, $response);
                    }
                    else {
                        // 2. 访问权限验证
                        $role = $logined_user['role'];
                        // 特权超级角色
                        $super_role = ($role['manage'] && $role['predefine']);
                        if(!$super_role) {
                            // 非特权用户需要验证详细的权限
                            $path = $request->getUri()->getPath();

                            if(starts_with($path, '/')) {
                                $path = substr($path, 1);
                            }
                            $tmp = explode('/', $path);
                            // 管理界面访问
                            if($tmp[0] == 'admin') {
                                if(!$role['manage']) {
                                    // 如果访问的是管理界面，但是所属角色没有管理界面访问权限，则403
                                    return $this->abort_403($request, $response);
                                }
                            }
                            // 详细权限验证
                            if(!$this->protect_access($role, $name)) {
                                return $this->abort_403($request, $response);
                            }
                        }
                        // TODO: 如果有必要，在这里记录(需要登录后才能操作)访问记录
                    }
                }
            }
            else {
                $has_logout = false;
            }
        }

        if(!$has_logout && isset($logined_user)) {
            $request = $request->withAttribute('logined_user', $logined_user);
        }

        $response = $next($request, $response);
        return $response;
    }

    // 访问保护(权限验证)
    private function protect_access($role, $name) {
        return OperationRoleMap::is_allowed_by_id($role['id'], $name);
    }

    // go to 403
    private function abort_403($request, $response) {
        $not_xhr = ($request->isGet() && $request->isXhr() == false);
        if($not_xhr) {
            $flash = $this->container->get('flash');
            $flash->addMessage('flash_message', '没有访问权限');
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
<?php

namespace App\Views;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * 管理员视图
 */
class AdminView extends \App\Controllers\ControllerBase {
    /**
     * 主页面
     */
    public function index(Request $request, Response $response, $args=[]) {
        $params = [];

        return $this->ci->get('twig')->render($response, 'admin/pages/index.twig', $params);
    }

    /**
     * 用户列表
     */
    public function user_list(Request $request, Response $response, $args=[]) {
        $params = [];

        return $this->ci->get('twig')->render($response, 'admin/pages/user-list.twig', $params);
    }

    /**
     * 角色列表
     */
    public function role_list(Request $request, Response $response, $args=[]) {
        $params = [];

        return $this->ci->get('twig')->render($response, 'admin/pages/role-list.twig', $params);
    }

    /**
     * 权限分配
     */
    public function permission_assign(Request $request, Response $response, $args=[]) {
        $params = [];

        return $this->ci->get('twig')->render($response, 'admin/pages/permission-assign.twig', $params);
    }

    /**
     * 商户列表
     */
    public function merchant_list(Request $request, Response $response, $args=[]) {
        $params = [];

        return $this->ci->get('twig')->render($response, 'admin/pages/merchant-list.twig', $params);
    }
}
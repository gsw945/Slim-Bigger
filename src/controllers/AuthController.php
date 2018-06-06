<?php
namespace App\Controllers;

use \Illuminate\Database\Capsule\Manager as DB;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRoleMap;

/**
 * AuthController
 */
class AuthController extends \App\Controllers\ControllerBase
{
    // 登录
    public function login(\Slim\Http\Request $request, \Slim\Http\Response $response, $args = [])
    {
        $params = $request->getParams();
        
        $account = array_get($params, 'account');
        $password = array_get($params, 'password');
        $remember = array_get($params, 'remember');

        $objs = null;
        $password = text_encrypt($password);
        if(strpos($account, '@') !== false) {
            $objs = User::where('email', $account)
                ->where('password', $password)
                ->get();
        }
        else {
            $objs = User::where('username', $account)
                ->where('password', $password)
                ->get();
        }

        if(!is_null($objs) && $objs->count() > 0) {
            $obj = $objs->first();

            $tb_role = Role::table_name();
            $tb_urm = UserRoleMap::table_name();
            $roles = DB::table($tb_role)
                ->join($tb_urm,  "{$tb_role}.id", '=', "{$tb_urm}.role")
                ->where("{$tb_urm}.user", $obj->id)
                ->select("{$tb_role}.*")
                ->get();
            $logined_data = [
                'id' => $obj->id,
                'username' => $obj->username,
                'email' => $obj->email
            ];
            if(!is_null($roles) && $roles->count() > 0) {
                $role = $roles->first();
                $logined_data['role'] = [
                    'id' => $role->id,
                    'name' => $role->name,
                    'cn_name' => $role->cn_name,
                    'manage' => $role->manage,
                    'predefine' => $role->predefine
                ];
            }
            else {
                throw new \Exception("Server Data Error");
                
            }

            $_SESSION['logined_user'] = $logined_data;

            $router = $this->ci->get('router');
            $admin_index = $router->pathFor('admin_index');
            // $rand = sprintf('?t=%.5f', microtime(true));
            $rand = '';
            $admin_index = $admin_index . $rand;
            $ret = [
                'error' => 0,
                'desc' => '登录成功',
                'data' => [
                    'url' => $admin_index
                ]
            ];
        }
        else {
            $error = '用户名或密码错误';
            $count = User::where('username', $account)
                ->orWhere('email', $account)
                ->count();
            if($count < 1) {
                $error = '用户不存在';
            }
            $ret = [
                'error' => 1,
                'desc' => $error
            ];
        }

        return $response->withJson($ret);
    }

    // 注销
    public function logout(\Slim\Http\Request $request, \Slim\Http\Response $response, $args = [])
    {
        $params = $request->getParams();
        // $path = array_get($params, 'path');
        if(isset($_SESSION['logined_user'])) {
            unset($_SESSION['logined_user']);
        }
        $router = $this->ci->get('router');
        $auth_logout_url = $router->pathFor('auth_logout_view');
        // $rand = sprintf('?t=%.5f', microtime(true));
        $rand = '';
        $auth_logout_url = $auth_logout_url . $rand;
        $ret = [
            'error' => 0,
            'desc' => '注销成功',
            'data' => [
                'url' => $auth_logout_url
            ]
        ];

        return $response->withJson($ret);
    }

    // 找回密码
    public function retrieve_password(\Slim\Http\Request $request, \Slim\Http\Response $response, $args = [])
    {
        $params = $request->getParams();
        // $path = array_get($params, 'path');
        $ret = [
            'error' => 0,
            'desc' => 'TODO forget password'
        ];
        return $response->withJson($ret);
    }
}
<?php

namespace App\Middlewares;

use \Illuminate\Database\Capsule\Manager as DB;
use App\Models\Operation;
use App\Models\OperationGroup;
use App\Models\OperationGroupMap;
use App\Models\Role;
use App\Models\OperationRoleMap;
use App\Models\User;
use App\Models\UserRoleMap;

/**
 * (开发阶段)数据初始化
 */
class DevDataInit extends Base {

    public function __invoke(\Slim\Http\Request $request, \Slim\Http\Response $response, callable  $next) {
        $config = $this->container->get('settings');
        if($config['debug']) {
            // 注册预定义角色
            $role_id = $this->add_predefine_role();
            // 注册预定义用户
            $user_id = $this->add_predefine_user();
            // 注册预定义用户角色关联
            $this->predefine_user_role_map($user_id, $role_id);
        } 

        $response = $next($request, $response);
        return $response;
    }

    // 设置预定义用户-角色关联
    private function predefine_user_role_map($user_id, $role_id) {
        $objs = UserRoleMap::where('user', $user_id)
            ->orWhere('role', $role_id)
            ->get();
        if($objs->count() > 0) {
            $old_obj = $objs->first();
            $old_obj->user = $user_id;
            $old_obj->role = $role_id;
            $old_obj->save();
        }
        else {
            $new_obj = new UserRoleMap();
            $new_obj->user = $user_id;
            $new_obj->role = $role_id;
            $new_obj->save();
        }
    }

    // 添加预定义用户
    private function add_predefine_user() {
        $user_obj = [
            'username' => 'admin',
            'email' => 'test@gsw945.com',
            'password' => text_encrypt('admin12345')
        ];
        $this->container->get('db'); // 容器注入, 实例化 db
        $objs = User::where('username', $user_obj['username'])
            ->orWhere('email', $user_obj['email'])
            ->get();
        $user_id = null;
        if($objs->count() > 0) {
            $old_obj = $objs->first();
            $old_obj->username = $user_obj['username'];
            $old_obj->email = $user_obj['email'];
            $old_obj->password = $user_obj['password'];
            $old_obj->save();
            $user_id = $old_obj->id;
        }
        else {
            $new_obj = new User();
            $new_obj->username = $user_obj['username'];
            $new_obj->email = $user_obj['email'];
            $new_obj->password = $user_obj['password'];
            $new_obj->save();
            $user_id = $new_obj->id;
        }
        return $user_id;
    }

    // 添加预定义角色
    private function add_predefine_role() {
        $role_obj = [
            'name' => 'system',
            'cn_name' => '系统特权用户',
            'manage' => true,
            'predefine' => true
        ];
        $this->container->get('db'); // 容器注入, 实例化 db
        $objs = Role::where('name', $role_obj['name'])
            ->orWhere('cn_name', $role_obj['cn_name'])
            ->get();
        $role_id = null;
        if($objs->count() > 0) {
            $old_obj = $objs->first();
            $old_obj->name = $role_obj['name'];
            $old_obj->cn_name = $role_obj['cn_name'];
            $old_obj->manage = $role_obj['manage'];
            $old_obj->predefine = $role_obj['predefine'];
            $old_obj->save();
            $role_id = $old_obj->id;
        }
        else {
            $new_obj = new Role();
            $new_obj->name = $role_obj['name'];
            $new_obj->cn_name = $role_obj['cn_name'];
            $new_obj->manage = $role_obj['manage'];
            $new_obj->predefine = $role_obj['predefine'];
            $new_obj->save();
            $role_id = $new_obj->id;
        }
        return $role_id;
    }
}

<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use \Illuminate\Database\Capsule\Manager as DB;

use App\Models\User;
use App\Models\Role;

/**
 * UserController操作
 */
class UserController extends ControllerBase {
    // 获取用户信息
    public function info(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        $id = array_get($args, 'id');
        if(!isset($id)) {
            $id = array_get($params, 'id');
        }
        $ret = [];
        if(text_is_digital($id)) {
            $data = User::get_by_id($id);
            if(!is_array($data) || empty($data)) {
                $ret = [
                    'error' => 1,
                    'desc' => '用户不存在'
                ];
            }
        }
        else {
            $data = User::get_all();
        }
        if(empty($ret)) {
            $ret = [
                'error' => 0,
                'desc' => null,
                'data' => $data
            ];
        }
        return $response->withJson($ret);
    }

    // 添加用户
    public function add(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        $username = array_get($params, 'username');
        $email = array_get($params, 'email');
        $password = array_get($params, 'password');
        
        if(text_in_range($username, 2, 20)) {
            if(text_is_email($email)) {
                if(text_in_range($password, 6, 20)) {
                    $objs = User::where('username', $username)
                        ->orWhere('email', $email)
                        ->get();
                    if($objs->count() < 1) {
                        $obj = new User();
                        $obj->username = $username;
                        $obj->email = $email;
                        $obj->password = text_encrypt($password);
                        $ok_count = $obj->save();
                        if($ok_count) {
                            $ret = [
                                'error' => 0,
                                'desc' => '添加成功'
                            ];
                        }
                        else {
                            $ret = [
                                'error' => 2,
                                'desc' => '数据库操作失败'
                            ];
                        }
                    }
                    else {
                        $obj = $objs->first();
                        $error = '未知错误';
                        if($obj->username == $username) {
                            $error = '用户名已被使用';
                        }
                        else if($obj->email == $email) {
                            $error = '邮箱已被使用';
                        }
                        $ret = [
                            'error' => 1,
                            'desc' => $error
                        ];
                    }
                }
                else {
                    $ret = [
                        'error' => 5,
                        'desc' => '密码长度(长度: 2~20)验证失败'
                    ];
                }
            }
            else {
                $ret = [
                    'error' => 4,
                    'desc' => 'email格式验证失败'
                ];
            }
        }
        else {
            $ret = [
                'error' => 3,
                'desc' => '真实姓名长度(长度: 2~20)验证失败'
            ];
        }

        return $response->withJson($ret);
    }

    // 删除用户
    public function delete(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        // $path = array_get($params, 'path');
        $ret = [
            'error' => 0,
            'desc' => 'TODO'
        ];
        return $response->withJson($ret);
    }

    // 修改用户
    public function modify(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        $id = array_get($args, 'id');
        if(!isset($id)) {
            $id = array_get($params, 'id');
        }
        $username = array_get($params, 'username');
        $email = array_get($params, 'email');
        $password = array_get($params, 'password');
        $role = array_get($params, 'role');
        $ret = [];
        if(isset($id) && (!empty($username) || !empty($email) || !empty($password) || isset($role))) {
            if(text_is_digital($id)) {
                $current = User::get_by_id($id, true);
                if(is_array($current) && array_key_exists('id', $current)) {
                    if(text_is_digital($role)) {
                        if($role != $current['role']['id']) {
                            // 如果当前要修改的用户是特权用户
                            if($current['role']['manage'] && $current['role']['predefine']) {
                                // 查询特权用户数量
                                $p_c = User::predefine_count();
                                if($p_c < 2) {
                                    $ret = [
                                        'error' => 13,
                                        'desc' => '系统唯一的特权用户不允许修改角色'
                                    ];
                                }
                            }
                            if(empty($ret)) {
                                $role = intval($role);
                                $roles = Role::where('id', $role)->get();
                                if($roles->count() > 0) {
                                    $role_obj = $roles->first();
                                    if($role_obj->manage && $role_obj->predefine) {
                                        $logined_user = $request->getAttribute('logined_user');
                                        $predefine_op = ($logined_user['role']['manage'] && $logined_user['role']['predefine']);
                                        if(!$predefine_op) {
                                            $ret = [
                                                'error' => 14,
                                                'desc' => '权限不足, 只有特权用户才可以分配特权角色'
                                            ];
                                        }
                                    }
                                    if(empty($ret)) {
                                        if(is_null($current['role']['id'])) {
                                            $is_ok = User::new_set_role($id, $role);
                                            if(!$is_ok) {
                                                $ret = [
                                                    'error' => 11,
                                                    'desc' => '用户角色修改失败'
                                                ];
                                            }
                                        }
                                        else {
                                            $old_role = intval($current['role']['id']);
                                            $is_ok = User::update_role($id, $old_role, $role);
                                            if(!$is_ok) {
                                                $ret = [
                                                    'error' => 12,
                                                    'desc' => '设置用户角色失败'
                                                ];
                                            }
                                        }
                                    }
                                }
                                else {
                                    $ret = [
                                        'error' => 10,
                                        'desc' => '角色不存在'
                                    ];
                                }
                            }
                        }
                    }
                    $base_info = [];
                    if(empty($ret) && !empty($username)) {
                        if(text_in_range($username, 2, 20)) {
                            if($username != $current['username']) {
                                $base_info['username'] = $username;
                            }
                        }
                        else {
                            $ret = [
                                'error' => 4,
                                'desc' => '真实姓名长度(长度: 2~20)验证失败'
                            ];
                        }
                    }
                    if(empty($ret) && !empty($email)) {
                        if(text_is_email($email)) {
                            if($email != $current['email']) {
                                $base_info['email'] = $email;
                            }
                        }
                        else {
                            $ret = [
                                'error' => 5,
                                'desc' => 'email格式验证失败'
                            ];
                        }
                    }
                    if(empty($ret) && !empty($password)) {
                        if(text_in_range($password, 6, 20)) {
                            $password  = text_encrypt($password);
                            if($password != $current['password']) {
                                $base_info['password'] = $password;
                            }
                        }
                        else {
                            $ret = [
                                'error' => 6,
                                'desc' => '密码长度(长度: 2~20)验证失败'
                            ];
                        }
                    }
                    if(empty($ret)) {
                        if(!empty($base_info)) {
                            $exist_obj = User::where('id' , '<>', intval($id));
                            if(array_key_exists('email', $base_info)) {
                                $exist_count = $exist_obj->where('email', $base_info['email'])->count();
                                if($exist_count > 0) {
                                    $ret = [
                                        'error' => 7,
                                        'desc' => '邮箱已被使用'
                                    ];
                                }
                            }
                            if(empty($ret)) {
                                $exist_obj = User::where('id' , '<>', intval($id));
                                if(array_key_exists('username', $base_info)) {
                                    $exist_count = $exist_obj->where('username', $base_info['username'])->count();
                                    if($exist_count > 0) {
                                        $ret = [
                                            'error' => 8,
                                            'desc' => '用户名已被使用'
                                        ];
                                    }
                                }
                            }
                            if(empty($ret)) {
                                $is_ok = User::update_info($id, $base_info);
                                if(!$is_ok) {
                                    $ret = [
                                        'error' => 9,
                                        'desc' => '用户信息修改失败'
                                    ];
                                }
                            }
                        }
                    }
                    if(empty($ret)) {
                        $ret = [
                            'error' => 0,
                            'desc' => '操作成功'
                        ];
                    }
                }
                else {
                    $ret = [
                        'error' => 3,
                        'desc' => '用户不存在'
                    ];
                }
            }
            else {
                $ret = [
                    'error' => 2,
                    'desc' => '参数格式有误'
                ];
            }
        }
        else {
            $ret = [
                'error' => 1,
                'desc' => '缺少参数'
            ];
        }
        return $response->withJson($ret);
    }

    /**
     * 获取用户列表.
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function lists(Request $request,Response $response,$args = []){
        $param = $request->getParams();
        $id = $this->filter_param('id',$param,$args);
        if(empty($id)){
            // 这里是过滤的ID
            $user_info = $request->getAttribute('logined_user');
            $id = $user_info['id'];
        }
        $data = User::filter_user($id);
        if(empty($data)){
            $ret = [
                'error'=>1,
                'desc'=>'没有找到用户信息'
            ];
        }else{
            $ret = [
                'error'=>0,
                'desc'=>'获取完毕',
                'data'=>$data,
            ];
        }
        return $response->withJson($ret);
    }
}

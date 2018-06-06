<?php

namespace App\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use \Illuminate\Database\Capsule\Manager as DB;

use App\Models\Role;

/**
 * RoleController操作
 */
class RoleController extends ControllerBase {
    // 获取角色信息
    public function info(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        $id = array_get($args, 'id');
        if(!isset($id)) {
            $id = array_get($params, 'id');
        }
        $ret = [];
        if(text_is_digital($id)) {
            $data = Role::get_by_id($id);
            if(!is_array($data) || empty($data)) {
                $ret = [
                    'error' => 1,
                    'desc' => '角色不存在'
                ];
            }
        }
        else {
            $data = Role::get_all();
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

    // 添加角色
    public function add(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        $name = array_get($params, 'name');
        $cn_name = array_get($params, 'cn_name');
        $manage = array_get($params, 'manage', null);
        $manage = in_array($manage, ['true', '1']);

        if(preg_match('/^[a-z_]{2,36}$/i', $name)) {
            if(text_in_range($cn_name, 2, 20)) {
                $objs = Role::where('name', $name)
                    ->orWhere('cn_name', $cn_name)
                    ->get();
                if($objs->count() < 1) {
                    $obj = new Role();
                    $obj->name = $name;
                    $obj->cn_name = $cn_name;
                    $obj->manage = $manage;
                    $obj->predefine = false;
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
                    if($obj->name == $name) {
                        $error = '角色(英文名)已被使用';
                    }
                    else if($obj->cn_name == $cn_name) {
                        $error = '角色(中文名)不能相同';
                    }
                    $ret = [
                        'error' => 3,
                        'desc' => $error
                    ];
                }
            }
            else {
                $ret = [
                    'error' => 2,
                    'desc' => '角色(中文名)长度(长度: 2~20)验证失败'
                ];
            }
        }
        else {
            $ret = [
                'error' => 1,
                'desc' => '角色(英文名)格式(长度: 2~36, 只能包含大小写字母和下划线)验证失败'
            ];
        }

        return $response->withJson($ret);
    }

    // 删除角色
    public function delete(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        // $path = array_get($params, 'path');
        $ret = [
            'error' => 0,
            'desc' => 'TODO'
        ];
        return $response->withJson($ret);
    }

    // 修改角色信息
    public function modify(Request $request, Response $response, $args=[]) {
        $params = $request->getParams();
        $id = array_get($args, 'id');
        if(!isset($id)) {
            $id = array_get($params, 'id');
        }
        $name = array_get($params, 'name');
        $cn_name = array_get($params, 'cn_name');
        $manage = array_get($params, 'manage', null);
        $ret = [];
        if(isset($id) && (!empty($name) || !empty($cn_name) || !is_null($manage))) {
            if(text_is_digital($id)) {
                $current = Role::get_by_id($id);
                if(is_array($current) && array_key_exists('id', $current)) {
                    $role_info = [];
                    if(!is_null($manage)) {
                        $manage = in_array($manage, ['true', '1']);
                        // 如果当前要修改的是特权用户
                        if($current['manage'] && $current['predefine']) {
                            $ret = [
                                'error' => 5,
                                'desc' => '特权角色的后台管理功能不允许修改'
                            ];
                        }
                        else {
                            if($manage != $current['manage']) {
                                $role_info['manage'] = $manage;
                            }
                        }
                    }
                    if(empty($ret) && !empty($name)) {
                        if(preg_match('/^[a-z_]{2,36}$/i', $name)) {
                            if($name != $current['name']) {
                                $role_info['name'] = $name;
                            }
                        }
                        else {
                            $ret = [
                                'error' => 4,
                                'desc' => '角色(英文名)格式(长度: 2~36, 只能包含大小写字母和下划线)验证失败'
                            ];
                        }
                    }
                    if(empty($ret) && !empty($cn_name)) {
                        if(text_in_range($cn_name, 2, 20)) {
                            if($cn_name != $current['cn_name']) {
                                $role_info['cn_name'] = $cn_name;
                            }
                        }
                        else {
                            $ret = [
                                'error' => 5,
                                'desc' => '角色(中文名)长度(长度: 2~20)验证失败'
                            ];
                        }
                    }
                    if(empty($ret)) {
                        if(!empty($role_info)) {
                            $exist_obj = Role::where('id' , '<>', intval($id));
                            if(array_key_exists('email', $role_info)) {
                                $exist_count = $exist_obj->where('name', $role_info['name'])->count();
                                if($exist_count > 0) {
                                    $ret = [
                                        'error' => 7,
                                        'desc' => '角色(英文名)已被使用'
                                    ];
                                }
                            }
                            if(empty($ret)) {
                                $exist_obj = Role::where('id' , '<>', intval($id));
                                if(array_key_exists('cn_name', $role_info)) {
                                    $exist_count = $exist_obj->where('cn_name', $role_info['cn_name'])->count();
                                    if($exist_count > 0) {
                                        $ret = [
                                            'error' => 8,
                                            'desc' => '角色(中文名)不能相同'
                                        ];
                                    }
                                }
                            }
                            if(empty($ret)) {
                                $is_ok = Role::update_info($id, $role_info);
                                if(!$is_ok) {
                                    $ret = [
                                        'error' => 9,
                                        'desc' => '角色信息修改失败'
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
                        'desc' => '角色不存在'
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
}

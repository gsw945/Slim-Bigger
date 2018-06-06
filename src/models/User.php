<?php
namespace App\Models;

use \Illuminate\Database\Capsule\Manager as DB;

use \App\Models\Role;
use \App\Models\UserRoleMap;

/**
 * 用户　User
 */
class User extends Base
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'user';

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    // 根据 id 查询用户信息
    public static function get_by_id($id, $with_pwd=false) {
        $tb_user = static::table_name();
        $tb_role = Role::table_name();
        $tb_urm = UserRoleMap::table_name();
        $cursor = DB::table($tb_user)
            ->leftJoin($tb_urm,  "{$tb_user}.id", '=', "{$tb_urm}.user")
            ->leftJoin($tb_role,  "{$tb_role}.id", '=', "{$tb_urm}.role")
            ->where("{$tb_user}.id", $id)
            ->select(
                "{$tb_user}.id",
                "{$tb_user}.username",
                "{$tb_user}.email",
                "{$tb_user}.password",
                DB::raw("{$tb_role}.id AS 'role_id'"),
                DB::raw("{$tb_role}.name AS 'role_name'"),
                DB::raw("{$tb_role}.cn_name AS 'role_cn_name'"),
                DB::raw("{$tb_role}.manage AS 'role_manage'"),
                DB::raw("{$tb_role}.predefine AS 'role_predefine'")
            )
            ->get();
        if($cursor->count() > 0) {
            $obj = $cursor->first();
            $result = [
                'id' => $obj->id,
                'username' => $obj->username,
                'email' => $obj->email,
                'role' => [
                    'id' => $obj->role_id,
                    'name' => $obj->role_name,
                    'cn_name' => $obj->role_cn_name,
                    'manage' => $obj->role_manage,
                    'predefine' => $obj->role_predefine
                ]
            ];
            if($with_pwd) {
                $result['password'] = $obj->password;
            }
            return $result;
        }
        return null;
    }

    // 查询所有用户信息
    public static function get_all() {
        $tb_user = static::table_name();
        $tb_role = Role::table_name();
        $tb_urm = UserRoleMap::table_name();
        $cursor = DB::table($tb_user)
            ->leftJoin($tb_urm,  "{$tb_user}.id", '=', "{$tb_urm}.user")
            ->leftJoin($tb_role,  "{$tb_role}.id", '=', "{$tb_urm}.role")
            ->select(
                "{$tb_user}.id",
                "{$tb_user}.username",
                "{$tb_user}.email",
                DB::raw("{$tb_role}.id AS 'role_id'"),
                DB::raw("{$tb_role}.name AS 'role_name'"),
                DB::raw("{$tb_role}.cn_name AS 'role_cn_name'"),
                DB::raw("{$tb_role}.manage AS 'role_manage'"),
                DB::raw("{$tb_role}.predefine AS 'role_predefine'")
            )
            ->get();
        $result = [];
        foreach ($cursor as $item) {
            $result[] = [
                'id' => $item->id,
                'username' => $item->username,
                'email' => $item->email,
                'role' => [
                    'id' => $item->role_id,
                    'name' => $item->role_name,
                    'cn_name' => $item->role_cn_name,
                    'manage' => $item->role_manage,
                    'predefine' => $item->role_predefine
                ]
            ];
        }
        return $result;
    }

    /**
     * 过滤掉某些用户.
     * @param array $ids    id列表.
     * @return array
     */
    public static function filter_user($ids){
        if(is_numeric($ids)){
            $ids = [$ids];
        }
        $tb_user = static::table_name();
        $tb_role = Role::table_name();
        $tb_urm = UserRoleMap::table_name();
        $cursor = DB::table($tb_user)
            ->whereNotIn("{$tb_user}.id",$ids)
            ->leftJoin($tb_urm,  "{$tb_user}.id", '=', "{$tb_urm}.user")
            ->leftJoin($tb_role,  "{$tb_role}.id", '=', "{$tb_urm}.role")
            ->select(
                "{$tb_user}.id",
                "{$tb_user}.username",
                "{$tb_user}.email",
                DB::raw("{$tb_role}.id AS 'role_id'"),
                DB::raw("{$tb_role}.name AS 'role_name'"),
                DB::raw("{$tb_role}.cn_name AS 'role_cn_name'"),
                DB::raw("{$tb_role}.manage AS 'role_manage'"),
                DB::raw("{$tb_role}.predefine AS 'role_predefine'")
            )
            ->get();
        $result = [];
        foreach ($cursor as $item) {
            $result[] = [
                'id' => $item->id,
                'username' => $item->username,
                'email' => $item->email,
                'role' => [
                    'id' => $item->role_id,
                    'name' => $item->role_name,
                    'cn_name' => $item->role_cn_name,
                    'manage' => $item->role_manage,
                    'predefine' => $item->role_predefine
                ]
            ];
        }
        return $result;
    }

    // 更新用户基本信息
    public static function update_info($id, $data) {
        $tb_user = static::table_name();
        $effet = DB::table($tb_user)->where('id', $id)->update($data);
        return $effet > 0;
    }

    // 新设置用户角色关联
    public static function new_set_role($id, $role_id) {
        $urm_obj = new UserRoleMap();
        $urm_obj->user = $id;
        $urm_obj->role = $role_id;
        $effet = $urm_obj->save();
        return $effet > 0;
    }

    // 更新用户角色关联
    public static function update_role($id, $old_role, $new_role) {
        $tb_urm = UserRoleMap::table_name();
        $effet = DB::table($tb_urm)
            ->where('user', $id)
            ->where('role', $old_role)
            ->update([
                'role' => $new_role
            ]);
        return $effet > 0;
    }

    // 获取特权角色用户数量
    public static function predefine_count() {
        $tb_user = static::table_name();
        $tb_role = Role::table_name();
        $tb_urm = UserRoleMap::table_name();
        return DB::table($tb_user)
            ->leftJoin($tb_urm,  "{$tb_user}.id", '=', "{$tb_urm}.user")
            ->leftJoin($tb_role,  "{$tb_role}.id", '=', "{$tb_urm}.role")
            ->where("{$tb_role}.manage", true)
            ->where("{$tb_role}.predefine", true)
            ->count();
    }
}
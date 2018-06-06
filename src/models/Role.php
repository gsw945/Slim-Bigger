<?php
namespace App\Models;

use \Illuminate\Database\Capsule\Manager as DB;

/**
 * 角色　Model
 */
class Role extends Base
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'role';

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    // 根据 id 查询角色信息
    public static function get_by_id($id) {
        $tb_name = static::table_name();
        $cursor = DB::table($tb_name)->where('id', $id);
        if($cursor->count() > 0) {
            $obj = $cursor->first();
            return [
                'id' => $obj->id,
                'name' => $obj->name,
                'cn_name' => $obj->cn_name,
                'manage' => $obj->manage,
                'predefine' => $obj->predefine
            ];
        }
        return null;
    }

    // 查询所有角色信息
    public static function get_all() {
        $cursor = static::all();
        $result = [];
        foreach ($cursor as $item) {
            // predefine 为系统保留，查询不予显示
            $result[] = [
                'id' => $item->id,
                'name' => $item->name,
                'cn_name' => $item->cn_name,
                'manage' => $item->manage,
                'predefine' => $item->predefine
            ];
        }
        return $result;
    }

    // 更新角色信息
    public static function update_info($id, $data) {
        $tb_role = static::table_name();
        $effet = DB::table($tb_role)->where('id', $id)->update($data);
        return $effet > 0;
    }
}
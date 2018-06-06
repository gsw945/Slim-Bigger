<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Base
 */
class Base extends Model
{
    /**
     * 获取表名
     */
    public static function table_name() {
        return with(new static())->getTable();
    }
}
<?php
namespace App\Models;

/**
 * 用户-角色关联　Model
 */
class UserRoleMap extends Base
{
    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'user_role_map';

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;
}
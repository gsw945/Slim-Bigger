<?php
namespace App\Migrations;

/**
 * 用户-角色关联表 Migration
 */
class UserRoleMap extends Base
{
    public function up()
    {
        $this->schema->create(
            $this->table_name,
            function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id')->comment('主键ID');
                $table->integer('user')->unsigned()->nullable()->comment('用户.ID');
                $table->integer('role')->unsigned()->nullable()->comment('角色.ID');

                $table->unique(['user', 'role'], 'user_role');
                $table->foreign('user')->references('id')->on('user');
                $table->foreign('role')->references('id')->on('role');
            }
        );
        $this->table_comment('用户-角色关联表');
    }
}
<?php
namespace App\Migrations;

/**
 * 用户表 Migration
 */
class User extends Base
{
    public function up()
    {
        $this->schema->create(
            $this->table_name,
            function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id')->comment('主键ID');
                $table->string('username', 256)->comment('用户名');
                $table->string('email', 256)->comment('邮箱');
                $table->string('password', 32)->comment('密码');

                $table->unique('username');
                $table->unique('email');
            }
        );
        $this->table_comment('用户表');
    }
}
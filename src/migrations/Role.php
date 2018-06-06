<?php
namespace App\Migrations;

/**
 * 角色表 Migration
 */
class Role extends Base
{
    public function up()
    {
        $this->schema->create(
            $this->table_name,
            function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->increments('id')->comment('主键ID');
                $table->string('name', 256)->comment('角色名(英文)');
                $table->string('cn_name', 256)->comment('角色名(中文)');
                $table->boolean('manage')->default(false)->comment('是否可以访问管理后台');
                $table->boolean('predefine')->default(false)->comment('是否是预定义的(修改有限制)');

                $table->unique('name');
                $table->unique('cn_name');
            }
        );
        $this->table_comment('角色表');
    }
}
<?php

namespace App\Migrations;

use \Illuminate\Database\Capsule\Manager as DB;
use \Illuminate\Database\Schema\Blueprint;

/**
* Base Migration
*/
class Base extends \Illuminate\Database\Migrations\Migration
{
    # https://laravel.com/docs/5.3/migrations
    public function __construct($table_name, $schema)
    {
        $this->schema = $schema;
        $this->table_name = $table_name;
    }

    public function exists()
    {
        return $this->schema->hasTable($this->table_name);
    }

    public function down()
    {
        $this->schema->drop($this->table_name);
    }

    public function table_comment($comment_str)
    {
        $comment_value = var_export($comment_str, true);
        DB::statement("ALTER TABLE `{$this->table_name}` COMMENT = {$comment_value}");
    }

    public function index_up($column_name)
    {
        # https://laravel.com/docs/5.6/migrations#indexes
        $this->schema->table($this->table_name, function(Blueprint $table) use($column_name) {
            $table->index($column_name);
        });
    }

    public function index_down($column_name)
    {
        $this->schema->table($this->table_name, function(Blueprint $table) use($column_name) {
            $table->dropIndex([$column_name]);
        });
    }
}
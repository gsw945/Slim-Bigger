<?php
namespace App\Dev\Init;

/**
 * DBMigration
 */
class DBMigration extends \App\Controllers\ControllerBase
{

    public static function get_tables() {
        return [
            // 微信用户记录，微信ID关联
            'wechat_user' => '\App\Migrations\WeChatUser',
            'wechat_id_map' => '\App\Migrations\WeChatIDMap',
            // 权限
            'operation_group' => '\App\Migrations\OperationGroup',
            'operation' => '\App\Migrations\Operation',
            'operation_group_map' => '\App\Migrations\OperationGroupMap',
            // 角色，角色-权限
            'role' => '\App\Migrations\Role',
            'operation_role_map' => '\App\Migrations\OperationRoleMap',
            // 用户、用户-角色
            'user' => '\App\Migrations\User',
            'user_role_map' => '\App\Migrations\UserRoleMap',
            // 桌台二维码
            'desk_qrcode' => '\App\Migrations\DeskQRCode',
            // 商户、门店
            'merchant_object' => '\App\Migrations\MerchantObject',
            'store' => '\App\Migrations\Store',
            // 门店提供的顾客服务
            'store_service' => '\App\Migrations\StoreService',
            // 门店员工
            'store_staff' => '\App\Migrations\StoreStaff',
            // 桌台分类、桌台
            'desk_category' => '\App\Migrations\DeskCategory',
            'desk' => '\App\Migrations\Desk',
            // 菜品分类、菜品-菜品分类
            'cuisine_category' => '\App\Migrations\CuisineCategory',
            'cuisine' => '\App\Migrations\Cuisine',
            'cuisine_category_map' => '\App\Migrations\CuisineCategoryMap',
            'cuisine_dimension' => '\App\Migrations\CuisineDimension',
            // 排号
            'queue' => '\App\Migrations\Queue',
            // 订单、订单详细
            'order' => '\App\Migrations\Order',
            'order_cuisine_map' => '\App\Migrations\OrderCuisineMap',
            // 顾客服务请求
            'service_request' => '\App\Migrations\ServiceRequest',
        ];
    }

    // 根据表名获取对象
    protected function get_object($table_name, $db)
    {
        $obj = null;
        if (empty($table_name)) {
            $obj = 'parameter [table] not provided';
        } else {
            $tables = static::get_tables();
            if(array_key_exists($table_name, $tables)) {
                $table_class = array_get($tables, $table_name);
                $obj = new $table_class($table_name, $db->schema());
            }
            else {
                $obj = sprintf('table [%s] not supportted', $table_name);
            }
        }

        return $obj;
    }

    // 创建表
    public function up(\Slim\Http\Request $request, \Slim\Http\Response $response, $args = [])
    {
        $table_name = array_get($args, 'table', null);
        if(empty($table_name)) {
            $params = $request->getParams();
            $table_name = array_get($params, 'table');
        }
        $db = $this->ci->get('db');
        $obj = $this->get_object($table_name, $db);
        if ($obj instanceof \App\Migrations\Base) {
            if (!$obj->exists()) {
                $obj->up();
                $response->getBody()->write(sprintf('create table [%s] ok', $table_name));
            } else {
                $response->getBody()->write(sprintf('table [%s] existed', $table_name));
            }
        } else {
            $response->getBody()->write($obj);
        }

        return $response;
    }

    // 销毁表
    public function down(\Slim\Http\Request $request, \Slim\Http\Response $response, $args = [])
    {
        $table_name = array_get($args, 'table', null);
        if(empty($table_name)) {
            $params = $request->getParams();
            $table_name = array_get($params, 'table');
        }
        $db = $this->ci->get('db');
        $obj = $this->get_object($table_name, $db);
        if ($obj instanceof \App\Migrations\Base) {
            if ($obj->exists()) {
                $obj->down();
                $response->getBody()->write(sprintf('drop table [%s] ok', $table_name));
            } else {
                $response->getBody()->write(sprintf('table [%s] not exists', $table_name));
            }
        } else {
            $response->getBody()->write($obj);
        }

        return $response;
    }
}
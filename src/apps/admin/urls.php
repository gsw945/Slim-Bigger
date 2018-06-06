<?php

return [
    '[/]' => [
        'get' => [
            'handler' => 'App\Views\AdminView:index',
            'name' => 'admin_index',
            'auth' => true,
            'op_name' => '访问"管理后台-统计概况"'
        ],
    ],
    '/user_list[/]' => [
        'get' => [
            'handler' => 'App\Views\AdminView:user_list',
            'name' => 'admin_user_list',
            'auth' => true,
            'op_name' => '访问"管理后台-用户列表"'
        ],
    ],
    '/role_list[/]' => [
        'get' => [
            'handler' => 'App\Views\AdminView:role_list',
            'name' => 'admin_role_list',
            'auth' => true,
            'op_name' => '访问"管理后台-角色列表"'
        ],
    ],
    '/permission_assign[/]' => [
        'get' => [
            'handler' => 'App\Views\AdminView:permission_assign',
            'name' => 'admin_permission_assign',
            'auth' => true,
            'op_name' => '访问"管理后台-权限分配"'
        ],
    ],
    '/merchant_list[/]' => [
        'get' => [
            'handler' => 'App\Views\AdminView:merchant_list',
            'name' => 'admin_merchant_list',
            'auth' => true,
            'op_name' => '访问"管理后台-商户列表"'
        ],
    ],
];
<?php
// Routes

$sub_apps = require __DIR__ . '/apps/entry.php';

$container->get('globals')->set('innerList', []);
$container->get('globals')->set('authList', []);
$container->get('globals')->set('tokenRouteList', []);
$container->get('globals')->set('opList', []);
$container->get('globals')->set('opgList', []);

$settings = $container->get('settings');
if($settings['debug']) {
    $container->get('globals')->set('opList', []);
    if($settings['tracy_debug']) {
        $app->post('/console', 'RunTracy\Controllers\RunTracyConsole:index');
    }
}

$i = 0;
foreach ($sub_apps as $key => $sub_app) {
    $prefix = $sub_app['prefix'];
    $urls = $sub_app['urls'];
    foreach ($urls as $url => $action) {
        foreach ($action as $method => $content) {
            $handler = $content['handler'];
            $route = $prefix . $url;
            $id = null;
            switch (strtolower($method)) {
                case 'get':
                    $id = $app->get($route, $handler);
                    break;
                case 'post':
                    $id = $app->post($route, $handler);
                    break;
                case 'put':
                    $id = $app->put($route, $handler);
                    break;
                case 'delete':
                    $id = $app->delete($route, $handler);
                    break;
                case 'head':
                    $id = $app->head($route, $handler);
                    break;
                case 'patch':
                    $id = $app->patch($route, $handler);
                    break;
                case 'options':
                    $id = $app->options($route, $handler);
                    break;
                # --------------------------------------------
                case 'any':
                    $id = $app->any($route, $handler);
                    break;
                case 'map':
                    if(array_key_exists('methods', $content)) {
                        $methods = $content['methods'];
                        if(!empty($methods)) {
                            $methods = array_map('strtoupper', $methods);
                            $id = $app->map($methods, $route, $handler);
                        }
                        else {
                            echo 'map method need methods with not empty';
                        }
                    }
                    else {
                        echo 'map method need methods';
                    }
                    break;
                default:
                    # code...
                    echo 'http request method not support';
                    break;
            }
            if(isset($id)) {
                $name = null;
                if(array_key_exists('name', $content)) {
                    $name = $content['name'];
                }
                if(!isset($name)) {
                    $name = 'route' . $i;
                }
                $id->setName($name);
                if(array_key_exists('inner', $content)) {
                    $inner = $content['inner'];
                    if($inner === true) {
                        $container->get('globals')->item_push('innerList', $name);
                    }
                }
                if(array_key_exists('auth', $content)) {
                    $auth = $content['auth'];
                    if($auth === true) {
                        $container->get('globals')->item_push('authList', $name);
                    }
                }
                if(array_key_exists('token', $content)) {
                    $need_token = $content['token'];
                    if($need_token === true) {
                        $container->get('globals')->item_push('tokenRouteList', $name);
                    }
                }
                if($settings['debug'] && array_key_exists('op_name', $content)) {
                    $op_item = [
                        'key' => $name,
                        'name' => $content['op_name'],
                        'op_group'=>isset($content['op_group']) ? $content['op_group'] : null
                    ];
                    $container->get('globals')->item_push('opList', $op_item);
                }
                if($settings['debug'] && array_key_exists('op_group',$content)){
                    $name = $content['op_group'];
                    $op_item = [
                        'key'=>$name,
                        'name'=>$content['op_group_name']
                    ];
                    $container->get('globals')->item_push('opgList',$op_item);
                }
            }
            $i += 1;
        }
    }
}
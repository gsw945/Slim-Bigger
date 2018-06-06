<?php
// Application middleware

$settings = $container->get('settings');
$app->add(new \App\Middlewares\TemplateAssign($app));  // 5
$app->add(new \App\Middlewares\TwigHelper($app));  // 4
$cli_no_auth = defined('CLI_NO_AUTH');
if(!$cli_no_auth) {
    $app->add(new \App\Middlewares\TokenCall($app));  // 3.2
    $app->add(new \App\Middlewares\InnerCall($app));  // 3.1
    $app->add(new \App\Middlewares\Auth($app));  // 2
}

if($settings['cors']) {
    $app->add(new \App\Middlewares\CrossDomain($app));  // 1.4
}
if($settings['debug']) {
    if($settings['tracy_debug']) {
        $app->add(new RunTracy\Middlewares\TracyMiddleware($app)); // 1.3
    }
    $cli_db_init = defined('CLI_DB_INIT');
    if(!$cli_db_init) {
        $app->add(new \App\Middlewares\DevDataInit($app));  // 1.2
        $app->add(new \App\Middlewares\DevMerge($app));  // 1.1
    }
}
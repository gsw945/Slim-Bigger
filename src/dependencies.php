<?php
use \Zend\Permissions\Acl\Acl;

// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($container) {
    $settings = $container->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};
$container['twig_profile'] = function () {
    return new Twig_Profiler_Profile();
};
$container['twig'] = function($container) {
    $config = $container->get('settings');
    $view = new \Slim\Views\Twig(
        $config['twig']['template_path'],
        $config['twig']['twig']
    );
    $basePath = base_path($container['request']);
    $view->addExtension(
        new \Slim\Views\TwigExtension($container['router'], $basePath)
    );
    if($container['settings']['debug'] && $container['settings']['tracy_debug']) {
        $view->addExtension(new Twig_Extension_Profiler($container['twig_profile']));
        $view->addExtension(new Twig_Extension_Debug());
    }
    return $view;
};

// monolog
$container['logger'] = function($container) {
    $settings = $container->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

$container['flash'] = function($container) {
    $view = $container->get('twig');
    $view->addExtension(
        new \Knlv\Slim\Views\TwigMessages(new Slim\Flash\Messages())
    );
    return new \Slim\Flash\Messages();
};

// Service factory for the Eloquent ORM
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    if($container['settings']['debug'] && $container['settings']['tracy_debug']) {
        $capsule::connection()->enableQueryLog();
    }

    $pdo = $capsule->getConnection()->getPdo();
    $tz = (new \DateTime('now', new \DateTimeZone('Asia/Shanghai')))->format('P');
    $pdo->exec("SET time_zone='$tz';");
    
    $container['pdo'] = $pdo;

    return $capsule;
};

// 全局变量读取器
$container['globals'] = function($container) {
    return new \GlobalVars();
};

// 配置文件存储
$container['file_store'] = function($container) {
    // fix php pathinfo() utf-8 bug
    // setlocale(LC_ALL, 'zh_CN.UTF-8');
    $store_cfg = $container['settings']['store'];
    $use_store = $container['settings']['use_store'];
    $store_detail = array_get($store_cfg, $use_store);
    if(is_array($store_detail)) {
        // aws3/minio client
        $client = new Aws\S3\S3Client([
            'credentials' => [
                'key' => $store_detail['key'],
                'secret' => $store_detail['secret']
            ],
            'region' => $store_detail['region'],
            'endpoint' => $store_detail['endpoint'],
            'version' => 'latest',
            'bucket_endpoint' => false,
            'use_path_style_endpoint' => true
        ]);
        $options = [
            'override_visibility_on_copy' => true
        ];
        $bucket = $store_detail['bucket'];
        $prefix = '';
        // Create the adapter
        $adapter = new League\Flysystem\AwsS3v3\AwsS3Adapter($client, $bucket, $prefix, $options);
        // And use that to create the file system
        $filesystem = new League\Flysystem\Filesystem($adapter);
        // $filesystem->addPlugin(new League\Flysystem\Plugin\ListPaths());
        return $filesystem;
    }
    
    throw new Exception("settings.store or settings.use_store 配置有误", 1);
    
};
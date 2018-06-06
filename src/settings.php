<?php
$twig_cache_dir = PROJ_BASE_DIR . '/cache/twig';
$logs_dir = PROJ_BASE_DIR . '/logs';

$debug = true;
$tracy_debug = true;
$mode = 'production';

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('DIR') || define('DIR', PROJ_BASE_DIR . DS);

if($debug) {
    $mode = 'development';
    if(!is_dir($twig_cache_dir)) {
        mkdir($twig_cache_dir, null, true);
    }
    if(!is_dir($logs_dir)) {
        mkdir($logs_dir, null, true);
    }
    clearstatcache();
    if($tracy_debug) {
        Tracy\Debugger::enable(Tracy\Debugger::DEVELOPMENT, DIR . 'logs');
        // Tracy\Debugger::enable(Tracy\Debugger::PRODUCTION, DIR . 'logs');
    }
}
return [
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true, # https://github.com/slimphp/Slim/issues/1505
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // !!$tracy_debug, // Allow the web server to send the content-length header

        // debug
        'debug' => $debug,
        'mode' => $mode,
        'tracy_debug' => $tracy_debug,
        'tracy' => [
            'showPhpInfoPanel' => 0,
            'showSlimRouterPanel' => 0,
            'showSlimEnvironmentPanel' => 0,
            'showSlimRequestPanel' => 1,
            'showSlimResponsePanel' => 1,
            'showSlimContainer' => 0,
            'showEloquentORMPanel' => 0,
            'showTwigPanel' => 0,
            'showIdiormPanel' => 0,// > 0 mean you enable logging
            // but show or not panel you decide in browser in panel selector
            'showDoctrinePanel' => 'em',// here also enable logging and you must enter your Doctrine container name
            // and also as above show or not panel you decide in browser in panel selector
            'showProfilerPanel' => 0,
            'showVendorVersionsPanel' => 0,
            'showXDebugHelper' => 0,
            'showIncludedFiles' => 0,
            'showConsolePanel' => 0,
            'configs' => [
                // XDebugger IDE key
                'XDebugHelperIDEKey' => 'PHPSTORM',
                // Disable login (don't ask for credentials, be careful) values( 1 || 0 )
                'ConsoleNoLogin' => false,
                // Multi-user credentials values( ['user1' => 'password1', 'user2' => 'password2'] )
                'ConsoleAccounts' => [
                    'dev' => '34c6fceca75e456f25e7e99531e2425c6c1de443'// = sha1('dev')
                ],
                // Password hash algorithm (password must be hashed) values('md5', 'sha256' ...)
                'ConsoleHashAlgorithm' => 'sha1',
                // Home directory (multi-user mode supported) values ( var || array )
                // '' || '/tmp' || ['user1' => '/home/user1', 'user2' => '/home/user2']
                'ConsoleHomeDirectory' => DIR,
                // terminal.js full URI
                'ConsoleTerminalJs' => '/assets/tracy/js/jquery.terminal.min.js',
                // terminal.css full URI
                'ConsoleTerminalCss' => '/assets/tracy/css/jquery.terminal.min.css',
                'ProfilerPanel' => [
                    // Memory usage 'primaryValue' set as Profiler::enable() or Profiler::enable(1)
                    // 'primaryValue' => 'effective',    // or 'absolute'
                    'show' => [
                        'memoryUsageChart' => 1, // or false
                        'shortProfiles' => true, // or false
                        'timeLines' => true // or false
                    ]
                ]
            ]
        ],

        // CORS 
        'cors' => true,

        // Renderer settings
        'twig' => [
            'template_path' => PROJ_BASE_DIR . '/templates',
            'twig' => [
                'cache' => $twig_cache_dir,
                'debug' => true,
                'auto_reload' => true
            ]
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => $logs_dir . '/app_' . date('Y-m', time()) . '.log',
            'level' => \Monolog\Logger::DEBUG,
        ],

        // Eloquent
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => '<your mysql database name>',
            'username' => 'root',
            'password' => '<your mysql password of root>',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix'    => '',
        ],

        // public path
        'public_path' => PROJ_BASE_DIR . '/public/',

        // secret key
        'secret' => 'secret string for slim app',
    ],
];

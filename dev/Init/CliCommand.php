<?php
namespace App\Dev\Init;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;

/**
 * CliCommand
 * @see https://getcomposer.org/doc/articles/scripts.md#defining-scripts
 */
class CliCommand
{

    /**
     * Process the application given a request method and URI
     *
     * @param string $requestMethod the request method (e.g. GET, POST, etc.)
     * @param string $requestUri the request URI
     * @param array|object|null $requestData the request data
     * @return \Slim\Http\Response
     */
    protected static function runApp($requestMethod, $requestUri, $requestData = null)
    {
        defined('PROJ_BASE_DIR') || define('PROJ_BASE_DIR', realpath(dirname(dirname(__DIR__))));

        // Use middleware when running application?
        $withMiddleware = true;

        require PROJ_BASE_DIR . '/src/set_env.php';
        require PROJ_BASE_DIR . '/vendor/autoload.php';

        // Create a mock environment for testing with
        $environment = Environment::mock(
            [
                'REQUEST_METHOD' => $requestMethod,
                'REQUEST_URI' => $requestUri
            ]
        );

        // Set up a request object based on the environment
        $request = Request::createFromEnvironment($environment);

        // Add request data, if it exists
        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        // Set up a response object
        $response = new Response();

        // Use the application settings
        $settings = require PROJ_BASE_DIR . '/src/settings.php';

        // Instantiate the application
        $app = new App($settings);

        // Set up dependencies
        require PROJ_BASE_DIR . '/src/dependencies.php';

        // Register middleware
        if ($withMiddleware) {
            require PROJ_BASE_DIR . '/src/middleware.php';
        }

        // Register routes
        require PROJ_BASE_DIR . '/src/routes.php';

        // Process the application
        $response = $app->process($request, $response);

        // Return the response
        return $response;
    }

    public static function InitDB(\Composer\Script\Event $event) {
        // $composer = $event->getComposer();
        // $vendorDir = $composer->getConfig()->get('vendor-dir');
        // 如果是命令行模式
        if(PHP_SAPI === 'cli') {
            // 不加载权限中间件
            defined('CLI_NO_AUTH') || define('CLI_NO_AUTH', true);
            // 表还不存在，所以不加载表数据初始化中间件
            defined('CLI_DB_INIT') || define('CLI_DB_INIT', true);
        }
        $tables = DBMigration::get_tables();
        foreach ($tables as $table_name => $table_class) {
            // var_dump($table_name);
            // var_dump($table_class);

            $params = [];
            $url = '/db/up/' . $table_name;
            $response = static::runApp('GET', $url, $params);
            $code = $response->getStatusCode();
            $content = (string)$response->getBody();

            echo $url;
            echo \PHP_EOL . "\t";
            echo $code;
            echo ' => ';
            echo $content;
            echo \PHP_EOL;
        }

    }
}
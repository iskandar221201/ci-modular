<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Setup how the exception handler works.
 */
class Exceptions extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * LOG EXCEPTIONS?
     * --------------------------------------------------------------------------
     * If true, then exceptions will be logged
     * through Services::Log.
     *
     * Default: true
     */
    public bool $log = true;

    /**
     * --------------------------------------------------------------------------
     * DO NOT LOG STATUS CODES
     * --------------------------------------------------------------------------
     * Any status codes here will NOT be logged if logging is turned on.
     * By default, only 404 (Page Not Found) exceptions are ignored.
     *
     * @var list<int>
     */
    public array $ignoreCodes = [404];

    /**
     * --------------------------------------------------------------------------
     * Error Views Path
     * --------------------------------------------------------------------------
     * This is the path to the directory that contains the 'cli' and 'html'
     * directories that hold the views used to generate errors.
     *
     * Default: APPPATH.'Views/errors'
     */
    public string $errorViewPath = APPPATH . 'Views/errors';

    /**
     * --------------------------------------------------------------------------
     * HIDE FROM DEBUG TRACE
     * --------------------------------------------------------------------------
     * Any data that you would like to hide from the debug trace.
     * In order to specify 2 levels, use "/" to separate.
     * ex. ['server', 'setup/password', 'secret_token']
     *
     * @var list<string>
     */
    public array $sensitiveDataInTrace = [];

    /**
     * --------------------------------------------------------------------------
     * WHETHER TO THROW AN EXCEPTION ON DEPRECATED ERRORS
     * --------------------------------------------------------------------------
     * If set to `true`, DEPRECATED errors are only logged and no exceptions are
     * thrown. This option also works for user deprecations.
     */
    public bool $logDeprecations = true;

    /**
     * --------------------------------------------------------------------------
     * LOG LEVEL THRESHOLD FOR DEPRECATIONS
     * --------------------------------------------------------------------------
     * If `$logDeprecations` is set to `true`, this sets the log level
     * to which the deprecation will be logged. This should be one of the log
     * levels recognized by PSR-3.
     *
     * The related `Config\Logger::$threshold` should be adjusted, if needed,
     * to capture logging the deprecations.
     */
    public string $deprecationLogLevel = LogLevel::WARNING;

    /*
     * DEFINE THE HANDLERS USED
     * --------------------------------------------------------------------------
     * Given the HTTP status code, returns exception handler that
     * should be used to deal with this error. By default, it will run CodeIgniter's
     * default handler and display the error information in the expected format
     * for CLI, HTTP, or AJAX requests, as determined by is_cli() and the expected
     * response format.
     *
     * Custom handlers can be returned if you want to handle one or more specific
     * error codes yourself like:
     *
     *      if (in_array($statusCode, [400, 404, 500])) {
     *          return new \App\Libraries\MyExceptionHandler();
     *      }
     *      if ($exception instanceOf PageNotFoundException) {
     *          return new \App\Libraries\MyExceptionHandler();
     *      }
     */
    public function handler(int $statusCode, Throwable $exception): ExceptionHandlerInterface
    {
        $request = \Config\Services::request();
        
        // CI4 request getPath() might be deprecated in some versions, but following spec exactly
        $path = $request->getPath();

        \App\Libraries\AppLogger::error('exception.uncaught', ['path' => $path, 'method' => $request->getMethod()], $exception);

        if (str_starts_with($path, 'api/') || str_starts_with($path, '/api/')) {
            $statusCode = $statusCode > 0 ? $statusCode : 500;
            
            $body = [
                'status'  => false,
                'code'    => $statusCode,
                'message' => ENVIRONMENT === 'production' ? 'Internal Server Error' : $exception->getMessage(),
                'errors'  => null,
            ];

            if (ENVIRONMENT !== 'production') {
                $body['debug'] = [
                    'exception' => get_class($exception),
                    'message'   => $exception->getMessage(),
                    'file'      => $exception->getFile(),
                    'line'      => $exception->getLine(),
                ];
            }

            $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                $json = '{"status":false,"code":500,"message":"Internal Server Error","errors":null}';
            }

            $response = \Config\Services::response();

            $allowedOrigin = env('CORS_ALLOWED_ORIGINS', '*');

            $response->setStatusCode($statusCode)
                     ->setHeader('Content-Type', 'application/json')
                     ->setHeader('Access-Control-Allow-Origin', $allowedOrigin)
                     ->setHeader('Access-Control-Allow-Methods', env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS'))
                     ->setHeader('Access-Control-Allow-Headers', env('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization,X-Requested-With'))
                     ->setBody($json);

            // Required when origin is not wildcard and credentials (cookies/tokens) are used
            if ($allowedOrigin !== '*') {
                $response->setHeader('Access-Control-Allow-Credentials', 'true');
            }

            $response->send();

            return new \App\Libraries\VoidExceptionHandler();
        }

        return new ExceptionHandler($this);
    }
}

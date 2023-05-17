<?php
namespace Vanier\Api\Middleware;

use DateTime;
use DateTimeZone;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware that checks if the HTTP method used is allowed for the requested resource.
 */
class LoggingMiddleware implements MiddlewareInterface
{
    /**
     * Checks if the HTTP method used is allowed for the requested resource.
     *
     * @param Request $request The request object.
     * @param RequestHandler $handler The handler for the request.
     * 
     * @return ResponseInterface The response object.
     */
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {   
        $logger = new Logger("access_log");
        $logger->setTimezone(new DateTimeZone('America/Toronto'));
        $log_handler = new StreamHandler(APP_LOG_DIR.'access.log', Logger::DEBUG);
        $logger->pushHandler($log_handler);

        $db_logger = new Logger('database_logs');
        $db_logger->setTimezone(new DateTimeZone('America/Toronto'));
        $db_logger->pushHandler($log_handler);
        $db_logger->error("This query failed ...");

        $params = $request->getQueryParams();
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $logger->info('Access: '. $request->getMethod() .' IP: '. $ip_address . ' '. $request->getUri()->getPath(), $params);
        $response = $handler->handle($request);
        return $response;
    }
}
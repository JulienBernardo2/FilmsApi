<?php
namespace Vanier\Api\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Vanier\Api\Exceptions\HttpNotAcceptableException;

/**
 * Make sure the content type is supported by this Api server
 */
class ContentNegotiationMiddleware implements MiddlewareInterface
{
    /**
     * Check the content type of the incoming request
     * @param Request $request The request object
     * @param RequestHandler $handler The response object
     * @throws HttpNotAcceptableException When the content type from the request is not allowed
     * @return ResponseInterface 
     */
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {   
        $accept = $request->getHeaderLine("Accept");

        if(!str_contains(APP_MEDIA_TYPE_JSON, $accept))
        {   
            throw new HttpNotAcceptableException($request);
        }
        $response = $handler->handle($request);
        return $response;
    }
}
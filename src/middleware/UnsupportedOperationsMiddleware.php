<?php
namespace Vanier\Api\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware that checks if the HTTP method used is allowed for the requested resource.
 */
class UnsupportedOperationsMiddleware implements MiddlewareInterface
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
        //Gets the method from the request
        $method = $request->getMethod();
        
        //All methods that are allowed on each resource
        $allowed_methods = ['films'=> ['GET', 'POST', 'PUT', 'DELETE'],
                            'customers' => ['GET', 'PUT', 'DELETE'],
                            'categories' => ['GET'],
                            'actors' => ['GET', 'POST']
                            ];

        //Gets the uri path
        $resource = $request->getUri()->getPath();

        //Separated each string by the / character
        $uri_parts = explode("/", $resource);

        //Gets the resource that is intended for this method
        $resource = $uri_parts[2];
        
        //If the method is not in the allowed resources than an error is thrown
        if(!in_array($method, $allowed_methods[$resource]))
        {
            $response= new \Slim\Psr7\Response();
            $error_data = ['status code' => '405' , 'message' => 'Unsupported Method' , 'description' => 'Method not allowed. Must be one of the following methods ' . implode(",", $allowed_methods[$resource])];
            $response->getBody()->write(json_encode($error_data));
            $response = $response->withStatus(405);
            return $response;
        }

        $response = $handler->handle($request);        
        return $response;
    }
}
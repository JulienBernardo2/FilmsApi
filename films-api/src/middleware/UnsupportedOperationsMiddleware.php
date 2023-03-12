<?php
namespace Vanier\Api\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;

class UnsupportedOperationsMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {   
        $method = $request->getMethod();
        
        $allowed_methods = ['films'=> ['GET', 'POST', 'PUT', 'DELETE'],
                            'customers' => ['GET', 'PUT', 'DELETE'],
                            'categories' => ['GET'],
                            'actors' => ['GET', 'POST']
                            ];

        $resource = $request->getUri()->getPath();
        $uri_parts = explode("/", $resource);
        $resource = $uri_parts[2];
        
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
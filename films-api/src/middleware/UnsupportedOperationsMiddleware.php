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
        $response = $handler->handle($request);
        
        if($response->getStatusCode() == "405")
        {
            $accepted_methods = $response->getHeaderLine("Allow");
            
            $response= new \Slim\Psr7\Response();
            $error_data = ['status code' => '405' , 'message' => 'Unsupported Method' , 'description' => 'Method not allowed. Must be one of the following methods ' . $accepted_methods];
            $response->getBody()->write(json_encode($error_data));
            $response = $response->withStatus(405);
            return $response;
        }
        
        return $response;
    }
}
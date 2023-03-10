<?php
namespace Vanier\Api\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Vanier\Api\Exceptions\HttpNotAcceptableException;

class ContentNegotiationMiddleware implements MiddlewareInterface
{
    //
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {   
        //echo "Hello.... ";exit;

        $accept = $request->getHeaderLine("Accept");

        if(!str_contains(APP_MEDIA_TYPE_JSON, $accept))
        {   
            throw new HttpNotAcceptableException($request);
            // $response= new \Slim\Psr7\Response();
            // $error_data = ['status code' => '406' , 'message' => 'Not Acceptable', 'description' => 'The server cannot produce a response matching the list of acceptable content types'];
            // $response->getBody()->write(json_encode($error_data));
            // $response = $response->withStatus(406);
            // return $response;
        }

        $response = $handler->handle($request);
        return $response;
    }
}
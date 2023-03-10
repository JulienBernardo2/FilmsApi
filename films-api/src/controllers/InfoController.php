<?php
namespace Vanier\Api\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InfoController
{
    public function __construct() 
    {

    }

    public function handleGetInfo(Request $request, Response $response)
    {
        $host_name = $request->getUri()->getHost();
        
        $resources = ["films" => $host_name."/films-api/films",
                      "customers" => $host_name."/films-api/customers",
                      "actors" => $host_name."/films-api/actors"
                     ];

        $json_data = json_encode($resources);
        
        $response->getBody()->write($json_data);

        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    }
}
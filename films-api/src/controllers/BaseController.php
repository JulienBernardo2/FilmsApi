<?php
namespace Vanier\Api\Controllers;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * A wrapper class for the PDO MySQL API.
 * This class can be extended for further customization.
 */
class BaseController
{
    protected function prepareResponse($response, $data, $statusCode)
    {
        $json_data = json_encode($data);

        $response->getBody()->write($json_data);
        
        return $response->withStatus($statusCode)->withHeader("Content-Type", "application/json");
    }
}
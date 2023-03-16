<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller for Getting info about the API
 */
class InfoController extends BaseController
{
    /**
     * Gives a description of all of the resources which the API exposes.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     *
     * @return Response The response with the API resource uri's.
    */
    public function handleGetInfo(Request $request, Response $response)
    {
        //Gets the domain name from the user
        $host_name = $request->getUri()->getHost();
        
        //Sets all of the resources into an array
        $resources = ["Get films" => $host_name."/films-api/films",
                      "Get customers" => $host_name."/films-api/customers",
                      "Get actors" => $host_name."/films-api/actors"
                     ];

        //Prepares the response with the list of films
        $response = $this->prepareResponse($response, $resources, StatusCodeInterface::STATUS_OK);
        return $response; 
    }
}
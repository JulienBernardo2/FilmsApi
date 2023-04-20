<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Exceptions\HttpNoContentException;
use Vanier\Api\Exceptions\HttpNotFoundException;
use Vanier\Api\models\LanguagesModel;

class LanguagesController extends BaseController
{
    private $languages_model;

    public function __construct() 
    {
        $this->languages_model = new LanguagesModel();
    }

    public function handleGetAllLanguages(Request $request, Response $response, array $uri_args)
    {
        //Gets the category data
        $languages_data = $this->languages_model->getLanguages();

        //Prepares the response with the list of films
        $response = $this->prepareResponse($response, $languages_data, StatusCodeInterface::STATUS_OK);
        return $response;        
    }
}
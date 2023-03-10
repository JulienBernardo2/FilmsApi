<?php
namespace Vanier\Api\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Fig\Http\Message\StatusCodeInterface;
use Vanier\Api\Exceptions\HttpUnprocessableContentException;
use Vanier\Api\models\FilmsModel;
use Vanier\Api\Validation\Validator;

class FilmsController extends Validator
{
    private $film_model;
    private $validator;
    public function __construct() 
    {
        $this->film_model = new FilmsModel();
        $this->validator = new Validator();
    }

    public function handleGetAllFilms(Request $request, Response $response)
    {
        $filters = $request->getQueryParams();

        $this->film_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);

        $data = $this->film_model->getAll($filters = $request->getQueryParams());

        $json_data = json_encode($data);

        $response->getBody()->write($json_data);

        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    }

    public function handleGetSingleFilm(Request $request, Response $response, array $uri_args)
    {
        $film_id = $uri_args['film_id'];

        //Check if the film_id is a digit, if not throw an exception
        if(!$this->validator->validateInteger($film_id))
        {
            throw new HttpUnprocessableContentException($request);
        }

        $data = $this->film_model->getFilmById($film_id);

        $json_data = json_encode($data);

        $response->getBody()->write($json_data);

        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    }

    public function handleCreateFilms(Request $request, Response $response)
    {
        //Retrieve the data from the request body
        $films_data = $request->getParsedBody();

        
        //insert the new actors in the DB
        foreach($films_data as $films)
        {
            //Validate if the key exists, the value is not empty, check data type, check validation types for the data types(if array_key_exists), 422 error 
            //Pass the actor element to the model
            $this->film_model->createFilm($films);
        };
            
        return $response->withStatus(StatusCodeInterface::STATUS_CREATED)->withHeader("Content-Type", "application/json");
    }
}

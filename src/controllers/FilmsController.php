<?php
namespace Vanier\Api\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Fig\Http\Message\StatusCodeInterface;
use Vanier\Api\Exceptions\HttpNoContentException;
use Vanier\Api\Exceptions\HttpNotFoundException;
use Vanier\Api\Exceptions\httpUnprocessableContentException;
use Vanier\Api\models\FilmsModel;

class FilmsController extends BaseController
{
    private $film_model;
    public function __construct() 
    {
        $this->film_model = new FilmsModel();
    }

    public function handleGetAllFilms(Request $request, Response $response)
    {
        $filters = $request->getQueryParams();

        // Define the allowed filter keys
        $filters_allowed = ['title', 'description', 'language', 'category', 'special_features', 'rating', 'page', 'page_size'];
        $this->checkKeysFilter($filters, $filters_allowed, $request);
        
        //Defines the validation rules for the filters
        $rules = array(
            'title' => [
                'alpha'
            ],
            'page' => [ 
                'integer',
                ['min', 1]
            ],
            'page_size' => [
                'integer',
                ['min', 1],
                ['max', 50]
            ],
            'description' => [
                'alpha'
            ],
            'language' => [
                'alpha'
            ],
            'category' => [
                'alpha'
            ],
            'rating'=> [
                ['in', ['G', 'PG', 'R', 'NC-17', 'PG-13']]
            ],
            'special_features'=> [
                'alpha'
            ]
        );

        //Checks if the rules are respected, if not throws an UnprocessableContent error
        $this->validateRules($filters, $rules, $request);

        $this->film_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);

        $films_data = $this->film_model->getAll($filters = $request->getQueryParams());
        
        if($films_data['data'] == null)
        {
            throw new HttpNoContentException($request);
        } 

        //Prepares the response with the list of actors
        $response = $this->prepareResponse($response, $films_data, StatusCodeInterface::STATUS_OK);
        return $response;
    }

    public function handleGetSingleFilm(Request $request, Response $response, array $uri_args)
    {
        $film_id = $uri_args['film_id'];

        //Checks if the film_id is a numerical value
        if(!ctype_digit($film_id))
        {
            throw new HttpNotFoundException($request, "The film ID  must be a numerical value");
        }

        $film_data = $this->film_model->getFilmById($film_id);

        if($film_data == null)
        {
            throw new HttpNotFoundException($request, "The film ID does not exist");
        }

        //Prepares the response with the list of actors
        $response = $this->prepareResponse($response, $film_data, StatusCodeInterface::STATUS_OK);
        return $response;
    }

    public function handleCreateFilms(Request $request, Response $response)
    {
        //Retrieve the data from the request body
        $films_data = $request->getParsedBody();

        //Removes undesired columns and checks if the desired columns exist
        $undesired_columns = ['film_id', 'last_update', 'replacement_cost'];
        $desired_columns = ['title', 'language_id'];
        $optional_columns = ['description', 'release_year', 'rental_duration', 'rental_rate', 'length', 'rating', 'special_features'];
        $this->checkColumns($films_data, $desired_columns, $undesired_columns, $optional_columns, $request);

        //Defines the validation rules for the filters
        $rules = array(
            'title' => [
                'alpha'
            ],
            'description' => [
                'alpha'
            ],
            'language_id' => [
                'integer',
                ['min', 1],
                ['max', 6]
            ],
            'rental_duration' => [
                'integer',
                ['min', 1],
                ['max', 100]
            ],
            'rental_rate' => [
                'numeric',
                ['min', 1.00],
                ['max', 1000]
            ],
            'length'=> [
                'integer',
                ['min', 1],
                ['max', 10000]
            ],
            'rating'=> [
                ['in', ['G', 'PG', 'R', 'NC-17', 'PG-13']]
            ],
            'special_features'=> [
                ['in', ['Trailers','Commentaries','Deleted Scenes','Behind the Scenes']]
            ]
        );

        // validate rules for each film individually
        foreach ($films_data as $film){
            $this->validateRules($film, $rules, $request);
        }

        //insert the new actors in the DB
        foreach($films_data as $film)
        {            
            $this->film_model->createFilm($film);
        };

        $films_created = ['Films(s) created'];

        //Prepares the response with the film created line
        $response = $this->prepareResponse($response, $films_created, StatusCodeInterface::STATUS_CREATED);
        return $response;
    }

    public function handleUpdateFilms(Request $request, Response $response)
    {
        //Retrieve the data from the request body
        $films_data = $request->getParsedBody();
        $films_columns = array_keys($films_data);
        

        //Removes undesired columns and checks if the desired columns exist
        $undesired_columns = ['last_update', 'replacement_cost'];
        $desired_columns = ['film_id'];
        $optional_columns = ['title', 'language_id', 'description', 'release_year', 'rental_duration', 'rental_rate', 'length', 'rating', 'special_features'];
        $this->checkColumns($films_data, $desired_columns, $undesired_columns, $optional_columns, $request);

        //Defines the validation rules for the filters
        //Defines the validation rules for the filters
        $rules = array(
            'film_id' => [
                'integer'
            ],
            'title' => [
                'alpha'
            ],
            'description' => [
                'alpha'
            ],
            'language_id' => [
                'integer',
                ['min', 1],
                ['max', 6]
            ],
            'rental_duration' => [
                'integer',
                ['min', 1],
                ['max', 100]
            ],
            'rental_rate' => [
                'numeric',
                ['min', 1.00],
                ['max', 1000]
            ],
            'length'=> [
                'integer',
                ['min', 1],
                ['max', 10000]
            ],
            'rating'=> [
                ['in', ['G', 'PG', 'R', 'NC-17', 'PG-13']]
            ],
            'special_features'=> [
                ['in', ['Trailers','Commentaries','Deleted Scenes','Behind the Scenes']]
            ]
        );

        // validate rules for each film individually
        foreach ($films_data as $film){
            $this->validateRules($film, $rules, $request);
        }

        $films_ids = $this->film_model->getAllFilmIds();

        //Check if the film_id exist
        foreach($films_data as $film_desired){
            
            $film_exists = false;
            //Compares each film id to the desired film
            foreach ($films_ids as $film) {
                if($film['film_id'] == $film_desired['film_id']){
                    $film_exists = true;
                    break;
                }
            }
            
            $keys = array_keys($film_desired);
            if(count($keys) == 1){
                throw new httpUnprocessableContentException($request, "Nothing to update");
            }
        }
        
        //If the desired customer does not exist, then throw an error
        if(!$film_exists){
            throw new HttpNotFoundException($request, "The film ID  does not exist");
        }

        //insert the new actors in the DB
        foreach($films_data as $film)
        {
            $film_id = $film['film_id'];

            //Gets rid of the customer id from the customer data
            unset($film['film_id']);
            $this->film_model->updateFilm($film, $film_id);
        };

        $film_updated = ["Film(s) were updated"];

       //Prepares the response with the customer deleted
       $response = $this->prepareResponse($response, $film_updated, StatusCodeInterface::STATUS_OK);
       return $response;
    }

    public function handleDeleteFilms(Request $request, Response $response)
    {
        $film_ids = $request->getParsedBody();
        $film_ids_string = implode(",", $film_ids);

        foreach($film_ids as $film_id){
            if(!is_int($film_id)){
                throw new HttpNotFoundException($request, "Film ID's must be numeric");
            }
        }

        $films_id = $this->film_model->getAllFilmIds();
        $film_exists = false;

        foreach ($films_id as $film) {
            if(in_array($film['film_id'], $film_ids)){
                $film_exists = true;
                break;
            }
        }
        
        if(!$film_exists){
            throw new HttpNotFoundException($request, "One or more film ID(s) do not exist");
        }
        $this->film_model->deleteFilms($film_ids_string);


        $films_deleted = ['Films " .$film_ids_string. " was deleted'];
        return $response->withStatus(StatusCodeInterface::STATUS_OK)->withHeader("Content-Type", "application/json");
    }
}

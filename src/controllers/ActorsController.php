<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Exceptions\HttpNoContentException;
use Vanier\Api\Exceptions\HttpNotFoundException;
use Vanier\Api\models\ActorsModel;

/**
 * Controller for Actor
 */
class ActorsController extends BaseController
{
    /**
     * @var ActorsModel The `ActorsModel` instance
     */
    private $actors_model;

    /**
     * ActorsController constructor.
     *
     * Initializes the `ActorsModel` instances.
    */
    public function __construct() 
    {
        $this->actors_model = new ActorsModel();
    }


    /**
     * gets all actors matching the given filters.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * 
     * @throws HttpNoContentException If there are no Actors that match the filters.
     * 
     * @return Response The response object with all of the actors which matched with the filters.
     */
    public function handleGetAllActors(Request $request, Response $response)
    {
        //Gets the filters that where sent in the http request
        $filters = $request->getQueryParams();
        
        // Define the allowed filter keys
        $filters_allowed = ['first_name', 'last_name', 'page', 'page_size'];

        //Checks if the filter keys are proper, if not throws an UnprocessableContent error
        $this->checkKeysFilter($filters, $filters_allowed, $request);

        //Sets the pagination options and if they are not specified, sets the defaults to page 1 and page_size 10
        $this->actors_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);

        //Defines the validation rules for the filters
        $rules = array(
            'first_name' => [
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
            'last_name' => [
                'alpha'
            ]
        );

        //Checks if the rules are respected, if not throws an UnprocessableContent error
        $this->validateRules($filters, $rules, $request);

        //Gets all of the actors
        $actors_data = $this->actors_model->getAll($filters);

        //Checks if there are no actors for the filters
        if($actors_data['data'] == null)
        {
            throw new HttpNoContentException($request);
        }   
        
        //Prepares the response with the list of actors
        $response = $this->prepareResponse($response, $actors_data, StatusCodeInterface::STATUS_OK);
        return $response;   
    }

    /**
     * Gets the list of films that belong to the specified actor.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $uri_args The URI arguments (category_id).
     * 
     * @throws HttpNotFoundException If the category ID does not exist or is not a numerical value.
     * @throws HttpNoContentException If there are no Films from this actor that have the values of the filters.
     * 
     * @return Response The response object with the list of films for the actor.
     */
    public function handleGetAllActorFilms(Request $request, Response $response, array $uri_args)
    {
        //Gets the actor_id from the uri and the filters that where sent in the http request
        $actor_id = $uri_args['actor_id'];
        $filters = $request->getQueryParams();

        //Checks if the actor_id is a numerical value
        if(!ctype_digit($actor_id))
        {
            throw new HttpNotFoundException($request, "The actor ID  must be a numerical value");
        }

        // Define the allowed filter keys
        $filters_allowed = ['length', 'rating', 'category', 'page', 'page_size'];

        //Checks if the filter keys are proper, if not throws an UnprocessableContent error
        $this->checkKeysFilter($filters, $filters_allowed, $request);

        //Defines the validation rules for the filters
        $rules = array(
            'length' => [
                'integer',
                ['min', 1],
                ['max', 10000]
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
            'category'=> [
                'alpha'
            ]
        );

        //Checks if the rules are respected, if not throws an UnprocessableContent error
        $this->validateRules($filters, $rules, $request);

        //Sets the pagination options and if they are not specified, sets the defaults to page 1 and page_size 10
        $this->actors_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);
        
        //Gets the actor data
        $actor_data = $this->actors_model->getActorById($actor_id);

        //Checks that the actor exists and gets the films from it
        if($actor_data == null)
        {
            throw new HttpNotFoundException($request, "The actor ID does not exist");
        } else{

            $actor_data["film"] = $this->actors_model->getActorFilms($actor_id, $filters);

            if($actor_data["film"]['data'] == null)
            {
                throw new HttpNoContentException($request);
            }
        }
        
        //Prepares the response with the list of films
        $response = $this->prepareResponse($response, $actor_data, StatusCodeInterface::STATUS_OK);
        return $response;
    }

    /**
     * Creates new actor or actors with the given data.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * 
     * @return Response The response object with the success line.
     */
    public function handleCreateActors(Request $request, Response $response)
    {
        //Gets the actor data from the request body
        $actors_data = $request->getParsedBody();

        //Removes undesired columns and checks if the desired columns exist
        $undesired_columns = ['actor_id', 'last_update'];
        $desired_columns = ['first_name', 'last_name'];
        $optional_columns = [];
        $this->checkColumns($actors_data, $desired_columns, $undesired_columns, $optional_columns, $request);

        //Defines the validation rules for the columns
        $rules = array(
            'first_name' => [
                'alpha'
            ],
            'last_name' => [
                'alpha'
            ]
        );

        // validate rules for each actor individually
        foreach ($actors_data as $actor){
            $this->validateRules($actor, $rules, $request);
        }

        //insert the new actors in the DB
        foreach($actors_data as $actor)
        {            
            $this->actors_model->createActor($actor);
        };

        $actors_created = ['Actor(s) created'];

        //Prepares the response with the actors created line
        $response = $this->prepareResponse($response, $actors_created, StatusCodeInterface::STATUS_CREATED);
        return $response;
    }
}
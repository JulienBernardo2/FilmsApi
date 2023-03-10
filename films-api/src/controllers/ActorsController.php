<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Exceptions\httpUnprocessableContentException;
use Vanier\Api\Validation\Validator;


use Vanier\Api\models\ActorsModel;

class ActorsController
{
    private $actors_model;
    public function __construct() 
    {
        $this->actors_model = new ActorsModel();
    }

    public function handleGetAllActors(Request $request, Response $response)
    {
        $filters = $request->getQueryParams();

        $this->actors_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);

        $data = $this->actors_model->getAll($filters);

        $json_data = json_encode($data);

        $response->getBody()->write($json_data);

        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    }

    public function handleGetAllActorFilms(Request $request, Response $response, array $uri_args)
    {
        $actor_id = $uri_args['actor_id'];
        $filters = $request->getQueryParams();

        $this->actors_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);
        
        $actor_data = $this->actors_model->getActorById($actor_id);
        $actor_data["films"] = $this->actors_model->getActorFilms($actor_id, $filters);

        $json_data = json_encode($actor_data);

        $response->getBody()->write($json_data);

        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    }

    public function handleCreateActors(Request $request, Response $response)
    {
        //Validate if the key exists, the value is not empty, check data type, check validation types for the data types(if array_key_exists), 422 error 
        //Pass the actor element to the model
        //Retrieve the data from the request body
        $actors_data = $request->getParsedBody();
        //var_dump($actors_data);
        if($actors_data == null){
            echo'heressss';exit;
            //throw new httpUnprocessableContentException($request);
        } else{
            $rules = array(
                'first_name' => [
                    'required',
                    'alpha'
                ],
                'last_name' => [
                    'required',
                    'alpha'
                ]
            );
    
            $validator = new Validator($actors_data, []);
            $validator->mapFieldsRules($rules);
            
            if ($validator->validate()) {
                echo 'im here';exit;
                foreach($actors_data as $actors)
                {
                    $this->actors_model->createActor($actors);
                };
    
                $response->getBody()->write(json_encode("Actors were created"));
                return $response->withStatus(StatusCodeInterface::STATUS_CREATED)->withHeader("Content-Type", "application/json");
            } else {
                echo $validator->errorsToJson();exit;  
                //throw new httpUnprocessableContentException($request);
            }
        }   
    }
}
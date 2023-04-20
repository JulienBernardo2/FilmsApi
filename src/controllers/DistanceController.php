<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Exceptions\httpUnprocessableContentException;
use Vanier\Api\models\DistanceModel;
use Vanier\Api\Helpers\Calculator;

/**
 * Controller for Distance
 */
class DistanceController extends BaseController
{
    private $distance_model;

    public function __construct() 
    {
        $this->distance_model = new DistanceModel();
    }
    
    public function handleGetDistance(Request $request, Response $response)
    {
        //Retrieve the data from the request body
        $distances_data = $request->getParsedBody();

        //Checks if the columns are there and not empty
        $desired_columns = ['from', 'to', 'unit'];
        $this->checkColumnsDistance($distances_data, $desired_columns, $request);

        //Check if the postal code is of valid format and does exist
        $regex = "/^[a-z, A-Z]\d[a-z, A-Z]$/";
        if(!preg_match($regex, $distances_data["from"]) || !preg_match($regex, $distances_data["to"]))
        {
            throw new httpUnprocessableContentException($request, "Not a valid Postal Code");
        }

        $distance_from = $this->distance_model->getDistanceFrom($distances_data["from"]);
        $distance_to = $this->distance_model->getDistanceFrom($distances_data["to"]);

        if($distance_from == null || $distance_to == null)
        {
            throw new httpUnprocessableContentException($request, "Postal code does not exist");
        }
        $calculator = new Calculator();
        $distance_between = $calculator->calculate(
            $distance_from["latitude"],
            $distance_from["longitude"],
            $distance_to["latitude"],
            $distance_to["longitude"]
        )->getDistance();
        
        //Checks if the unit passed is all and if so selects the proper method
        $isAll = false;
        foreach($distances_data['unit'] as $unit)
        {
            if($unit == "all"){
                $distance_between = $calculator->toAll(2, true);
                $isAll = true;
                break;
            }
        }
        
        if($isAll == false){
            $distance_between = $calculator->toMany($distances_data['unit'], 2, true);
        }

        $json_data["from"] = $distance_from; 
        $json_data["to"] = $distance_to; 
        $json_data["distance"] = $distance_between; 

        //Prepares the response with the film created line
        $response = $this->prepareResponse($response, $json_data, StatusCodeInterface::STATUS_OK);
        return $response;
    }

}
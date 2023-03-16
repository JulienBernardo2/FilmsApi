<?php
namespace Vanier\Api\Controllers;

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Vanier\Api\Exceptions\httpUnprocessableContentException;
use Vanier\Api\Validation\Validator;

/**
 * A wrapper class for the controller classes.
 * This class can be extended for further customization.
 */
class BaseController
{
    /**
     * Prepares a JSON response with the given data and HTTP status code.
     *
     * @param Response $response The response object.
     * @param array $data The data to be returned in the response.
     * @param int $statusCode The status code for the response.
     * 
     * @return Response The response object.
     */
    protected function prepareResponse(Response $response, array $data, int $statusCode)
    {
        //Converts the data to json
        $json_data = json_encode($data);

        //Writes it to the body
        $response->getBody()->write($json_data);
        
        //Returns the response with the proper status code and content type
        return $response->withStatus($statusCode)->withHeader("Content-Type", "application/json");
    }


    /**
     * Checks that the keys in the given filters array are allowed and not empty.
     *
     * @param array $filters The filters.
     * @param array $filters_allowed The list of allowed filter keys.
     * @param Request $request The request.
     * 
     * @throws httpUnprocessableContentException if a filter key is not allowed or its value is empty.
    */
    protected function checkKeysFilter(array $filters, array $filters_allowed, Request $request)
    {
        //Gets the keys from the filters array
        foreach (array_keys($filters) as $filter) {
            //Checks if the key is an allowed filter
            if (!in_array($filter, $filters_allowed)) {
                throw new httpUnprocessableContentException($request, "Invalid filter key: $filter");
            }
            //checks if the value of the filter is empty
            if (empty($filters[$filter])) {
                throw new httpUnprocessableContentException($request, "Filter '$filter' value cannot be empty");
            }
        }
    }

    /**
     * Checks that only the desired keys are present in an array of data, and that no undesired keys are present
     * and that there are not random columns added.
     *
     * @param array $data The data to be checked.
     * @param array $desiredKeys The list of keys that must be present.
     * @param array $undesiredKeys The list of keys that must not be present.
     * @param Request $request The request object.
     * 
     * @throws httpUnprocessableContentException If the data is invalid.
     */
    protected function checkColumns(array $data, array $desiredKeys, array $undesiredKeys, array $optional_keys, Request $request)
    {
        //Goes through each object given from the data (actor, film, customer)
        foreach ($data as $object) {

            //Checks if the actor has all of the keys and that their values are not empty
            foreach($desiredKeys as $key)
            {
                if (!array_key_exists($key, $object)) {
                    throw new httpUnprocessableContentException($request, "Missing Required Key: $key");
                }

                else if (empty($object[$key])) {
                    throw new httpUnprocessableContentException($request, "Column '$key' value cannot be empty");
                }
            }

            //Checks if the actor has none of these keys
            foreach($undesiredKeys as $key){
                if(array_key_exists($key, $object)){
                    throw new httpUnprocessableContentException($request, "The key $key cannot be set");
                }
            }

            //Checks if the actor has no values empty
            foreach($desiredKeys as $key)
            {
                if (empty($object[$key])) {
                    throw new httpUnprocessableContentException($request, "Column '$key' value cannot be empty");
                }
            }
            
            //Gets all of the invalid columns by checking if the column belongs in the allKeys array
            $allKeys = array_merge($desiredKeys, $undesiredKeys, $optional_keys);
            $invalidKeys = array_diff(array_keys($object), $allKeys);
            if (!empty($invalidKeys)) {
                throw new httpUnprocessableContentException($request, "Invalid Key(s): " . implode(',', $invalidKeys));
            }
        }
    }

    /**
     * Validates an array of data against rules
     *
     * @param array $data The data.
     * @param array $rules The validation rules.
     * @param Request $request The request object.
     * 
     * @throws httpUnprocessableContentException If the data is invalid.
     */
    protected function validateRules(array $data, array $rules, Request $request)
    {
        //Validates the data from the specified rules
        $validator = new Validator($data, []);

        //Adds the validation for multiple fields
        $validator->mapFieldsRules($rules);
        
        //Checks if the data is valid, if not throws the error
        if (!$validator->validate()) {
            $errors = $validator->errorsToString();
            throw new httpUnprocessableContentException($request, $errors); 
        }
    }
}
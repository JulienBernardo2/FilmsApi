<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Exceptions\HttpNoContentException;
use Vanier\Api\Exceptions\HttpNotFoundException;
use Vanier\Api\Exceptions\httpUnprocessableContentException;
use Vanier\Api\models\CustomersModel;

/**
 * Controller for Customers
 */
class CustomersController extends BaseController
{
    /**
     * @var CustomersModel The `CustomersModel` instance
    */
    private $customers_model;
    
    /**
     * CustomersController constructor.
     *
     * Initializes the `CustomersModel` instances.
    */
    public function __construct() 
    {
        $this->customers_model = new CustomersModel();
    }

    /**
     * Get all customers.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     *
     * @throws HttpNoContentException If there are no customers found that match with the filters.
     *
     * @return Response The response object containing the customers.
     */
    public function handleGetAllCustomers(Request $request, Response $response)
    {
        $filters = $request->getQueryParams();

        // Define the allowed filter keys
        $filters_allowed = ['first_name', 'last_name', 'city', 'country', 'page', 'page_size'];
        $this->checkKeysFilter($filters, $filters_allowed, $request);
        
        //Defines the validation rules for the filters
        $rules = array(
            'first_name' => [
                ['regex', '/^[a-zA-Z]+$/']
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
                ['regex', '/^[a-zA-Z]+$/']
            ],
            'city' => [
                ['regex', '/^[a-zA-Z]+$/']
            ],
            'country' => [
                ['regex', '/^[a-zA-Z]+$/']
            ]
        );
        
        $this->validateRules($filters, $rules, $request);

        $this->customers_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);

        $customers_data = $this->customers_model->getAll($filters);

        if($customers_data['data'] == null)
        {
            throw new HttpNoContentException($request);
        } 

        //Prepares the response with the list of actors
        $response = $this->prepareResponse($response, $customers_data, StatusCodeInterface::STATUS_OK);
        return $response;
    }

    /**
     * Get all films rented by a customer.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $uri_args The URI arguments (customer_id).
     *
     * @throws HttpNotFoundException If the customer ID does not exist or is not a numerical value.
     * @throws HttpNoContentException If there are no Films from this customer that have the values of the filters.
     *
     * @return Response The response object with the list of films for the customer.
     */
    public function handleGetAllCustomerFilms(Request $request, Response $response, array $uri_args)
    {
        $customer_id = $uri_args['customer_id'];
        $filters = $request->getQueryParams();

        //Checks if the actor_id is a numerical value
        if(!ctype_digit($customer_id))
        {
            throw new HttpNotFoundException($request, "The customer ID  must be a numerical value");
        }

        // Define the allowed filter keys
        $filters_allowed = ['special_features', 'fromRentalDate', 'toRentalDate', 'rating', 'category', 'page', 'page_size'];

        //Checks if the filter keys are proper, if not throws an UnprocessableContent error
        $this->checkKeysFilter($filters, $filters_allowed, $request);

        //Defines the validation rules for the filters
        $rules = array(
            'rating' => [
                ['in', ['G', 'PG', 'R', 'NC-17', 'PG-13']]
            ],
            'special_features'=> [
                ['regex', '/^[a-zA-Z]+$/']
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
                ['regex', '/^[a-zA-Z]+$/']
            ],
            'fromRentalDate' => [
                ['requiredWith', ['toRentalDate']],
                ['dateBefore', $filters['toRentalDate']]
            ], 
             'toRentalDate' => [
                ['requiredWith', ['fromRentalDate']],
                ['dateAfter', $filters['fromRentalDate']]
            ]
        );

        //Checks if the rules are respected, if not throws an UnprocessableContent error
        $this->validateRules($filters, $rules, $request);

        $this->customers_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);
        
        $customer_data = $this->customers_model->getCustomerById($customer_id);
        
        //Checks that the customer exists and gets the films from it
        if($customer_data == null)
        {
            throw new HttpNotFoundException($request, "The customer ID does not exist");
        } else{

            $customer_data["film"] = $this->customers_model->getCustomerFilms($customer_id, $filters);

            if($customer_data["film"]['data'] == null)
            {
                throw new HttpNoContentException($request);
            }
        }
        
        //Prepares the response with the list of films
        $response = $this->prepareResponse($response, $customer_data, StatusCodeInterface::STATUS_OK);
        return $response;
    }

    /**
     * Update a customer.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     *
     * @throws HttpUnprocessableContentException If the there is no column to update.
     *
     * @return Response The response object.
     */
    public function handleUpdateCustomers(Request $request, Response $response)
    {
        //Retrieve the data from the request body
        $customers_data = $request->getParsedBody();

        //Removes undesired columns and checks if the desired columns exist
        $undesired_columns = ['last_update', 'create_date'];
        $desired_columns = ['customer_id'];
        $optional_columns = ['store_id', 'first_name', 'last_name', 'email', 'address_id', 'active'];
        $this->checkColumns($customers_data, $desired_columns, $undesired_columns, $optional_columns, $request);

        //Defines the validation rules for the filters
        $rules = array(
            'customer_id'=>[
                'integer'
            ],
            'store_id' => [
                'integer',
                ['min', 1],
                ['max', 2]
            ],
            'first_name' => [
                'alpha'
            ],
            'last_name' => [
                'alpha'
            ],
            'email' => [
                'email'
            ],
            'address_id' => [
                'integer',
                ["min", 1],
                ["max", 605]
            ],
            'active' => [
                'integer',
                ['min', 0],
                ['max', 1]
            ]
        );
        
        // validate rules for each customer individually
        foreach ($customers_data as $customer){
            $this->validateRules($customer, $rules, $request);
        }

        //Check if the customer_id exist
        foreach($customers_data as $customer_desired){
            $this->checkCustomerExists($customer_desired['customer_id'], $request);
            
            $keys = array_keys($customer_desired);
            if(count($keys) == 1){
                throw new httpUnprocessableContentException($request, "Nothing to update");
            }
        }   

        //insert the updated values for the customer in the DB
        foreach($customers_data as $customer)
        {
            $customer_id = $customer['customer_id'];

            //Gets rid of the customer id from the customer data
            unset($customer['customer_id']);

            $this->customers_model->updateCustomer($customer, $customer_id);
        };

        $customer_updated = ["Customer(s) were updated"];

       //Prepares the response with the customer deleted
       $response = $this->prepareResponse($response, $customer_updated, StatusCodeInterface::STATUS_OK);
       return $response;
    }

    /**
     * Delete a customer.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @param array $uri_args The URI arguments (customer_id).
     *
     * @throws HttpNotFoundException If the customer ID does not exist or is not a numerical value.
     *
     * @return Response The response object.
     */
    public function handleDeleteCustomer(Request $request, Response $response, array $uri_args)
    {
        //Gets the customer_id from the Uri
        $customer_id = $uri_args['customer_id'];

        //Checks if the customer_id is a numerical value
        if(!ctype_digit($customer_id))
        {
            throw new HttpNotFoundException($request, "The customer ID  must be a numerical value");
        }

        //Checks if the customer exists
        $this->checkCustomerExists($customer_id, $request);

        //Deletes the customer
        $this->customers_model->deleteCustomer($customer_id);

        $customer_deleted = ["Customer " . $customer_id . " was deleted"];

        //Prepares the response with the customer deleted
        $response = $this->prepareResponse($response, $customer_deleted, StatusCodeInterface::STATUS_OK);
        return $response;
    }

    /**
     * Checks if a customer exists.
     *
     * @param int $desired_id The customer wanting to know if they exist
     * @param Request The request object
     * @throws HttpNotFoundException If the customer ID does not exist.
     *
     */
    private function checkCustomerExists(int $desired_id, Request $request)
    {
        //Gets all of the customer id's
        $customers_ids = $this->customers_model->getAllCustomerIds();
        $customer_exists = false;

        //Compares each customer id to the desired customer
        foreach ($customers_ids as $customer) {
            if($customer['customer_id'] == $desired_id){
                $customer_exists = true;
                break;
            }
        }
        
        //If the desired customer does not exist, then throw an error
        if(!$customer_exists){
            throw new HttpNotFoundException($request, "The customer ID  does not exist");
        }
    }
}
<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Vanier\Api\models\CustomersModel;

class CustomersController
{
    private $customers_model;
    
    public function __construct() 
    {
        $this->customers_model = new CustomersModel();
    }

    public function handleGetAllCustomers(Request $request, Response $response)
    {
        $filters = $request->getQueryParams();
        
        $this->customers_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);

        $data = $this->customers_model->getAll($filters);

        $json_data = json_encode($data);

        $response->getBody()->write($json_data);

        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    }

    public function handleGetAllCustomerFilms(Request $request, Response $response, array $uri_args)
    {
        $customer_id = $uri_args['customer_id'];
        $filters = $request->getQueryParams();

        $this->customers_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);
        
        $customer_data = $this->customers_model->getCustomerById($customer_id);
        $customer_data["films"] = $this->customers_model->getCustomerFilms($customer_id, $filters);

        $json_data = json_encode($customer_data);

        $response->getBody()->write($json_data);

        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    }

    public function handleUpdateCustomers(Request $request, Response $response)
    {
        //Retrieve the data from the request body
        $customers_data = $request->getParsedBody();
        
        //insert the new actors in the DB
        foreach($customers_data as $customers)
        {
            $this->customers_model->updateCustomer($customers, $customers["customer_id"]);
        };

        $response->getBody()->write("Customers were updated");
            
        return $response->withStatus(StatusCodeInterface::STATUS_OK)->withHeader("Content-Type", "application/json");
    }

    public function handleDeleteCustomer(Request $request, Response $response, array $uri_args)
    {
        $customer_id = $uri_args['customer_id'];
        
        $this->customers_model->deleteCustomer($customer_id);

        $response->getBody()->write("Customer " . $customer_id . " was deleted");

        return $response->withStatus(StatusCodeInterface::STATUS_OK)->withHeader("Content-Type", "application/json");
    }
}
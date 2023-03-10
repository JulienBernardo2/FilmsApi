<?php
namespace Vanier\Api\Controllers;
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
}
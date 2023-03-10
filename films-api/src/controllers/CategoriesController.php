<?php
namespace Vanier\Api\Controllers;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Vanier\Api\models\CategoriesModel;

class CategoriesController
{
    private $categories_model;
    public function __construct() 
    {
        $this->categories_model = new CategoriesModel();
    }

    public function handleGetAllCategoryFilms(Request $request, Response $response, array $uri_args)
    {
        $category_id = $uri_args['category_id'];
        $filters = $request->getQueryParams();
        
        $this->categories_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);
        
        $category_data = $this->categories_model->getCategoryById($category_id);
        $category_data["film"] = $this->categories_model->getCategoryFilms($category_id, $filters);

        $json_data = json_encode($category_data);

        $response->getBody()->write($json_data);

        return $response->withStatus(200)->withHeader("Content-Type", "application/json");
    }
}
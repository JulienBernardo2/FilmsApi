<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Exceptions\HttpNoContentException;
use Vanier\Api\Exceptions\HttpNotFoundException;
use Vanier\Api\models\CategoriesModel;

/**
 * Controller for Categories
 */
class CategoriesController extends BaseController
{
    /**
     * @var CategoriesModel The `CategoriesModel` instance
    */
    private $categories_model;

    /**
     * CategoriesController constructor.
     *
     * Initializes the `CategoriesModel` instances.
    */
    public function __construct() 
    {
        $this->categories_model = new CategoriesModel();
    }

    /**
     * Gets the list of films that belong to the specified category.
     *
     * @param Request $request The request object.
     * @param  Response $response The response object.
     * @param array $uri_args The uri arguments (category_id).
     *
     * @throws HttpNotFoundException If the category ID does not exist or is not a numerical value.
     * @throws HttpNoContentException If there are no Films from this category that have the values of the filters.
     *
     * @return Response The response object with the list of films for the category.
    */
    public function handleGetAllCategoryFilms(Request $request, Response $response, array $uri_args)
    {
        //Gets the category_id from the uri and the filters that where sent in the http request
        $category_id = $uri_args['category_id'];
        $filters = $request->getQueryParams();
        
        //Checks if the category_id is a numerical value
        if(!ctype_digit($category_id))
        {
            throw new HttpNotFoundException($request, "The category ID  must be a numerical value");
        }

        // Define the allowed filter keys
        $filters_allowed = ['length', 'rating', 'page', 'page_size'];

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
            'rating'=> [
                ['in', ['G', 'PG', 'R', 'NC-17', 'PG-13']]
            ]
        );

        //Checks if the rules are respected, if not throws an UnprocessableContent error
        $this->validateRules($filters, $rules, $request);

        //Sets the pagination options and if they are not specified, sets the defaults to page 1 and page_size 10
        $this->categories_model->setPaginationOptions($filters["page"] ?? 1, $filters["page_size"] ?? 10);
        
        //Gets the category data
        $category_data = $this->categories_model->getCategoryById($category_id);

        //Checks that the category exists and gets the films from it
        if($category_data == null)
        {
            throw new HttpNotFoundException($request, "The category ID does not exist");
        } else{

            $category_data["film"] = $this->categories_model->getCategoryFilms($category_id, $filters);

            if($category_data["film"]['data'] == null)
            {
                throw new HttpNoContentException($request);
            }
        }

        //Prepares the response with the list of films
        $response = $this->prepareResponse($response, $category_data, StatusCodeInterface::STATUS_OK);
        return $response;        
    }
}
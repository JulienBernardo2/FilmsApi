<?php
namespace Vanier\Api\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Vanier\Api\Exceptions\HttpNoContentException;
use Vanier\Api\Exceptions\HttpNotFoundException;
use Vanier\Api\Exceptions\httpUnprocessableContentException;
use Vanier\Api\models\CategoriesModel;
use Vanier\Api\Validation\Validator;

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
     * Initializes the `CategoriesModel` and `Validator` instances.
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
     * @param array $uri_args The uri arguments.
     *
     * @throws HttpNotFoundException If the category ID does not exist or is not a numerical value.
     * @throws HttpUnprocessableContentException If the filters are not valid.
     * @throws HttpNoContentException If there are no Films from this category.
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

        //Defines the validation rules for the filters
        $rules = array(
            'length' => [
                'numeric',
                ['min', 1]
            ],
            'page' => [
                'numeric',
                ['min', 1]
            ],
            'page_size' => [
                'numeric',
                ['min', 1]
            ],
            'rating'=> [
                ['in', ['G', 'PG', 'R', 'NC-17', 'PG-13']] 
            ]
        );

        //Validates the filters from the rules above
        $validator = new Validator($filters, []);
        $validator->mapFieldsRules($rules);
        
        if (!$validator->validate()) {
            $errors = $validator->errorsToString();
            throw new httpUnprocessableContentException($request, $errors); 
        }

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

            if($category_data["film"] == null)
            {
                throw new HttpNoContentException($request, "There are no Films in this category");
            }
        }

        //Prepares the response with the list of films
        $response = $this->prepareResponse($response, $category_data, StatusCodeInterface::STATUS_OK);
        return $response;        
    }
}
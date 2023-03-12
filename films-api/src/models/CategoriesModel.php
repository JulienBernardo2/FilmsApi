<?php
namespace Vanier\Api\Models;
use Vanier\Api\models\BaseModel;

class CategoriesModel extends BaseModel
{   
    /**
     * CategoriesModel constructor.
     *
     * Constructs a new CategoriesModel object.
    */
    public function __construct() 
    {
        parent:: __construct();
    }

    /**
     * Returns a paginated list of films from the specified category that meet the filters.
     *
     * @param int $category_id The ID of the category.
     * @param array $filters An array of filter.
     * @return array An array of films that meet the specified criteria.
     */
    public function getCategoryFilms(int $category_id, array $filters = [])
    {
        //The filter values which will be joined to the sql query
        $query_values = [];
        
        //Gets the films based off of the category given
        $sql = "SELECT film.*, GROUP_CONCAT(DISTINCT CONCAT(actor.first_name, ' ', actor.last_name)) AS actors
                    FROM film
                    JOIN film_actor ON film_actor.film_id = film.film_id
                    JOIN actor ON film_actor.actor_id = actor.actor_id
                    JOIN film_category ON film.film_id = film_category.film_id
                    JOIN category ON category.category_id = film_category.category_id
                    WHERE 1";
        
        //Checks if the length filter was applied 
        if(isset($filters['length']))
        {
            $sql .= " AND length >= :length ";
            $query_values[":length"] = $filters['length']."%";
        }

        //Checks if the rating filter was applied
        if(isset($filters['rating']))
        {
            $sql .= " AND rating LIKE CONCAT(:rating, '%') ";
            $query_values[":rating"] = $filters['rating'];
        }

        $sql .= " AND category.category_id = :category_id GROUP BY film.film_id ORDER BY film.film_id";

        //Returns the results of the query
        return $this->paginate($sql, [":category_id"=> $category_id] + $query_values);
    }

    /**
     * Returns the category with the specified ID.
     *
     * @param int $category_id The ID of the category.
     * @return array The category with the specified ID.
     */
    public function getCategoryById(int $category_id)
    {   
        //Gets the category based off of the ID given
        $sql = "SELECT *
                    FROM category
                    WHERE category.category_id = :category_id";

        //Returns the results of the query
        return $this->run($sql, [":category_id"=> $category_id])->fetch();
    }
}
<?php
namespace Vanier\Api\Models;
use Vanier\Api\models\BaseModel;

class CategoriesModel extends BaseModel
{ 
    public function __construct() 
    {
        parent:: __construct();
    }

    public function getCategoryFilms(int $category_id, array $filters = [])
    {
        $query_values = [];

        $sql = "SELECT film.*, GROUP_CONCAT(DISTINCT CONCAT(actor.first_name, ' ', actor.last_name)) AS actors
                    FROM film
                    JOIN film_actor ON film_actor.film_id = film.film_id
                    JOIN actor ON film_actor.actor_id = actor.actor_id
                    JOIN film_category ON film.film_id = film_category.film_id
                    JOIN category ON category.category_id = film_category.category_id
                    WHERE 1";
        
        if(isset($filters['length']))
        {
            $sql .= " AND length >= :length ";
            $query_values[":length"] = $filters['length']."%";
        }

        if(isset($filters['rating']))
        {
            $sql .= " AND rating LIKE CONCAT(:rating, '%') ";
            $query_values[":rating"] = $filters['rating'];
        }

        $sql .= " AND category.category_id = :category_id GROUP BY film.film_id ORDER BY film.film_id";

        return $this->paginate($sql, [":category_id"=> $category_id] + $query_values);
    }

    public function getCategoryById(int $category_id)
    {
        $sql = "SELECT *
                    FROM category
                    WHERE category.category_id = :category_id";

        return $this->run($sql, [":category_id"=> $category_id])->fetch();
    }
}
<?php
namespace Vanier\Api\Models;
use Vanier\Api\models\BaseModel;

class ActorsModel extends BaseModel
{
    public function __construct() 
    {
        parent:: __construct();
    }

    public function getAll(array $filters = [])
    {
        $query_values = [];

        $sql = "SELECT * FROM actor
                    WHERE 1";

        if(isset($filters['first_name']))
        {
            $sql .= " AND first_name LIKE CONCAT('%', :first_name, '%') ";
            $query_values[":first_name"] = $filters['first_name'];
        }

        if(isset($filters['last_name']))
        {
            $sql .= " AND last_name LIKE CONCAT('%', :last_name, '%') ";
            $query_values[":last_name"] = $filters['last_name'];
        }

        return $this->paginate($sql, $query_values);
    }

    public function getActorFilms(int $actor_id, array $filters = [])
    {
        $query_values = [];

        $sql = "SELECT film.*, category.name AS category_name
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

        if(isset($filters['category']))
        {
            $sql .= " AND category.name LIKE CONCAT(:category, '%') ";
            $query_values[":category"] = $filters['category'];
        }

        if(isset($filters['rating']))
        {
            $sql .= " AND rating LIKE CONCAT(:rating, '%') ";
            $query_values[":rating"] = $filters['rating'];
        }

        $sql .= " AND actor.actor_id = :actor_id ORDER BY film.film_id";

        return $this->paginate($sql, [":actor_id"=> $actor_id] + $query_values);
    }

    public function getActorById(int $actor_id)
    {
        $sql = "SELECT *
                    FROM actor
                    WHERE actor.actor_id = :actor_id";

        return $this->run($sql, [":actor_id"=> $actor_id])->fetch();
    }

    public function createActor(array $actor)
    {
        return $this->insert('actor', $actor);
    }
}

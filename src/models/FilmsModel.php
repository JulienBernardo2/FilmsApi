<?php
namespace Vanier\Api\Models;
use Vanier\Api\models\BaseModel;

class FilmsModel extends BaseModel
{
    public function __construct() 
    {
        parent:: __construct();
    }

    public function getAll(array $filters = [])
    {
        $query_values = [];

        $sql = "SELECT film.*, GROUP_CONCAT(DISTINCT CONCAT(actor.first_name, ' ', actor.last_name)) AS actors, category.name AS category_name, language.name AS language_name
                    FROM film
                    LEFT JOIN film_actor ON film_actor.film_id = film.film_id
                    LEFT JOIN actor ON film_actor.actor_id = actor.actor_id
                    LEFT JOIN film_category ON film_category.film_id = film.film_id
                    LEFT JOIN category ON film_category.category_id = category.category_id
                    JOIN language ON language.language_id = film.language_id 
                    WHERE 1";
        
        if(isset($filters['title']))
        {
            $sql .= " AND title LIKE CONCAT(:title, '%') ";
            $query_values[":title"] = $filters['title']."%";
        }

        if(isset($filters['description']))
        {
            $sql .= " AND description LIKE CONCAT('%', :description, '%') ";
            $query_values[":description"] = $filters['description'];
        }

        if(isset($filters['language']))
        {
            $sql .= " AND language.name LIKE CONCAT(:language, '%') ";
            $query_values[":language"] = $filters['language'];
        }

        if(isset($filters['category']))
        {
            $sql .= " AND category.name LIKE CONCAT(:category, '%') ";
            $query_values[":category"] = $filters['category'];
        }

        if(isset($filters['special_features']))
        {
            $sql .= " AND special_features LIKE CONCAT('%', :special_features, '%') ";
            $query_values[":special_features"] = $filters['special_features'];
        }

        if(isset($filters['rating']))
        {
            $sql .= " AND rating LIKE CONCAT(:rating, '%') ";
            $query_values[":rating"] = $filters['rating'];
        }

        $sql .= " GROUP BY film.film_id ORDER BY film.film_id";

        return $this->paginate($sql, $query_values);
    }

    public function getFilmById(int $film_id)
    {
        $sql = "SELECT film.*
                FROM film
                WHERE film.film_id=:film_id";
        
        return $this->run($sql, [":film_id"=> $film_id])->fetch();
    }

    public function getAllFilmIds()
    {
        $sql = "SELECT film_id
                    FROM film";

        return $this->run($sql)->fetchAll();
    }

    public function createFilm(array $film)
    {
        return $this->insert('film', $film);
    }

    public function updateFilm(array $film, int $film_id)
    {
        return $this->update('film', $film, ["film_id" => "$film_id"]);
    }

    public function deleteFilms(string $film_ids)
    {
        return $this->deleteByIds('film', 'film_id', $film_ids);
    }
}

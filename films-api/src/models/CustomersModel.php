<?php
namespace Vanier\Api\Models;
use Vanier\Api\models\BaseModel;

class CustomersModel extends BaseModel
{ 
    public function __construct() 
    {
        parent:: __construct();
    }

    public function getAll(array $filters = [])
    {
        $query_values = [];

        $sql = "SELECT customer.*, address, postal_code, district, phone, city, country 
                    FROM customer 
                    JOIN address ON customer.address_id = address.address_id 
                    JOIN city ON address.city_id = city.city_id
                    JOIN country ON city.country_id = country.country_id
                    WHERE 1";

        if(isset($filters['first_name']))
        {
            $sql .= " AND first_name LIKE CONCAT(:first_name, '%') ";
            $query_values[":first_name"] = $filters['first_name'];
        }

        if(isset($filters['last_name']))
        {
            $sql .= " AND last_name LIKE CONCAT(:last_name, '%') ";
            $query_values[":last_name"] = $filters['last_name'];
        }

        if(isset($filters['city']))
        {
            $sql .= " AND city LIKE CONCAT(:city, '%') ";
            $query_values[":city"] = $filters['city'];
        }

        if(isset($filters['country']))
        {
            $sql .= " AND country LIKE CONCAT(:country, '%') ";
            $query_values[":country"] = $filters['country'];
        }

        $sql .= " ORDER BY customer.customer_id";

        return $this->paginate($sql, $query_values);
    }

    public function getCustomerFilms(int $customer_id, array $filters = [])
    {
        $query_values = [];

        $sql = "SELECT film.*, category.name AS category_name, rental.rental_date, rental.return_date
                    FROM customer
                    JOIN rental ON customer.customer_id = rental.customer_id
                    JOIN inventory ON inventory.inventory_id = rental.inventory_id
                    JOIN film ON film.film_id = inventory.film_id
                    JOIN film_category ON film.film_id = film_category.film_id
                    JOIN category ON category.category_id = film_category.category_id
                    WHERE 1";

        if(isset($filters['rental_date']))
        {
            $sql .= " AND rental.rental_date >= :rental_date ";
            $query_values[":rental_date"] = $filters['rental_date'];
        }

        if(isset($filters['return_date']))
        {
            $sql .= " AND rental.return_date <= :return_date ";
            $query_values[":return_date"] = $filters['return_date'];
        }

        if(isset($filters['rating']))
        {
            $sql .= " AND rating LIKE CONCAT(:rating, '%') ";
            $query_values[":rating"] = $filters['rating'];
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

        $sql .= " AND customer.customer_id = :customer_id ORDER BY film.film_id";

        return $this->paginate($sql, [":customer_id"=> $customer_id] + $query_values);
    }

    public function getCustomerById(int $customer_id)
    {
        $sql = "SELECT customer_id, first_name, last_name
                    FROM customer
                    WHERE customer.customer_id = :customer_id";

        return $this->run($sql, [":customer_id"=> $customer_id])->fetch();
    }

    public function updateCustomer(array $customer, array $columns)
    {
        return $this->update('customer', $customer, $columns);
    }

    public function deleteCustomer(int $customer_id)
    {
        return $this->deleteById('customer', 'customer_id', $customer_id);
    }
}

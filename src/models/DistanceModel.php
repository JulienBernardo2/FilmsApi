<?php
namespace Vanier\Api\Models;
use Vanier\Api\models\BaseModel;

class DistanceModel extends BaseModel
{ 
    public function __construct() 
    {
        parent:: __construct();
    }

    public function getDistanceFrom(string $postal_code)
    {
        $sql = "SELECT  ca_codes.latitude, ca_codes.longitude 
                FROM ca_codes
                WHERE postal_code=:postal_code";

        return $this->run($sql, [":postal_code"=> $postal_code])->fetch();
    }

    public function getDistanceTo(string $postal_code)
    {
        $sql = "SELECT  ca_codes.latitude, ca_codes.longitude 
                FROM ca_codes
                WHERE postal_code=:postal_code";

        return $this->run($sql, [":postal_code"=> $postal_code])->fetch();
    }
}
<?php
namespace Vanier\Api\Models;
use Vanier\Api\models\BaseModel;

class LanguagesModel extends BaseModel
{   
    public function __construct() 
    {
        parent:: __construct();
    }

    public function getLanguages()
    {   
        $sql = "SELECT *
                    FROM language
                    ";

        //Returns the results of the query
        return $this->run($sql)->fetchAll();
    }
}
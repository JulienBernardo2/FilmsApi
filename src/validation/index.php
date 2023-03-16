<?php

require_once("validation/Validator.php");

use Vanier\Api\Validations\Validator;

// NOTE: 
// @see:https://github.com/vlucas/valitron

echo "Validation in progress... <br>";

//TODO: To run the remaining tests, uncomment the following
//      functions calls.
testValidatePagingParams();
//testValidatePersonInfo();
//testValidateArray();
//testSingleValue();

function testValidatePagingParams()
{
    // The array containing the data to be validated.
    $data = array(
        "page" => '1',
        "page_size" => '55',
    );
    // An array element can be associated with one or more validation rules. 
    // Validation rules must be wrapped in an associative array where:
    // NOTE: 
    //     key => must be an existing key  in the data array to be validated. 
    //     value => array of one or more rules.    
    $rules = array(
        'page' => [
            'required',
            'numeric',
            ['min', 1]
        ],
        'page_size' => [
            'required',
            'integer',
            ['min', 5],
            ['max', 50]
        ]
    );

    // Create a validator and override the default language used in expressing the error messages.
    $validator = new Validator($data, [], 'fr');
    // Important: map the validation rules before calling validate()
    $validator->mapFieldsRules($rules);
    if ($validator->validate()) {
        echo "<br> Valid data!";
        return true;
    } else {
        //var_dump($validator->errors());        
        echo $validator->errorsToString();
        echo '<br>';
        echo $validator->errorsToJson();
        return false;
    }
}

function testValidatePersonInfo()
{
    // The array containing the data to be validated.
    $data = array(
        "fist_name" => "Ladybug",
        "last_name" => "Bumblebee",
        "age" =>  '9',
        "price" =>  '389.53',
        "oi" =>  '5',
        "dob" =>  '1-2022-05',
    );
    // An array element can be associated with one or more validation rules. 
    // Validation rules must be wrapped in an associative array where:
    // key => must be an existing key  in the data array to be validated. 
    // value => array of one or more rules.    
    $rules = array(
        'fist_name' => array(
            'required',
            array('lengthMin', 4)
        ),
        'last_name' => array(
            'required',
            array('lengthBetween', 1, 4)
        ),
        'age' => [
            'required',
            'integer',
            ['min', 18]
        ],
        'dob' => [
            'required',
            ['dateFormat', 'Y-m-d']
        ],
        'oi' => [
            'required',
            ['equals', 'Oye']
        ]
    );

    $validator = new Validator($data);
    // Important: map the validation rules before calling validate()
    $validator->mapFieldsRules($rules);
    if ($validator->validate()) {
        echo "<br> Valid data!";
    } else {
        //var_dump($validator->errors());
        //print_r($validator->errors());
        echo $validator->errorsToString();
        echo $validator->errorsToJson();
    }
}
function testSingleValue()
{
    // Validate a single value.
    // The value must be passed as an array. 
    $value = '33r';
    $validator = new Validator(['age' => $value]);
    $validator->rule('integer', 'age');
    if ($validator->validate()) {
        echo "<br> Valid data!";
    } else {
        //var_dump($validator->errors());
        //print_r($validator->errors());
        echo $validator->errorsToString();
        echo '<br>';
        echo $validator->errorsToJson();
    }
}
function testValidateArray()
{
    $data = array(
        "fist_name" => "Ladybug",
        "last_name" => "Bumblebee",
        "age" =>  17,
        "price" =>  '389.53',
        "oi" =>  '5',
    );

    $rules = [
        'integer' => [
            'age'
        ],
        // We can apply the same rule to multiple elements.
        'required' => [
            'fist_name', 'last_name', 'age', 'price'
        ],
        // Validate the max length of list of elements.
        'lengthMax' => array(
            array('fist_name', 5),
            array('last_name', 5)
        ),
        'numeric' => [
            'price'
        ],
        'min' => [
            ['oi', 14]
        ]
    ];
    // Change the default language to French.
    //$validator = new Validator($data, [], "fr");
    $validator = new Validator($data);
    $validator->rules($rules);

    if ($validator->validate()) {
        echo "Valid data!";
    } else {
        //var_dump($validator->errors());
        //echo implode('|', $validator->errors());
        print_r($validator->errors());
    }
}

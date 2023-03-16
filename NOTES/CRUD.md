# Implementing CRUD
    Needs A
    1. Controller
    2. A model
    3. Callback
    4. Route with the method
    5. Adding middleware
    6. Validate data

    Slim framework documentation: <https://www.slimframework.com/>
    Status codes <https://developer.mozilla.org/en-US/docs/Web/HTTP/Status>
    Callbacks must be named with the word "handle" prefix

# Test routes
    Add an echo and exit call, and return the response

# Validations
    Check the format
    Check the data type
    Check if it exists for update and delete

    In php you can use the following forms to perform validation:
        Regex
        Built in php native function (check php documentation to see all <https://www.php.net/docs.php>)
            is_*
            ctype_*
            filter_var

    What to validate
        Numbers: int (in a range, must be positive or negative, %2 = 0), float (in a range, must be positive or negative)
        String: non empty, string only containing [a - z or A - Z], check structure example email or phone number
        Array: non empty, array contains the identifier
    
    Data can be coming in from the URI, parameters, or the body of the request

    search in schema for restrict, replace restrict with cascade, ss_city foreign key (city_id), crtlh to replace'

    How 

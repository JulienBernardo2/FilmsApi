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

# Interfaces
    It can be seen as a clue
    Types of interface like UI, API

    API is a that exposes different resources    
    RPC allows the client to talk to a program that exposes public methods or procedures

    Advantages of webservices, code re-use, easily accesible, easy integration

# Logging

    Monolog for php
    Purpose of logging : 
    What does it do : we can log errors
    Things to log : ip adresses, resources being extracted, errors, security rated attacks, token authentication, number of requests


Identity Management
    It is a type of security layer on top of the application to control who is using the application and control access to certain pages.
    Resource that allows client application to create account and get a token to have access to the other resources

    Workflow
        client app has a create account : credentials, username, email, password
        Client app will send a post request to the generate a token
        web service /account : give the token /token

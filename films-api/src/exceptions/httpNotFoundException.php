<?php
namespace Vanier\Api\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpNotFoundException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 404;

    /**
     * @var string
     */
    protected $message = 'The server cannot find the requested resource.';
    protected $title = "404 - Not Found";
}
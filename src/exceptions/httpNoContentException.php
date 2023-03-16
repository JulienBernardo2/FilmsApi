<?php

namespace Vanier\Api\Exceptions;

use Slim\Exception\HttpSpecializedException;

class HttpNoContentException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 204;

    /**
     * @var string
     */
    protected $message = 'There is no content for the given ID';
}

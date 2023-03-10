<?php
namespace Vanier\Api\Exceptions;

use Slim\Exception\HttpSpecializedException;

class httpUnprocessableContentException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 422;

    /**
     * @var string
     */
    protected $message = 'The server was unable to process the contained instructions';

    protected $title = '422 Unprocessable content';
}
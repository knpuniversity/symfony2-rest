<?php

namespace AppBundle\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiProblemException extends HttpException
{
    private $apiProblem;

    public function __construct(ApiProblem $apiProblem, $statusCode, $message = null, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->apiProblem = $apiProblem;

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}

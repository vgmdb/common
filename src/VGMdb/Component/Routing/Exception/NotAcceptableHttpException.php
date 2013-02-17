<?php

namespace VGMdb\Component\Routing\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\ExceptionInterface;

class NotAcceptableHttpException extends HttpException implements ExceptionInterface
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct(406, $message, $previous, array(), $code);
    }
}

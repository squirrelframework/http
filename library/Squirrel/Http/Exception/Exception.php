<?php

namespace Squirrel\Http\Exception;

use Squirrel\Http\Response;

/**
 * @package Squirrel\Http\Exception;
 * @author Valérian Galliat
 */
class Exception extends \Exception
{
    /**
     * @throws \InvalidArgumentException If given code is not a valid HTTP status code.
     * @param integer $code HTTP status code.
     * @param string $message Optional message.
     */
    public function __construct($code, $message = null)
    {
        if (!isset(Response::$statusTexts[$code])) {
            throw new \InvalidArgumentException(sprintf('Given code %s is not a valid HTTP status code.', $code));
        }

        parent::__construct($message, $code);
    }
}

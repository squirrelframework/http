<?php

namespace Squirrel\Http\Exception;

/**
 * @package Squirrel\Http\Exception;
 * @author Valérian Galliat
 */
class NotFoundException extends Exception
{
    /**
     * @param string $message Optional message.
     */
    public function __construct($message = null)
    {
        parent::__construct(404, $message);
    }
}

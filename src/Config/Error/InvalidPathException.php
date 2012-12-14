<?php

namespace Config\Error;

use Config\ConfigException;

class InvalidPathException extends  \InvalidArgumentException implements 
    ConfigException
{
    /**
     * Default constructor
     *
     * @param string $path    Invalid path
     * @param string $message Optional message
     */
    public function __construct($path, $message = null)
    {
        if (null === $message) {
            parent::__construct(sprintf("Path '%s' is invalid", $path));
        } else {
            parent::__construct(sprintf("Path '%s' is invalid: %s", $path, $message));
        }
    }
}

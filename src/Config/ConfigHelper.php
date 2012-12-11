<?php

namespace Config;

final class ConfigHelper
{
    /**
     * Path separator.
     */
    const PATH_SEPARATOR = '.';

    /**
     * Pure performance helper.
     */
    const EMPTY_SEGMENT = '..';

    /**
     * Latest error message
     *
     * @var string
     */
    private static $lastError = null;

    /**
     * Get latest error message
     *
     * @return string Latest error message
     */
    public static function getLastError()
    {
        return self::$lastError;
    }

    /**
     * Set latest error message
     *
     * @param string $message Error message
     */
    public static function setLastError($message)
    {
        self::$lastError = $message;
    }

    /**
     * Tell if the given path is valid
     *
     * @param string $path Path to test
     *
     * @return bool        True if path is valid
     */
    public static function isValidPath($path)
    {
        if (0 === ($len = strlen($path))) {
            self::$lastError = "Path is empty";
            return false;
        } else if (false === ($pos = strpos($path, self::PATH_SEPARATOR))) {
            return true;
        } else if (self::PATH_SEPARATOR === $path[0]) {
            self::$lastError = sprintf("Path starts with %s", self::PATH_SEPARATOR);
            return false;
        } else if (self::PATH_SEPARATOR === $path[$len - 1]) {
            self::$lastError = sprintf("Path ends with %s", self::PATH_SEPARATOR);
            return false;
        } else if (false !== strpos($path, self::EMPTY_SEGMENT)) {
            self::$lastError = "Path contains one or more empty segment";
            return false;
        } else {
            return true;
        }
    }
}

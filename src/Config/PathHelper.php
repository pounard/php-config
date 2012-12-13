<?php

namespace Config;

use Config\Error\InvalidPathException;

final class PathHelper
{
    /**
     * Path separator
     */
    const PATH_SEPARATOR = '.';

    /**
     * Pure performance helper.
     */
    const EMPTY_SEGMENT = '..';

    /**
     * Name validation PCRE regex
     *
     * FIXME: Fix this
     */
    const VALID_NAME_RE = '/^
        [a-z]+        # Name must start with a lowercased letter
        [a-zA-Z-0-9]* # Name cannot contain anything else than...
        [^-]          # Name cannot end with -
        $/imsx';

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
     * FIXME: Ensure that names can only contain letters, numbers and -
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

    /**
     * Get last path segment
     *
     * @param string $path Path
     *
     * @return string      Last path segment
     *
     * @throws InvalidPathException If path is invalid
     */
    public static function getLastSegment($path)
    {
        if (!self::isValidPath($path)) {
            throw new InvalidPathException($path, self::getLastError());
        }

        if (false !== ($pos = strrpos($path, self::PATH_SEPARATOR))) {
            return substr($path, $pos + 1);
        } else {
            return $path;
        }
    }

    /**
     * Join one or more path together
     *
     * @param ...     String path list
     *
     * @return string New path
     */
    public static function join()
    {
        return implode(self::PATH_SEPARATOR, func_get_args());
    }
}

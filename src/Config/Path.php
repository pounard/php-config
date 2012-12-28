<?php

namespace Config;

use Config\Error\InvalidPathException;

final class Path
{
    /**
     * Path separator
     */
    const SEPARATOR = '/';

    /**
     * Pure performance helper.
     */
    const EMPTY_SEGMENT = '//';

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
    static public function setLastError($message)
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
    static public function isValid($path)
    {
        if (0 === ($len = strlen($path))) {
            self::$lastError = "Path is empty";
            return false;
        } else if (false === ($pos = strpos($path, self::SEPARATOR))) {
            return true;
        /* } else if (self::SEPARATOR === $path[0]) {
            self::$lastError = sprintf("Path starts with %s", self::SEPARATOR);
            return false; */
        } else if (self::SEPARATOR === $path[$len - 1]) {
            self::$lastError = sprintf("Path ends with %s", self::SEPARATOR);
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
        if (!self::isValid($path)) {
            throw new InvalidPathException($path, self::getLastError());
        }

        if (false !== ($pos = strrpos($path, self::SEPARATOR))) {
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
    static public function join()
    {
        $args = func_get_args();

        // Clean leading separators (which is valid)
        foreach ($args as &$arg) {
            if (0 === strpos($arg, self::SEPARATOR)) {
                $arg = substr($arg, 1);
            }
        }

        return implode(self::SEPARATOR, $args);
    }

    /**
     * Ensures that path is valid and clean leading separator
     *
     * @param string $path Path
     *
     * @return string      Trimmed path, false if invalid
     */
    static public function trim($path)
    {
        if (!Path::isValid($path)) {
            return false;
        } else if (0 === strpos($path, Path::SEPARATOR)) {
            return substr($path, 1);
        } else {
            return $path;
        }
    }
}

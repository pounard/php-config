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
    const _EMPTY_SEGMENT = '..';

    /**
     * Tell if the given path is valid
     *
     * @param string $path Path to test
     * @return bool        True if path is valid
     */
    public static function isValidPath($path)
    {
        if (0 === ($len = strlen($path))) {
            throw new InvalidPathException($path, "Path is empty");
        } else if (false === ($pos = strpos($path, self::PATH_SEPARATOR))) {
            return true;
        } else if (self::PATH_SEPARATOR === $path[0]) {
            throw new InvalidPathException($path, sprintf("Path starts with %s", self::PATH_SEPARATOR));
        } else if (self::PATH_SEPARATOR === $path[$len - 1]) {
            throw new InvalidPathException($path, sprintf("Path ends with %s", self::PATH_SEPARATOR));
        } else if (false !== strpos($path, self::_EMPTY_SEGMENT)) {
            throw new InvalidPathException($path, "Path contains one or more empty segment");
        }

        return true;
    }
}

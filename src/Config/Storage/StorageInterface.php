<?php

namespace Config\Storage;

use Config\ConfigType;

/**
 * Give a simple key/value oriented storage interface
 *
 * Consider path here as being a raw string key, but please be careful with
 * tree handling, some methods needs the storage backend to be able to list
 * a complete specific subtree
 *
 * If any error happens in any method execution, the error code must be kept
 * into memory until next method call. In case of success, error code must be
 * reset to SUCCESS.
 */
interface StorageInterface
{
    /**
     * Last operation succeeded
     */
    const SUCCESS = 0;

    /**
     * Expected type mismatch the one stored
     */
    const ERROR_TYPE_MISMATCH = 10;

    /**
     * Given path is invalid
     */
    const ERROR_PATH_INVALID = 20;

    /**
     * Path does not exist while read() or isWritable() is called
     */
    const ERROR_PATH_DOES_NOT_EXIST = 30;

    /**
     * Cannot determine operation status because operation is async or unsafe
     * or backend didn't returned anything
     */
    const STATUS_UNKNOWN = 40;

    /**
     * Get last error code
     *
     * @return int One of the StorageInterface::ERROR_* constant
     */
    public function getLastStatus();

    /**
     * Read a single value
     *
     * @param string $path      Path
     * @param int $expectedType ConfigType constant
     *
     * @return mixed            Value if type matches and value exists
     *                          else null
     */
    public function read($path, $expectedType);

    /**
     * Tell if path exists with a value
     *
     * @param string $path Path
     *
     * @return boolean     True if path is writable
     */
    public function exists($path);

    /**
     * Tell if path is writable
     *
     * @param string $path Path
     *
     * @return boolean     True if path is writable
     */
    public function isWritable($path);

    /**
     * Write a single value
     *
     * @param string $path Path
     * @param mixed $value Value
     * @param int $type    ConfigType constant
     * @param string $safe If set to false unsafe or asynchronous operations
     *                     are allowed and key might now written
     */
    public function write($path, $value, $type = ConfigType::MIXED, $safe = true);

    /**
     * Delete one or more entries
     *
     * @param string|array $path If a string is given, it will be considered
     *                           as a single entry, if an array is given it
     *                           will be considered as a list of path
     * @param string $safe       If set to false unsafe or asynchronous
     *                           operations are allowed and key might not be
     *                           deleted
     */
    public function delete($path, $safe = true);

    /**
     * Get existing keys list
     *
     * @param string $path Root path from which to lookup
     *
     * @return array       path list (always absolute even when a root path is
     *                     specified)
     */
    public function getKeys($path = null);
}

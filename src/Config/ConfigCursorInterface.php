<?php

namespace Config;

use Config\Error\InvalidPathException;
use Config\Schema\SchemaAwareInterface;
use Config\Schema\EntrySchemaInterface;

/**
 * Defines a node in the configuration registry
 *
 * An entry is a key/value pair
 * A section is a subtree containing entries and sections
 *
 * \Traversable will iterate over all of sections and entries
 *
 * \Countable will count the total number of children, section and entries to
 * be in sync with the \Traversable interface
 *
 * \ArrayAccess interface will give you sugar candy aliases for get(), set(),
 * delete() and has() methods
 */
interface ConfigCursorInterface extends
    \Countable,
    \ArrayAccess,
    \Traversable,
    SchemaAwareInterface
{
    /**
     * Tells if this instance is root
     *
     * @return bool True if this instance is root
     */
    public function isRoot();

    /**
     * Does the current is not root but has lost its parent reference
     *
     * In most cases if this returns true you're screwed, except if you mocked
     * up a config cursor for a very specific usage
     *
     * @return bool True if instance is not root but has no parent
     */
    public function isOrphaned();

    /**
     * Get this instance full path including its key
     *
     * @return string Can be null if instance is orphaned or root
     */
    public function getPath();

    /**
     * Return this instance local key name
     *
     * @return string Can be null if instance is orphaned or root
     */
    public function getKey();

    /**
     * Get cursor for specified subpath
     *
     * @param string $path           Relative path from this instance
     *
     * @return ConfigCursorInterface Cursor to relative path
     *
     * @throws InvalidPathException  If path is invalid, does not exists or is
     *                               a value instead
     */
    public function getCursor($path);

    /**
     * Does the specified ebtry exists
     *
     * @param string $path          Relative path to check for existence
     * @return bool                 True if the value exists
     *
     * @throws InvalidPathException If path is invalid 
     */
    public function has($path);

    /**
     * Get a single entry value
     *
     * @param string $path          Path for which to get data
     *
     * @return mixed                Whatever exists at the specified path per
     *                              default this will never throw exception if
     *                              path syntax is incorrect
     */
    public function get($path);

    /**
     * Set value at specified path or key
     *
     * @param string $path Path in which to set the value
     * @param mixed $value Single value
     */
    public function set($path, $value);

    /**
     * Delete entry
     *
     * @param string $path Path
     *
     * @throws InvalidPathException If path does not exist or is not an entry
     */
    public function delete($path);

    /**
     * Self introspect full configuration from this cursor and return it as a
     * multidimensional array mapped upon the existing tree, values being all
     * null or scalar values
     *
     * @return array Array representation of this cursor tree
     */
    public function toArray();
}

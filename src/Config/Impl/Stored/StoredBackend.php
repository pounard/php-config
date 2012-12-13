<?php

namespace Config\Impl\Stored;

use Config\ConfigBackendInterface;
use Config\Impl\AbstractCursor;
use Config\Impl\PassThroughCursor;
use Config\Storage\StorageInterface;

/**
 * Most simple implementation of configuration backend of all, uses the simple
 * key/value storage interface as backend, keeps no cache, and uses the schema
 * for browsing instead of relying on real data. This fits whenever you have
 * a complete schema and a fast backend.
 *
 * Storage based configuration backend will inherit due to storage interface
 * from a stricter typing and type checks.
 *
 * Using such implementation, only simple accessors and setters (get(), set(),
 * has() and delete()) will be fast and optimized, everything else may suffer
 * from performance problems due to heavy coupling with the schema: any normal
 * site runtime should never attempt metadata introspection.
 *
 * Also consider using a fast implementation of the schema browser (at least
 * a fast implementation for accessing single entries) when using with this
 * storage based config implementation: if your schema browser is fast, this
 * implementation will be as fast too.
 *
 * Another performance consideration, while this instance will be as fast as
 * the backend and schema can be together, it is also unadvised to use cursors
 * for any other thing than pure administrative introspection, since they will
 * only act as a proxy toward this instance.
 */
class StoredBackend extends AbstractCursor implements ConfigBackendInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * Default constructor
     *
     * @param StorageInterface $storage Storage backend
     * @param string $rootPath          Root path within the storage if any
     */
    public function __construct(
        StorageInterface $storage,
        $rootPath = null)
    {
        $this->storage = $storage;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::isRoot()
     */
    public function isRoot()
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::isOrphaned()
     */
    public function isOrphaned()
    {
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::isReadonly()
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getPath()
     */
    public function getPath()
    {
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getKey()
     */
    public function getKey()
    {
        return null;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getCursor()
     */
    public function getCursor($path)
    {
        return new PassThroughCursor($this, $path);
    }

    /**
     * Return an iterator of sections under this cursor
     *
     * @return \Traversable Values given by this iterator will be a set of
     *                      ConfigCursorInterface instances, keys will be local
     *                      section names. It can be empty
     */
    public function getSections()
    {
        // Must rely on schema
        throw new \Exception("Not implemented yet");
    }

    /**
     * Return an iterator of values under this cursor
     *
     * @return \Traversable Values given by this iterator will be scalar values
     *                      or list of scalar values. It can be empty
     */
    public function getValues()
    {
        // Must rely on schema
        throw new \Exception("Not implemented yet");
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::has()
     */
    public function has($path)
    {
        return $this->storage->exists($path);
    }

    /**
     * Get a single entry value
     *
     * @param string $path          Path for which to get data
     *
     * @return mixed                Whatever exists at the specified path per
     *                              default this will never throw exception if
     *                              path syntax is incorrect
     */
    public function get($path)
    {
        // Must rely on schema
        throw new \Exception("Not implemented yet");
    }

    /**
     * Set value at specified path or key
     *
     * @param string $path Path in which to set the value
     * @param mixed $value Single value
     */
    public function set($path, $value)
    {
        // Must rely on schema
        throw new \Exception("Not implemented yet");
    }

    /**
     * Delete entry
     *
     * @param string $path Path
     *
     * @throws InvalidPathException If path does not exist or is not an entry
     */
    public function delete($path)
    {
        return $this->storage->delete($path);
    }

    /**
     * Self introspect full configuration from this cursor and return it as a
     * multidimensional array mapped upon the existing tree, values being all
     * null or scalar values
     *
     * @return array Array representation of this cursor tree
     */
    public function toArray()
    {
        // Must rely on schema
        throw new \Exception("Not implemented yet");
    }
}

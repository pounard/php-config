<?php

namespace Config\Impl;

use Config\ConfigCursorInterface;
use Config\ConfigType;
use Config\Error\InvalidPathException;
use Config\Path;
use Config\Schema\SchemaInterface;
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
 * add another level of indirection.
 *
 * This instance can be strict or less strict. If the strict mode is enabled,
 * strict checks will be done at both read and write time, if strict mode is
 * disabled, only the write operations will be strict checked. In case no
 * schema is set, this instance will never do strict operations and will
 * always store values trying to determine their type dynmically (which is
 * bad).
 */
class StoredBackend extends AbstractCursor implements ConfigCursorInterface
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var bool
     */
    private $strict = false;

    /**
     * @var bool
     */
    private $safe = true;

    /**
     * @var string
     */
    private $rootPath = null;

    /**
     * @var string
     */
    private $path = null;

    /**
     * Default constructor
     *
     * @param StorageInterface $storage Storage backend
     * @param SchemaInterface $schema   Schema browser, if given this instance
     *                                  will do strict checks upon the schema
     *                                  at both read and write time on the
     * @param string $rootPath          Root path within the storage if any
     * @param bool $strict              If set to false, strict mode will not
     *                                  be enabled even if a schema is given
     * @param bool $safe                If set to false unsafe operations will
     *                                  be allowed with the storage
     */
    public function __construct(
        StorageInterface $storage,
        SchemaInterface $schema = null,
        $rootPath               = null,
        $strict                 = true,
        $safe                   = true,
        $path                   = null)
    {
        $this->storage  = $storage;
        $this->safe     = (bool)$safe;

        if (null !== $schema) {
            $this->setSchema($schema);
            $this->strict = (bool)$strict;
        }

        if (null !== $rootPath && !($this->rootPath = Path::trim($rootPath))) {
            throw new InvalidPathException($rootPath);
        }
        if (null !== $path && !($this->path = Path::trim($path))) {
            throw new InvalidPathException($path);
        }
    }

    /**
     * Tell if strict mode is enabled
     *
     * @return bool True if strict mode is enabled
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Set strict mode
     *
     * @param bool $strict True for enabling strict mode, false for disabling
     */
    public function setStrict($strict)
    {
        if ($this->hasSchema()) {
            $this->strict = (bool)$strict;
        } else if ($strict) {
            throw new \LogicException(
                "Cannot set strict mode when no schema is set");
        }
    }

    /**
     * Tell if safe mode is enabled
     *
     * @return bool True if write operations are safe
     */
    public function isSafe()
    {
        return $this->safe;
    }

    /**
     * Set safe mode
     *
     * @param bool $safe False for disabling the safe mode
     */
    public function setSafe($safe)
    {
        $this->safe = (bool)$safe;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::isRoot()
     */
    public function isRoot()
    {
        return null === $this->path;
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
     * @see \Config\ConfigCursorInterface::getPath()
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getKey()
     */
    public function getKey()
    {
        if (null === $this->path) {
            return null;
        } else {
            return Path::getLastSegment($this->path);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getCursor()
     */
    public function getCursor($path)
    {
        if (!$relPath = Path::trim($path)) {
            throw new InvalidPathException($path);
        }

        if (null === $this->rootPath) {
            $rootPath = $relPath;
        } else {
            $rootPath = Path::join($this->rootPath, $relPath);
        }

        return new self(
            $this->storage,
            $this->getSchema(),
            $rootPath,
            $this->strict,
            $this->safe,
            $relPath);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::has()
     */
    public function has($path)
    {
        if (null !== $this->rootPath) {
            $path = Path::join($this->rootPath, $path);
        }
        if (!Path::isValid($path)) {
            throw new InvalidPathException($path);
        }

        return $this->storage->exists($path);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::get()
     */
    public function get($path)
    {
        if (null !== $this->rootPath) {
            $path = Path::join($this->rootPath, $path);
        }
        if (!Path::isValid($path)) {
            throw new InvalidPathException($path);
        }

        if ($this->strict && $this->hasSchema()) {
            $entry = $this->getSchema()->getEntrySchema($path);

            return $this->storage->read($path, $entry->getType());
        } else {
            return $this->storage->read($path, ConfigType::MIXED);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::set()
     */
    public function set($path, $value)
    {
        if (null !== $this->rootPath) {
            $path = Path::join($this->rootPath, $path);
        }
        if (!Path::isValid($path)) {
            throw new InvalidPathException($path);
        }

        if ($this->hasSchema()) {
            $type = $this->getSchema()->getEntrySchema($path)->getType();
        } else {
            $type = ConfigType::getType($value);
        }

        $this->storage->write($path, $value, $type, $this->safe);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::delete()
     */
    public function delete($path)
    {
        if (null !== $this->rootPath) {
            $path = Path::join($this->rootPath, $path);
        }
        if (!Path::isValid($path)) {
            throw new InvalidPathException($path);
        }

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
        $ret = array();

        if ($this->strict && $this->hasSchema()) {
            throw new \Exception("Not implemented yet");
        } else {
            foreach ($this->storage->getKeys($this->rootPath) as $path) {

                // Derecursification of array building
                $parts = Path::explode($path);
                $current = &$ret;
                while ($key = array_shift($parts)) {
                    $current = &$current[$key];
                }

                $current = $this->storage->read($path, ConfigType::MIXED);
            } 
        }

        return $ret;
    }
}

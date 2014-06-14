<?php

namespace Config\Impl;

use Config\ConfigCursorInterface;
use Config\Path;

/**
 * Implementation of cursor that isn't storage specific and completely rely
 * upon the given backend. This fits for all usages, except if you really
 * are bored to death and want to implement your own.
 *
 * For example, the memory backend that serves the only purposes of validating
 * the unit tests does not uses it, because I was bored when I wrote it, but
 * any other serious implementation will probably use it, except if you have
 * real performance problems due to your storage mecanism architecture.
 *
 * This implementation is incomplete and cannot introspect itself.
 */
class PassThroughCursor extends AbstractSchemaAware implements
    \IteratorAggregate,
    ConfigCursorInterface
{
    /**
     * @var ConfigCursorInterface
     */
    protected $backend;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $key;

    /**
     * Default constructor
     *
     * @param ConfigCursorInterface $backend Cursor backend
     * @param string $path                   Cursor path
     */
    public function __construct(
        ConfigCursorInterface $backend,
        $path)
    {
        $this->backend = $backend;
        $this->path = $path;

        $this->key = Path::getLastSegment($this->path);
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count()
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::isRoot()
     */
    public function isRoot()
    {
        return false;
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
        return $this->key;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getCursor()
     */
    public function getCursor($path)
    {
        return $this->backend->getCursor(Path::join($this->path, $path));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::has()
     */
    public function has($path)
    {
        return $this->backend->has(Path::join($this->path, $path));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::get()
     */
    public function get($path)
    {
        return $this->backend->get(Path::join($this->path, $path));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::set()
     */
    public function set($path, $value)
    {
        return $this->backend->set(Path::join($this->path, $path), $value);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::delete()
     */
    public function delete($path)
    {
        return $this->backend->delete(Path::join($this->path, $path));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::toArray()
     */
    public function toArray()
    {
        throw new \Exception("Not implemented yet");
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists($offset)
    {
        return $this->backend->has(Path::join($this->path, $offset));
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->backend->get(Path::join($this->path, $offset));
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        return $this->backend->set(Path::join($this->path, $offset), $value);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        return $this->backend->delete(Path::join($this->path, $offset));
    }
}

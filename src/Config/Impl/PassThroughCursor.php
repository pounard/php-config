<?php

namespace Config\Impl;

use Config\ConfigBackendInterface;
use Config\ConfigCursorInterface;
use Config\PathHelper;

/**
 * Implementation of cursor that isn't storage specific and completely rely
 * upon the given backend. This fits for all usages, except if you really
 * are bored to death and want to implement your own.
 *
 * For example, the memory backend that serves the only purposes of validating
 * the unit tests does not uses it, because I was bored when I wrote it, but
 * any other serious implementation will probably use it, except if you have
 * real performance problems due to your storage mecanism architecture.
 */
final class PassThroughCursor extends AbstractSchemaAware implements
    \IteratorAggregate,
    ConfigCursorInterface,
    \Countable
{
    /**
     * @var ConfigBackendInterface
     */
    private $backend;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $key;

    /**
     * Default constructor
     *
     * @param ConfigBackendInterface $backend Cursor backend
     * @param string $path                    Cursor path
     */
    public function __construct(
        ConfigBackendInterface $backend,
        $path)
    {
        $this->backend = $backend;
        $this->path = $path;

        $this->key = PathHelper::getLastSegment($this->path);
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
     * @see \Config\ConfigCursorInterface::isReadonly()
     */
    public function isReadonly()
    {
        return $this->backend->isReadonly();
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
        return $this->backend->getCursor(
            PathHelper::join($this->path, $path));
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
        throw new \Exception("Not implemented yet");
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::has()
     */
    public function has($path)
    {
        return $this->backend->has(
            PathHelper::join($this->path, $path));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::get()
     */
    public function get($path)
    {
        return $this->backend->get(
            PathHelper::join($this->path, $path));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::set()
     */
    public function set($path, $value)
    {
        return $this->backend->set(
            PathHelper::join($this->path, $path), $value);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::delete()
     */
    public function delete($path)
    {
        return $this->backend->delete(
            PathHelper::join($this->path, $path));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getEntrySchema()
     */
    public function getEntrySchema($path)
    {
        return $this->backend->getEntrySchema(
            PathHelper::join($this->path, $path));
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
        return $this->backend->has(
            PathHelper::join($this->path, $offset));
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet($offset)
    {
        return $this->backend->get(
            PathHelper::join($this->path, $offset));
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value)
    {
        return $this->backend->set(
            PathHelper::join($this->path, $offset), $value);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset($offset)
    {
        return $this->backend->delete(
            PathHelper::join($this->path, $offset));
    }
}

<?php

namespace Config\Impl;

use Config\ConfigCursorInterface;
use Config\ParentNotFoundException;

/**
 * Base implementation that would fit most needs
 */
abstract class AbstractCursor implements
    \IteratorAggregate,
    ConfigCursorInterface
{
    /**
     * @var ConfigCursorInterface
     */
    protected $parentCursor;

    /**
     * @var bool
     */
    protected $readonly = true;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $key;

    /**
     * PHP can't handle the \Traversable interface not being implemented over
     * an abstract class we therefore have no other choice to force one of the
     * possible implementation choice
     *
     * For most people \IteratorAggregate is the easiest one
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        throw new \Exception("You must implement this method");
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::isRoot()
     */
    public function isRoot()
    {
        return $this instanceof ConfigBackendInterface;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::isOrphaned()
     */
    public function isOrphaned()
    {
        return !isset($this->parent) && !$this->isRoot();
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::isReadonly()
     */
    public function isReadonly()
    {
        return $this->readonly;
    }

    /**
     * There is great chances that parent instance actually share the same
     * code base, so this functions won't be protected for it
     *
     * @param string $path Path
     */
    protected function setPath($path)
    {
        $this->path = $path;

        if (false === strpos($path, '.')) {
            $this->key = $path;
        } else {
            $this->key = substr($path, strrpos($path, '.') - 1);
        }
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
     * There is great chances that parent instance actually share the same
     * code base, so this functions won't be protected for it
     *
     * @param ConfigCursorInterface $parent Parent cursor
     */
    protected function setParentCursor(ConfigCursorInterface $parentCursor)
    {
        $this->parentCursor = $parentCursor;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getParentCursor()
     */
    public function getParentCursor()
    {
        if (!isset($this->parent)) {
            if ($this->isRoot()) {
                throw new ParentNotFoundException("Current config instance is root");
            } else {
                throw new ParentNotFoundException("Current config instance is orphaned");
            }
        }

        return $this->parentCursor;
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    final public function offsetExists($offset)
    {
        return $this->has($path);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    final public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    final public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    final public function offsetUnset($offset)
    {
        return $this->delete($offset);
    }
}

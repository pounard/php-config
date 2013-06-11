<?php

namespace Config\Impl;

use Config\ConfigCursorInterface;
use Config\ConfigType;
use Config\Path;

/**
 * Abstract implementation of storage and schema agnostic methods
 */
abstract class AbstractCursor extends AbstractSchemaAware implements
    \IteratorAggregate,
    ConfigCursorInterface
{
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
        throw new \Exception("You must implement the getIterator() method");
    }

    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count()
    {
        $iterator = $this->getIterator();

        if ($iterator instanceof \Countable) {
            return $iterator->count();
        } else {
            throw new \Exception("You must implement the count() method");
        }
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

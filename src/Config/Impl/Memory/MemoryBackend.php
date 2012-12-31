<?php

namespace Config\Impl\Memory;

use Config\ConfigBackendInterface;
use Config\Error\InvalidPathException;
use Config\Impl\AbstractCursor;
use Config\Impl\PassThroughCursor;
use Config\Path;
use Config\Schema\SchemaInterface;

/**
 * Array based implementation of config cursor
 *
 * This implementation can not support list or hashmap value types because it
 * types everything dynamically and works schema less; For example, this:
 * @code
 *   $cursor['a/b/c'] = 12;
 * @endcode
 * Will be equivalent to this:
 * @code
 *   $cursor['a'] = array('b' => array('c' => 12));
 * @endcode
 * Disregarding the original interface documentation
 */
class MemoryBackend extends AbstractCursor implements ConfigBackendInterface
{
    /**
     * Internal data.
     * @var array
     */
    protected $data;

    /**
     * Default constructor
     *
     * @param array &$data   Arbitrary data
     */
    public function __construct(array &$data = null)
    {
        if (null === $data) {
            $this->data = array();
        } else {
            $this->data = &$data;
        }
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
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator(array_keys($this->data));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getCursor()
     */
    public function getCursor($path)
    {
        $ret = &$this->findPath($path);

        if (!is_array($ret)) {
            throw new InvalidPathException("Expected a section, value found instead");
        }

        $cursor = new PassThroughCursor($this, $path);

        return $cursor;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::has()
     */
    public function has($path)
    {
        return !is_array($this->findPath($path));
    }

    /**
     * Return the current internal data pointer
     *
     * @param string $path      
     *     Path to reach
     * @param bool $create          If set to true will create missing sub
     *                              sections
     *
     * @return mixed                Reference to whatever is at the specified
     *                              path, returns an empty array in case of any
     *                              error
     */
    protected function &findPath($path, $create = false)
    {
        if (!$path = Path::trim($path)) {
            throw new InvalidPathException($path);
        }

        $parts   = explode(Path::SEPARATOR, $path);
        $current = &$this->data;
        $error   = array();

        while (!empty($parts)) {
            $key = array_shift($parts);

            // Values can be null hence array_key_exists()
            if (!array_key_exists($key, $current)) {
                if (!$create) {
                    return $error;
                } else {
                    $current[$key] = array();
                }
            } elseif (!empty($parts) && !is_array($current[$key])) {
                return $error;
            }

            $current = &$current[$key];
        }

        return $current;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::get()
     */
    public function get($path)
    {
        $ret = &$this->findPath($path);

        // Array is either an error, either a section, user asked for a value
        if (!is_array($ret)) {
            return $ret;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::set()
     */
    public function set($path, $value)
    {
        $ret = &$this->findPath($path, true);

        if (is_array($ret) && !empty($ret)) {
            throw new InvalidPathException($path, "Excepted a value or nothing, section found instead");
        }

        $ret = $value;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::delete()
     */
    public function delete($path)
    {
        if ($ret = &$this->findPath($path, true)) {
            unset($path);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::toArray()
     */
    public function toArray()
    {
        return $this->data;
    }
}

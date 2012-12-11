<?php

namespace Config\Impl\Memory;

use Config\ConfigHelper;
use Config\Impl\AbstractCursor;
use Config\Impl\DefaultSchema;
use Config\InvalidPathException;

/**
 * Array config does not support list or hashmap value types, since it does
 * everything schema less and typed dynamically
 */
class MemoryCursor extends AbstractCursor implements \IteratorAggregate
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
     * @param bool $readonly True if the current instance is readonly
     */
    public function __construct(array &$data = null, $readonly = false)
    {
        if (null === $data) {
            $this->data = array();
        } else {
            $this->data = &$data;
        }

        $this->readonly = $readonly;
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

        return new MemoryCursor($ret, $this->readonly);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getSections()
     */
    public function getSections()
    {
        $sections = array();

        foreach ($this->data as $key => &$value) {
            if (is_array($value)) {
                $sections[$key] = new MemoryCursor($value, $this->readonly);
            }
        }

        return $sections;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getValues()
     */
    public function getValues()
    {
        $values = array();

        foreach ($this->data as $key => $value) {
            if (!is_array($value)) {
                $values[$key] = $value;
            }
        }

        return $values;
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
     * @param string $path          Path to reach
     * @param bool $create          If set to true will create missing sub
     *                              sections
     *
     * @return mixed                Reference to whatever is at the specified
     *                              path, returns an empty array in case of any
     *                              error
     */
    protected function &findPath($path, $create = false)
    {
        if (!ConfigHelper::isValidPath($path)) {
            throw new InvalidPathException($path, ConfigHelper::getLastError());
        }

        $parts   = explode('.', $path);
        $current = &$this->data;

        while (!empty($parts)) {
            $key = array_shift($parts);

            // Values can be null hence array_key_exists()
            if (!array_key_exists($key, $current)) {
                $current[$key] = array();
                if (!$create) {
                    ConfigHelper::setLastError(sprintf("Expected a section for segment '%s', nothing found instead", $key));
                }
            } elseif (!empty($parts) && !is_array($current[$key])) {
                ConfigHelper::setLastError(sprintf("Expected a section for segment '%s', value found instead", $key));
                return array();
            }

            $current = &$current[$key];
        }

        return $current;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::get()
     */
    public function get($path, $default = null)
    {
        $ret = &$this->findPath($path);

        // Array is either an error, either a section, user asked for a value
        if (is_array($ret)) {
            return $default;
        } else {
            return $ret;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::set()
     */
    public function set($path, $value)
    {
        if ($this->readonly) {
            throw new \LogicException("This cursor is readonly");
        }

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
        if ($this->readonly) {
            throw new \LogicException("This cursor is readonly");
        }

        if ($ret = &$this->findPath($path, true)) {
            unset($path);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::getSchema()
     */
    public function getSchema($path)
    {
        $value = $this->get($path);

        return new DefaultSchema(\ConfigType::getType($value));
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

<?php

namespace Config\Impl\Memory;

use Config\ConfigHelper;
use Config\Impl\AbstractConfig;
use Config\Impl\DefaultSchema;
use Config\InvalidPathException;

class ArrayConfig extends AbstractConfig implements \IteratorAggregate
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
        if (null !== $data) {
          $this->data = $data;
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
        $ret = $this->findPath($path);

        if (!is_array($ret)) {
            throw new InvalidPathException("Expected a section, value found instead");
        }

        return new ArrayConfig($ret, $this->readonly);
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
                $sections[$key] = new ArrayConfig($value, $this->readonly);
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

        foreach ($this->data as $key => &$value) {
            if (!is_array($value)) {
                $values[$key] = &$value;
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
        // FIXME: Could be faster, using an exception for legit behavior is
        // not performance wise.
        try {
            $this->findPath($path);

            if (is_array($path)) {
                return false;
            }
        } catch (InvalidPathException $e) {
            return false;
        }

        return true;
    }

    /**
     * Return the current internal data pointer
     *
     * @param string $path          Path to reach
     * @param bool $create          If set to true will create missing sub
     *                              sections
     *
     * @return mixed                Reference to whatever is at the specified
     *                              path
     *
     * @throws InvalidPathException In case of any error
     */
    protected function &findPath($path, $create = false)
    {
        ConfigHelper::isValidPath($path);

        $parts   = explode('.', $path);
        $current = &$this->data;

        while (!empty($parts)) {
            $key = array_shift($parts);

            if (!array_key_exists($key, $current)) {
                if ($create) {
                    $current[$key] = array();
                } else {
                    throw new InvalidPathException($path, sprintf("Expected a section for segment '%s', nothing found instead", $key));
                }
            } elseif (!empty($parts) && !is_array($current[$key])) {
                throw new InvalidPathException($path, sprintf("Expected a section for segment '%s', value found instead", $key));
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
        if (isset($default)) {
            // Enclose the exception handling into the if for pure performance
            // we don't need the try/catch statement if we have not default.
            // This is for purity matter, I'm not even sure this really change
            // a thing.
            try {
                $ret = $this->findPath($path);
            } catch (InvalidPathException $e) {
                return $default;
            }
        } else {
            $ret = $this->findPath($path);
        }

        if (is_array($ret)) {
            throw new InvalidPathException($path, "Expected a value, section found instead");
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigCursorInterface::set()
     */
    public function set($path, $value)
    {
        $ret = &$this->findPath($path, true);

        if (is_array($ret)) {
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
        throw new \Exception("Not impletement yet");
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

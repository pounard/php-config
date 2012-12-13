<?php

namespace Config\Impl;

use Config\ConfigBackendInterface;
use Config\ConfigCursorInterface;

/**
 * Base implementation that would fit most needs as long as you don't use
 * any specific storage backend implemeting StorageInterface
 */
abstract class AbstractMemoryCursor extends AbstractCursor implements
    ConfigCursorInterface
{
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
}

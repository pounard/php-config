<?php

namespace Config\Impl\Memory;

use Config\ConfigType;
use Config\PathHelper;
use Config\Storage\StorageInterface;

/**
 * Memory implementation of storage backend
 */
class MemoryStorage implements StorageInterface
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @var array
     */
    private $types = array();

    /**
     * @var int
     */
    private $status = StorageInterface::STATUS_UNKNOWN;

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::getLastStatus()
     */
    public function getLastStatus()
    {
        return $this->status;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::read()
     */
    public function read($path, $expectedType)
    {
        $ret = null;

        if (!$path = PathHelper::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else if (isset($this->types[$path])) {
            if ($expectedType === $this->types[$path]) {
                $this->status = StorageInterface::SUCCESS;
                $ret = $this->data[$path];
            } else {
                $this->status = StorageInterface::ERROR_TYPE_MISMATCH;
            }
        } else {
            $this->status = StorageInterface::ERROR_PATH_DOES_NOT_EXIST;
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::exists()
     */
    public function exists($path)
    {
        $ret = false;

        if (!$path = PathHelper::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else {
            $this->status = StorageInterface::SUCCESS;
            $ret = isset($this->types[$path]);
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::isWritable()
     */
    public function isWritable($path)
    {
        return false !== PathHelper::trim($path);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::write()
     */
    public function write($path, $value, $type = ConfigType::MIXED, $safe = true)
    {
        if (!$path = PathHelper::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else {
            // FIXME: Handle type and CAAAAAAAST!!!

            $this->data[$path] = $value;
            $this->types[$path] = $type;

            if ($safe) {
                $this->status = StorageInterface::SUCCESS;
            } else {
                $this->status = StorageInterface::STATUS_UNKNOWN;
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::delete()
     */
    public function delete($path, $safe = true)
    {
        if (!$path = PathHelper::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else {

            unset($this->data[$path], $this->types[$path]);

            if ($safe) {
                $this->status = StorageInterface::SUCCESS;
            } else {
                $this->status = StorageInterface::STATUS_UNKNOWN;
            }
        }
    }
}

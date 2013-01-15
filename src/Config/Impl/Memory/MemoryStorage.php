<?php

namespace Config\Impl\Memory;

use Config\ConfigType;
use Config\Path;
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

        if (!$path = Path::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else if (isset($this->types[$path])) {
            if (ConfigType::MIXED === $expectedType || $expectedType === $this->types[$path]) {
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

        if (!$path = Path::trim($path)) {
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
        return false !== Path::trim($path);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::write()
     */
    public function write($path, $value, $type = ConfigType::MIXED, $safe = true)
    {
        if (!$path = Path::trim($path)) {
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
        if (!$path = Path::trim($path)) {
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

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::getKeys()
     */
    public function getKeys($path = null)
    {
        $ret = array();

        if (null !== $path && !($path = Path::trim($path))) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else {
            if (null === $path) {
                $this->status = StorageInterface::SUCCESS;
                $ret = array_keys($this->data);
            } else {
                foreach ($this->data as $localPath => $value) {
                    if (0 === strpos($localPath, $path) && Path::SEPARATOR === $localPath[strlen($path)]) {
                        $ret[] = $localPath;
                    }
                }

                if (empty($ret)) {
                    $this->status = StorageInterface::ERROR_PATH_DOES_NOT_EXIST;
                } else {
                    $this->status = StorageInterface::SUCCESS;
                }
            }
        }

        if (!empty($ret)) {
            ksort($ret);
        }

        return $ret;
    }
}

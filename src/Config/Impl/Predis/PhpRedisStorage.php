<?php

namespace Config\Impl\Predis;

use Config\ConfigType;
use Config\Impl\ValueSerializer;
use Config\Path;
use Config\Storage\StorageInterface;

/**
 * Implements the Redis storage backend using the Predis PHP library.
 */
class PhpRedisStorage implements StorageInterface
{
    /**
     * Type key in Redis hash
     */
    const KEY_TYPE = 't';

    /**
     * Value key in Redis hash
     */
    const KEY_VALUE = 'v';

    /**
     * Locked key in Redis hash
     */
    const KEY_LOCK = 'l';

    /**
     * Latest status
     *
     * @var int
     */
    private $status = StorageInterface::STATUS_UNKNOWN;

    /**
     * Predis client
     *
     * @var \Redis
     */
    private $client;

    /**
     * @var ValueSerializer
     */
    private $serializer;

    /**
     * Default constructor
     *
     * @param Redis $client PhpRedis client
     */
    public function __construct(\Redis $client)
    {
        $this->client = $client;
        $this->serializer = new ValueSerializer();
    }

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
        } else {

            $data = $this->client->hgetall($path);

            if (empty($data)) {
                $this->status = StorageInterface::ERROR_PATH_DOES_NOT_EXIST;
            } else {

                $type  = (int)$data[self::KEY_TYPE];
                $value = $this->serializer->unserialize($data[self::KEY_VALUE], $type);

                if (ConfigType::MIXED === $expectedType || $type === $expectedType) {
                    $ret = $value;
                    $this->status = StorageInterface::SUCCESS;
                } else {
                    $this->status = StorageInterface::ERROR_TYPE_MISMATCH;
                }
            }
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
            $ret = $this->client->exists($path);
            $this->status = StorageInterface::STATUS_UNKNOWN;
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::isWritable()
     */
    public function isWritable($path)
    {
        // This backend does not support key locking (yet)
        // @todo Implement it
        $this->status = StorageInterface::SUCCESS;

        return true;
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
            // @todo async?
            $this->client->hmset($path, array(
                self::KEY_TYPE  => $type,
                self::KEY_VALUE => $this->serializer->serialize($value, $type),
            ));

            $this->status = StorageInterface::SUCCESS;
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
            if ($this->client->del($path)) {
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
        $ret = null;
        $pattern = null;

        if (null !== $path) {
            if ($path = Path::trim($path)) {
                $pattern = $path . '*';
            } else {
                $this->status = StorageInterface::ERROR_PATH_INVALID;
            }
        } else {
            $pattern = '*';
        }

        if (null !== $pattern) {

            $ret = $this->client->keys($pattern);

            if (is_array($ret)) {
                $this->status = StorageInterface::SUCCESS;
            } else {
                $ret = null;
                $this->status = StorageInterface::STATUS_UNKNOWN;
            }
        }

        return $ret;
    }
}

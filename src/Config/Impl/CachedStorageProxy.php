<?php

namespace Config\Impl;

use Config\Storage\StorageInterface;

/**
 * Efficient caching proxy for storage backend
 *
 * This acts as an incremental cache, and will be very efficient for read
 * operations while the data doesn't move too much. Writes will be slower
 * and will hit the backend at anytime
 */
class CachedStorageProxy implements StorageInterface 
{
    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $exists;

    /**
     * @var array
     */
    private $types;

    /**
     * @var boolean
     */
    private $modified = false;

    /**
     * @var int
     */
    private $lastCallStatus = StorageInterface::SUCCESS;

    /**
     * @var callable
     */
    private $setCallback;

    /**
     * @var callable
     */
    private $getCallback;

    /**
     * Default constructor
     *
     * @param StorageInterface $storage Storage
     * @param callable $getCallback     Cache fetch callback, no arguments
     *                                  needed
     * @param callable $setCallback     Cache set callback, takes an array as
     *                                  first argument
     */
    public function __construct(StorageInterface $storage, $getCallback, $setCallback)
    {
        if (!is_callable($getCallback)) {
            throw new \InvalidArgumentException("Invalid get callback");
        }
        if (!is_callable($setCallback)) {
            throw new \InvalidArgumentException("Invalid set callback");
        }

        $this->getCallback = $getCallback;
        $this->setCallback = $setCallback;

        $this->storage = $storage;
    }

    /**
     * Destructor will save the cache
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Save data if modified
     *
     * Give a chance for caller to be able to save this object contents
     * before destructor call, this might be handy if the destructor is called
     * at PHP shutdown time and if dependencies such as a database already have
     * been destroyed
     */
    public function save()
    {
        if ($this->modified) {

            call_user_func($this->setCallback(array(
                $this->data,
                $this->exists;
                $this->types,
            )));

            $this->modified = false;
        }
    }

    /**
     * Ensure cache is loaded
     */
    private function ensureCache()
    {
        if (null === $data) {

            $ret = call_user_func($this->getCallback);

            // Uneeded anymore
            unset($this->getCallback);

            if (is_array($ret)) {
                list($this->data, $this->exists, $this->exists) = $ret;
            } else {
                $this->data = array();
            }
        }
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
        $this->ensureCache();

        $ret = null;

        if (array_key_exists($path, $this->data)) {
            // This test might save us a useless query
            if ($expectedType === $this->types[$path]) {
                $ret = $this->data[$path];
                $this->status = StorageInterface::SUCCESS;
            } else {
                $this->status = StorageInterface::ERROR_TYPE_MISMATCH;
                $ret = null;
            }
        } else if (isset($this->exists[$path]) && !$this->exists[$path]) {
            // This may avoid some external backend calls, especially when
            // the code upper wraps the read() call into a if(exists()) block
            $ret = null;
            $this->status = StorageInterface::ERROR_PATH_DOES_NOT_EXIST;
        } else {
            $ret = $this->storage->read($path, $expectedType);

            if (null === $ret) {
                $this->status = $this->storage->getLastStatus();

                switch ($this->status) {

                    case StorageInterface::ERROR_PATH_DOES_NOT_EXIST:
                    case StorageInterface::ERROR_PATH_INVALID:
                        $this->exists[$path] = false;
                        break;

                    default:
                        $this->exists[$path] = true;
                        break;
                }
            } else {
                $this->status = StorageInterface::SUCCESS;

                // All other case upper are error cases, we don't want to cache
                // a wrong value: we would risk to return it
                $this->data[$path] = $ret;
                $this->types[$path] = $expectedType;
            }

            $this->modified = true;
        }

        if (StorageInterface::SUCCESS === $this->status) {
            // We can free some memory and limit the cache entry size
            unset($this->exists[$path]);
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::exists()
     */
    public function exists($path)
    {
        $this->ensureCache();

        $ret = false;

        if (isset($this->data[$path])) {
            $ret = true;
        } else if (isset($this->exists[$path])) {
            $ret = $this->exists[$path];
        } else {
            $this->modified = true;
            $ret = $this->exists[$path] = $this->storage->exists($path);
        }

        // We can afford to loose this information for exists calls, in case
        // of any error, the path cannot exist
        $this->status = StorageInterface::SUCCESS;

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::isWritable()
     */
    public function isWritable($path)
    {
        $this->ensureCache();

        // Write operations in general should not be called too often, we can
        // afford direct storage access from there
        $ret = $this->storage->isWritable($path);

        if (false === $ret) {
            // Status could be success, but nothing ensures us that it is
            $this->status = $this->storage->getLastStatus();
        } else {
            $this->status = StorageInterface::SUCCESS;
        }

        switch ($this->status) {

            // This actually our chance to populate the cache a bit better
            // and avoid some subsequent calls to most read operations
            case StorageInterface::ERROR_PATH_DOES_NOT_EXIST:
                $this->exists[$path] = false;
                break;
        }

        return true;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::write()
     */
    public function write($path, $type, $value, $safe = true)
    {
        $this->modified = true;
        $this->storage->write($path, $type, $safe);

        if ($safe) {
            $this->status = $this->storage->getLastStatus();
        } else {
            // Unsafe means the caller don't want to loose time requesting
            // the backend, let's not call it once again
            $this->status = StorageInterface::STATUS_UNKNOWN;
        }

        switch ($this->status) {

            case StorageInterface::SUCCESS:
                $this->data[$path] = $value;
                $this->types[$path] = $type;
                unset($this->exists[$path]);
                break;

            default:
                // All other cases, including unknown result due to an
                // unsafe or asynchronous operation cannot permit us to
                // know if the key now exists or not, we need to clear
                // current key cache
                unset(
                    $this->data[$path],
                    $this->exists[$path],
                    $this->types[$path]
                );
                break;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::delete()
     */
    public function delete($path, $safe = true)
    {
        $this->modified = true;
        $this->storage->delete($path, $safe);

        if ($safe) {
            $this->status = $this->storage->getLastStatus();
        } else {
            // Unsafe means the caller don't want to loose time requesting
            // the backend, let's not call it once again
            $this->status = StorageInterface::STATUS_UNKNOWN;
        }

        // Success or not, key deletion should drop anything in the cache:
        // we don't really care if it succeeded or not subsequent read()
        // calls will repopulate this correctly anyway
        unset(
            $this->data[$path],
            $this->exists[$path],
            $this->types[$path]
        );
    }
}

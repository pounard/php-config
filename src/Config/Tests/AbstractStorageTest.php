<?php

namespace Config\Tests;

use Config\ConfigType;
use Config\Storage\StorageInterface;

abstract class AbstractStorageTest extends \PHPUnit_Framework_TestCase
{
    protected $storage;

    /**
     * Create storage instance
     *
     * @return StorageInterface
     */
    abstract protected function createStorage();

    protected function setUp()
    {
        parent::setUp();

        $this->storage = $this->createStorage();
    }

    public function testGetSet()
    {
        $storage = $this->storage;

        // Set a key, retrieve it
        $this->assertFalse($storage->exists('test1'));
        $storage->write('test1', 42, ConfigType::INT);
        $this->assertTrue($storage->exists('test1'));
        $value = $storage->read('test1', ConfigType::INT);
        $this->assertSame(42, $value);
        $this->assertSame(StorageInterface::SUCCESS, $storage->getLastStatus());

        // Retrieve it with wrong type
        $value = $storage->read('test1', ConfigType::STRING);
        $this->assertNull($value);
        $this->assertSame(StorageInterface::ERROR_TYPE_MISMATCH, $storage->getLastStatus());

        // Retrieve it back
        $value = $storage->read('test1', ConfigType::INT);
        $this->assertSame(42, $value);
        $this->assertSame(StorageInterface::SUCCESS, $storage->getLastStatus());

        // Set another key, retrieve it
        $this->assertFalse($storage->exists('a/b/c'));
        $storage->write('a/b/c', "test", ConfigType::STRING);
        $value = $storage->read('a/b/c', ConfigType::STRING);
        $this->assertSame("test", $value);
        $this->assertSame(StorageInterface::SUCCESS, $storage->getLastStatus());

        // Leading separator is OK
        $value = $storage->read('/a/b/c', ConfigType::STRING);
        $this->assertSame("test", $value);
        $this->assertSame(StorageInterface::SUCCESS, $storage->getLastStatus());

        // Delete it
        $storage->delete('a/b/c');
        $this->assertSame(StorageInterface::SUCCESS, $storage->getLastStatus());
        $this->assertFalse($storage->exists('a/b/c'));

        // Set a new key and check it is writable
        $storage->write('a/b/c', "test", ConfigType::STRING);

        // Get a non existing key

        // Set the same key, and retrieve it

        // Delete (unsafe) the key

        // Set (unsafe) a new key

        // Sleep and get it
    }
}

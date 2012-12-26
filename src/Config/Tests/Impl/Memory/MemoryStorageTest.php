<?php

namespace Config\Tests\Impl\Memory;

use Config\Impl\Memory\MemoryStorage;
use Config\Tests\AbstractStorageTest;

class MemoryStorageTest extends AbstractStorageTest
{
    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractStorageTest::createStorage()
     */
    protected function createStorage()
    {
        return new MemoryStorage();
    }
}

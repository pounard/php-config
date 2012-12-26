<?php

namespace Config\Tests\Impl;

use Config\Impl\Memory\MemoryStorage;
use Config\Impl\StoredBackend;
use Config\Tests\AbstractAccessTest;

class StoredAccessTest extends AbstractAccessTest
{
    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractAccessTest::setUp()
     */
    public function setUp()
    {
        // FIXME: Fix the StoredBackend class implementation
        // and remove the next line
        $this->markTestSkipped("StoredBackend class implementation is not completed yet");
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractAccessTest::createBackend()
     */
    protected function createBackend()
    {
        return new StoredBackend(new MemoryStorage());
    }
}

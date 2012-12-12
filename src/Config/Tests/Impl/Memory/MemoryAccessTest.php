<?php

namespace Config\Tests\Impl\Memory;

use Config\Impl\Memory\MemoryBackend;
use Config\Tests\AbstractAccessTest;

class MemoryAccessTest extends AbstractAccessTest
{
    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractAccessTest::createBackend()
     */
    protected function createBackend()
    {
        return new MemoryBackend();
    }
}

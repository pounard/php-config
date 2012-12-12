<?php

namespace Config\Tests\Impl\Memory;

use Config\Impl\Memory\MemoryBackend;
use Config\Tests\AbstractSchemaTest;

class MemorySchemaTest extends AbstractSchemaTest
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

<?php

namespace Config\Tests\Impl\Memory;

use Config\Impl\Memory\MemoryWritableSchema;
use Config\Tests\AbstractWritableSchemaTest;

class MemoryWritableSchemaTest extends AbstractWritableSchemaTest
{
    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractAccessTest::createBackend()
     */
    protected function createSchema()
    {
        return new MemoryWritableSchema();
    }
}

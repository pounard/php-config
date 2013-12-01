<?php

namespace Config\Tests\Impl\Predis;

use Config\Tests\AbstractStorageTest;
use Config\Impl\Predis\PhpRedisStorage;

class PhpRedisStorageTest extends AbstractStorageTest
{
    /**
     * @var \Redis
     */
    protected $client;

    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractStorageTest::createStorage()
     */
    protected function createStorage()
    {
        if ($this->client = PhpRedisHelper::getClient()) {
            return new PhpRedisStorage($this->client);
        } else {
            $this->markTestSkipped("Missing redis information.");
        }
    }

    public function setUp()
    {
        parent::setUp();

        if (null !== $this->client) {
            $this
                ->client
                ->flushdb();
        }
    }
}

<?php

namespace Config\Tests\Impl\Predis;

use Config\Impl\Predis\PredisStorage;
use Config\Tests\AbstractStorageTest;

class PredisStorageTest extends AbstractStorageTest
{
    /**
     * @var \Predis\Client
     */
    protected $client;

    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractStorageTest::createStorage()
     */
    protected function createStorage()
    {
        if ($this->client = PredisHelper::getClient()) {
            return new PredisStorage($this->client);
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

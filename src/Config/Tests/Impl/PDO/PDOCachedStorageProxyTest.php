<?php

namespace Config\Tests\Impl\PDO;

use Config\Impl\CachedStorageProxy;
use Config\Impl\Memory\MemoryStorage;
use Config\Impl\PDO\PDOStorage;
use Config\Tests\AbstractStorageTest;

class PDOCachedStorageProxyTest extends AbstractStorageTest
{
    protected function createStorage()
    {
        if ($this->connection = PDOHelper::getConnection()) {
            return new CachedStorageProxy(new PDOStorage(PDOHelper::getConnection()), function () {}, function () {});
        } else {
            $this->markTestSkipped("Missing database information.");
        }
    }

    public function setUp()
    {
        parent::setUp();

        if (null !== $this->connection) {
            $this
                ->connection
                ->query("DELETE FROM php_config_storage")
                ->execute();
        }
    }
}

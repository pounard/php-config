<?php

namespace Config\Tests\Impl\PDO;

use Config\Impl\PDO\PDOStorage;
use Config\Tests\AbstractStorageTest;

class PDOStorageTest extends AbstractStorageTest
{
    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractStorageTest::createStorage()
     */
    protected function createStorage()
    {
        if ($this->connection = PDOHelper::getConnection()) {
            return new PDOStorage($this->connection);
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

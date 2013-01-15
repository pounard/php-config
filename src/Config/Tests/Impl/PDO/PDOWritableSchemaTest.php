<?php

namespace Config\Tests\Impl\PDO;

use Config\Impl\PDO\PDOWritableSchema;
use Config\Tests\AbstractWritableSchemaTest;

class PDOWritableSchemaTest extends AbstractWritableSchemaTest
{
    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractWritableSchemaTest::createSchema()
     */
    protected function createSchema()
    {
        if ($this->connection = PDOHelper::getConnection()) {
            return new PDOWritableSchema($this->connection);
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
                ->query("DELETE FROM php_config_schema")
                ->execute();
        }
    }
}

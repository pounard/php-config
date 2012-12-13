<?php

namespace Config\Impl;

use Config\Schema\NullSchema;
use Config\Schema\SchemaAwareInterface;
use Config\Schema\SchemaInterface;

abstract class AbstractSchemaAware implements SchemaAwareInterface
{
    /**
     * @var SchemaInterface
     */
    private $schema;

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaAwareInterface::setSchema()
     */
    final public function setSchema(SchemaInterface $schema)
    {
        $this->schema = $schema;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaAwareInterface::getSchema()
     */
    final public function getSchema()
    {
        if (null === $this->schema) {
            $this->schema = new NullSchema();
        }

        return $this->schema;
    }
}

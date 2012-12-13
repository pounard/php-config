<?php

namespace Config\Impl;

use Config\PathHelper;
use Config\Schema\DefaultEntrySchema;
use Config\Schema\WritableSchemaInterface;

/**
 * This implementation is intented to use for unit testing only, please do not
 * use on a production site
 */
class MemoryWritableSchema implements WritableSchemaInterface
{
    /**
     * @var SchemaEntryInterface[]
     */
    protected $map = array();

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaInterface::getEntrySchema()
     */
    public function getEntrySchema($path)
    {
        if (isset($this->map[$path])) {
            return $this->map[$path];
        } else {
            return new DefaultEntrySchema($path, 'none');
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::merge()
     */
    public function merge(SchemaInterface $schema, $overwrite = true)
    {
        return $this->rellocate(null, $schema, $overwrite);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::rellocate()
     */
    public function rellocate($path, SchemaInterface $schema, $overwrite = true)
    {
        foreach ($schema as $entry) {
            if (null === $path) {
                $this->map[$path] = clone $entry;
            } else {
                $this->map[$path] = new DefaultEntrySchema(
                    PathHelper::join($path, $entry->getPath()),
                    $entry->getSchemaId(),
                    $entry->getType(),
                    $entry->getListType(),
                    $entry->getShortDescription(),
                    $entry->getDescription(),
                    $entry->getLocale(),
                    $entry->getDefaultValue());
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::remove()
     */
    public function remove($schemaId)
    {
        foreach ($this->map as $path => $entry) {
            if ($schemaId === $entry->getSchemaId()) {
                unset($this->map[$path]);
            }
        }
    }
}

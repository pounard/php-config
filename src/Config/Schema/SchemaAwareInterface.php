<?php

namespace Config\Schema;

/**
 * Schema and backend and decoupled objects, thus schema browser is hot
 * swappable for any instance that needs it, hence the setSchema() method
 * being public on those objects
 */
interface SchemaAwareInterface
{
    /**
     * Set schema browser
     *
     * @param SchemaInterface $schema Schema browser
     */
    public function setSchema(SchemaInterface $schema);

    /**
     * Get schema browser
     *
     * @return SchemaInterface
     */
    public function getSchema();
}

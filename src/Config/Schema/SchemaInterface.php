<?php

namespace Config\Schema;

/**
 * Schema browser and provider interface
 *
 * Traversable interface allows to iterate over the defined entries, keys
 * will be each entry path and value the associated entry schema information
 *
 * Countable will gives you an approximative or exact number of entries, do
 * not rely upon this for algorithmic needs
 */
interface SchemaInterface extends \Traversable, \Countable
{
    /**
     * Get schema information for a single entry
     *
     * @param string $path Entry path
     */
    public function getEntrySchema($path);
}

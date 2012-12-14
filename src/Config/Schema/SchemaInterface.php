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
     * @param string $path        Entry path
     *
     * @return DefaultEntrySchema Entry schema, if no entry exists with this
     *                            path a null object instance will be returned
     *                            to avoid fatal errors
     */
    public function getEntrySchema($path);

    /**
     * Tell if the entry exists in schema
     *
     * @param string $path Path
     *
     * @return bool        True if exists false else
     */
    public function exists($path);
}

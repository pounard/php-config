<?php

namespace Config\Schema;

/**
 * Schema browser and provider interface
 */
interface SchemaInterface extends \Traversable, \Countable
{
    /**
     * Get schema identifier
     *
     * @return string Identifier
     */
    public function getId();

    /**
     * Get path if schema is relocatable
     *
     * @return string Root path
     */
    //public function getPath();

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

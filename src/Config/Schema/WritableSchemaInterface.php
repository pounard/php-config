<?php

namespace Config\Schema;

/**
 * Writable schemas allow definitions to be added or removed, ideal to use as
 * an application centric schema, you can use this for register or unregister
 * application's modules when being installed or uninstalled
 */
interface WritableSchemaInterface extends SchemaInterface
{
    /**
     * Merge all definitions from the given schema into this one
     *
     * @param SchemaInterface $schema Can be independently a writable (database
     *                                for example) or a readonly (ini file or
     *                                XML based definition) schema
     * @param boolean $overwrite      If set to true already defined entries
     *                                will be overwritten, can be used for
     *                                updates
     */
    public function merge(SchemaInterface $schema, $overwrite = true);

    /**
     * Copy the given schema at the given path and treat all path inside as
     * relative path
     *
     * @param string $path            Copied schema root path, if set to null
     *                                this will behave as an alias of merge()
     * @param SchemaInterface $schema Can be independently a writable (database
     *                                for example) or a readonly (ini file or
     *                                XML based definition) schema
     * @param boolean $overwrite      If set to true already defined entries
     *                                will be overwritten, can be used for
     *                                updates
     */
    public function rellocate($path, SchemaInterface $schema, $overwrite = true);

    /**
     * Remove all keys from the given schema identifier
     *
     * @param string Schema identifier
     */
    public function remove($schemaId);
}

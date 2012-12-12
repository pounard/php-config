<?php

namespace Config\Schema;

/**
 * Schema (meta information) about a single entry
 */
interface EntrySchemaInterface
{
    /**
     * Get the entry type
     *
     * @return int One of the ConfigType::* constants
     */
    public function getType();

    /**
     * Get the entries type if current entry is a list or hashmap type
     *
     * @return int One of the ConfigType::* constants, null if current entry
     *             is not a list or a hashmap
     */
    public function getListType();

    /**
     * Get entry short description
     *
     * @return string Short description or null if none set
     */
    public function getShortDescription();

    /**
     * Get entry long description
     *
     * @return string Long description or null if none set
     */
    public function getLongDescription();

    /**
     * Get entry locale
     *
     * @return string Locale
     */
    public function getLocale();

    /**
     * Get default entry value
     *
     * @return mixed Value
     */
    public function getDefaultValue();
}

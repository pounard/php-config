<?php

namespace Config\Impl;

use Config\ConfigSchemaInterface;

/**
 * Schema (meta information) about a single entry
 */
class DefaultSchema implements ConfigSchemaInterface
{
    protected $type;

    protected $listType;

    protected $shortDesc;

    protected $longDesc;

    protected $locale;

    protected $defaultValue;

    /**
     * Default constructor
     */
    public function __construct(
        $type         = ConfigType::UNKNOWN,
        $listType     = null,
        $shortDesc    = null,
        $longDesc     = null,
        $locale       = null,
        $defaultValue = null)
    {
        $this->type         = $type;
        $this->listType     = $listType;
        $this->shortDesc    = $shortDesc;
        $this->longDesc     = $longDesc;
        $this->locale       = $locale;
        $this->defaultValue = $defaultValue;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigSchemaInterface::getType()
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigSchemaInterface::getListType()
     */
    public function getListType()
    {
        return $this->listType;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigSchemaInterface::getShortDescription()
     */
    public function getShortDescription()
    {
        return $this->shortDesc;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigSchemaInterface::getLongDescription()
     */
    public function getLongDescription()
    {
        return $this->longDesc;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigSchemaInterface::getLocale()
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\ConfigSchemaInterface::getDefaultValue()
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}

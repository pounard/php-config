<?php

namespace Config\Impl\Memory;

use Config\Path;
use Config\Schema\DefaultEntrySchema;
use Config\Schema\SchemaInterface;
use Config\Schema\WritableSchemaInterface;

/**
 * This implementation is intented to use for unit testing only, please do not
 * use on a production site
 */
class MemoryWritableSchema implements
    \IteratorAggregate,
    WritableSchemaInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var SchemaEntryInterface[]
     */
    protected $map = array();

    /**
     * Default constructor
     */
    public function __construct(array $map = null, $id = null)
    {
        if (null !== $map) {
            $this->id = $id;
        }
        if (null !== $map) {
            $this->map = $map;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaInterface::getId()
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaInterface::getEntrySchema()
     */
    public function getEntrySchema($path)
    {
        if (($path = Path::trim($path)) && isset($this->map[$path])) {
            return $this->map[$path];
        } else {
            return new DefaultEntrySchema($path, 'none');
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaInterface::exists()
     */
    public function exists($path)
    {
        if ($path = Path::trim($path)) {
            return isset($this->map[$path]);
        } else {
            return false;
        }
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->map);
    }

    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->map);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::merge()
     */
    public function merge(SchemaInterface $schema, $overwrite = true)
    {
        return $this->relocate(null, $schema, $overwrite);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::rellocate()
     */
    public function relocate($path, SchemaInterface $schema, $overwrite = true)
    {
        foreach ($schema as $entry) {
            if (null === $path) {
                $realpath = $entry->getPath();
            } else {
                $realpath = Path::join($path, $entry->getPath());
            }

            if (!$overwrite && isset($this->map[$realpath])) {
                continue;
            }

            if ($realpath === $path) {
                $this->map[$realpath] = clone $entry;
            } else {
                $this->map[$realpath] = new DefaultEntrySchema(
                    $realpath,
                    $entry->getSchemaId(),
                    $entry->getType(),
                    $entry->getListType(),
                    $entry->getShortDescription(),
                    $entry->getLongDescription(),
                    $entry->getLocale(),
                    $entry->getDefaultValue());
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::remove()
     */
    public function remove($path)
    {
        if (!is_array($path)) {
            $path = array($path);
        }

        $this->map = array_diff_key($this->map, array_flip($path));
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::remove()
     */
    public function unmerge($schemaId)
    {
        foreach ($this->map as $path => $entry) {
            if ($schemaId === $entry->getSchemaId()) {
                unset($this->map[$path]);
            }
        }
    }
}

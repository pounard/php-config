<?php

namespace Config\Schema;

/**
 * Dummy implementation of schema browser
 */
final class NullSchema implements \IteratorAggregate, SchemaInterface
{
    /**
     * All entries from this implementation will be the exact same so we can
     * use the flyweight pattern to save a few bytes of memory
     *
     * @var DefaultSchema
     */
    private $defaultEntry;

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->defaultEntry = new DefaultEntrySchema('none', 'none');
    }

    /**
     * Get schema information for a single entry
     *
     * @param string $path Entry path
     */
    public function getEntrySchema($path)
    {
        return $this->defaultEntry;
    }

    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count()
    {
        return 0;
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return new \EmptyIterator();
    }
}

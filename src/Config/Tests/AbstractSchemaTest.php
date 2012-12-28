<?php

namespace Config\Tests;

use Config\ConfigBackendInterface;
use Config\ConfigType;
use Config\Error\InvalidPathException;

abstract class AbstractSchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigBackendInterface
     */
    protected $backend;

    /**
     * Create backend instance
     *
     * @return ConfigBackendInterface
     */
    abstract protected function createBackend();

    protected function setUp()
    {
        parent::setUp();

        $this->backend = $this->createBackend();
    }

    /**
     * Tests that we will always have an entry schema instance, even if not
     * described. If none present, everything will be MIXED
     */
    public function testDynamicTyping()
    {
        // We explcitely do not set the schema browser on this instance to
        // force it to dynamically type unknown entries
        $cursor = $this->backend;

        $cursor['test1'] = 13;
        $schema = $cursor->getSchema()->getEntrySchema('test1');
        // Dynamically typed schema cannot contain information
        $this->assertSame(null, $schema->getShortDescription());
        $this->assertSame(null, $schema->getLongDescription());
        $this->assertSame(ConfigType::MIXED, $schema->getType());

        // Test with some other types, and in a different cursor
        // than root, to ensure this works too
        $cursor2 = $cursor->getCursor('a');

        $cursor2['b'] = 'some string';
        $schema = $cursor2->getSchema()->getEntrySchema('a/b');
        $this->assertSame(ConfigType::MIXED, $schema->getType());

        $cursor2['c'] = 11.2;
        $schema = $cursor2->getSchema()->getEntrySchema('a/c');
        $this->assertSame(ConfigType::MIXED, $schema->getType());

        $cursor2['d'] = false;
        $schema = $cursor2->getSchema()->getEntrySchema('a/d');
        $this->assertSame(ConfigType::MIXED, $schema->getType());

        // @todo We need more!
    }
}

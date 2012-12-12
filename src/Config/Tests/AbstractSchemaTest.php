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

    public function testDynamicTyping()
    {
        // We explcitely do not set the schema browser on this instance to
        // force it to dynamically type unknown entries
        $cursor = $this->backend;

        $cursor['test1'] = 13;
        $schema = $cursor->getEntrySchema('test1');
        // Dynamically typed schema cannot contain information
        $this->assertSame(null, $schema->getShortDescription());
        $this->assertSame(null, $schema->getLongDescription());
        $this->assertSame(ConfigType::INT, $schema->getType());

        // Test with some other types, and in a different cursor
        // than root, to ensure this works too
        $cursor2 = $cursor->getCursor('a');

        $cursor2['b'] = 'some string';
        $schema = $cursor2->getEntrySchema('b');
        $this->assertSame(ConfigType::STRING, $schema->getType());

        $cursor2['c'] = 11.2;
        $schema = $cursor2->getEntrySchema('c');
        $this->assertSame(ConfigType::FLOAT, $schema->getType());

        $cursor2['d'] = false;
        $schema = $cursor2->getEntrySchema('d');
        $this->assertSame(ConfigType::BOOLEAN, $schema->getType());

        // @todo We need more!
    }
}

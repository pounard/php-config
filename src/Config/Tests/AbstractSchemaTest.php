<?php

namespace Config\Tests;

use Config\ConfigBackendInterface;
use Config\ConfigType;
use Config\InvalidPathException;

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
        $cursor = $this->backend;

        $cursor['test1'] = 13;
        $schema = $cursor->getSchema('test1');
        // Dynamically typed schema cannot contain information
        $this->assertSame(null, $schema->getShortDescription());
        $this->assertSame(null, $schema->getLongDescription());
        $this->assertSame(ConfigType::INT, $schema->getType());

        // Test with some other types, and in a different cursor
        // than root, to ensure this works too
        $cursor2 = $cursor->getCursor('a');

        $cursor2['b'] = 'some string';
        $schema = $cursor2->getSchema('b');
        $this->assertSame(ConfigType::STRING, $schema->getType());

        $cursor2['c'] = 11.2;
        $schema = $cursor2->getSchema('c');
        $this->assertSame(ConfigType::FLOAT, $schema->getType());

        $cursor2['d'] = false;
        $schema = $cursor2->getSchema('d');
        $this->assertSame(ConfigType::BOOLEAN, $schema->getType());

        // @todo We need more!
    }
}

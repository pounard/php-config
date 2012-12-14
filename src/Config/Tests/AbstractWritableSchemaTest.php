<?php

namespace Config\Tests;

use Config\ConfigType;
use Config\Impl\Memory\MemoryWritableSchema;
use Config\Schema\DefaultEntrySchema;
use Config\Schema\WritableSchemaInterface;

abstract class AbstractWritableSchemaTest extends \PHPUnit_Framework_TestCase
{
    protected $schema;

    /**
     * Create backend instance
     *
     * @return WritableSchemaInterface
     */
    abstract protected function createSchema();

    protected function setUp()
    {
        parent::setUp();

        $this->schema = $this->createSchema();
    }

    public function testMergeAndRemove()
    {
        $schema = $this->schema;

        $definition = array(
            'a.b.c' => new DefaultEntrySchema(
                'a.b.c',
                'schema1',
                ConfigType::INT,
                null,
                "Short a.b.c",
                "Long a.b.c",
                'fr_FR',
                12
            ),
            'a.b.d' => new DefaultEntrySchema(
                'a.b.d',
                'schema1',
                ConfigType::STRING,
                null,
                "Short a.b.d",
                "Long a.b.d",
                'fr_FR',
                "foo"
            ),
            'a.b.e' => new DefaultEntrySchema(
                'a.b.e',
                'schema2',
                ConfigType::STRING,
                null,
                "Short a.b.e",
                "Long a.b.e",
                'fr_FR',
                "bar"
            ),
        );
        $newSchema = new MemoryWritableSchema($definition);

        // Test merge
        $schema->merge($newSchema);

        $entry = $schema->getEntrySchema('a.b.c');
        $this->assertSame("a.b.c", $entry->getPath());
        $this->assertSame("schema1", $entry->getSchemaId());
        $this->assertSame(ConfigType::INT, $entry->getType());
        $this->assertSame(12, $entry->getDefaultValue());

        $entry = $schema->getEntrySchema('a.b.e');
        $this->assertSame("a.b.e", $entry->getPath());
        $this->assertSame("schema2", $entry->getSchemaId());
        $this->assertSame(ConfigType::STRING, $entry->getType());
        $this->assertSame("bar", $entry->getDefaultValue());

        // Ok, test some counts and stuff
        $this->assertCount(3, $schema);

        // Everything OK, now test some overrides (default behavior)
        $newSchemaOverride = new MemoryWritableSchema(array(
            'a.b.e' => new DefaultEntrySchema(
                'a.b.e',
                'schema2',
                ConfigType::TUPLE,
                ConfigType::INT,
                "Short overriden a.b.e",
                "Long a.b.e",
                'fr_FR',
                array(1, 2)
            ),
        ));

        $schema->merge($newSchemaOverride);

        $entry = $schema->getEntrySchema('a.b.e');
        $this->assertSame("a.b.e", $entry->getPath());
        $this->assertSame("schema2", $entry->getSchemaId());
        $this->assertSame(ConfigType::TUPLE, $entry->getType());
        $this->assertSame(ConfigType::INT, $entry->getListType());
        $this->assertSame("Short overriden a.b.e", $entry->getShortDescription());

        // Ok now do it once again, without override
        $newSchemaNoOverride = new MemoryWritableSchema($definition + array(
            'test1' => new DefaultEntrySchema(
                'test1',
                'schema2',
                ConfigType::MAP,
                ConfigType::INT,
                "Short test1",
                "Long test1",
                'fr_FR',
                array("a" => 1, "b" => 2)
            ),
        ));

        $schema->merge($newSchemaNoOverride, false);

        $entry = $schema->getEntrySchema('a.b.e');
        $this->assertSame(ConfigType::TUPLE, $entry->getType());
        $this->assertSame(ConfigType::INT, $entry->getListType());
        $this->assertSame("Short overriden a.b.e", $entry->getShortDescription());

        // Also test that new entries where merged
        $entry = $schema->getEntrySchema('test1');
        $this->assertSame("Short test1", $entry->getShortDescription());

        // Test some other helpers
        $this->assertTrue($schema->exists('test1'));
        $this->assertFalse($schema->exists('booouuh'));

        // Now remove stuff
        // Nothing should happen here (remain silent)
        $schema->remove('test2');

        $schema->remove('test1');
        $this->assertFalse($schema->exists('test1'));

        $schema->remove(array('non_existing', 'a.b.c'));
        $this->assertFalse($schema->exists('a.b.c'));

        // And also test unmerge
        $this->assertTrue($schema->exists('a.b.d'));
        $schema->unmerge("schema1");
        $this->assertFalse($schema->exists('a.b.c'));
        $this->assertFalse($schema->exists('a.b.d'));
        $this->assertTrue($schema->exists('a.b.e'));
        $this->assertFalse($schema->exists('test1'));
        $this->assertFalse($schema->exists('test2'));
    }

    public function testRelocate()
    {
        $schema = $this->schema;
    }
}

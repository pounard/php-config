<?php

namespace Config\Tests;

use Config\ConfigBackendInterface;
use Config\Error\InvalidPathException;

abstract class AbstractAccessTest extends \PHPUnit_Framework_TestCase
{
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

    public function testGetSetHas()
    {
        $cursor = $this->backend;

        // At the beginning everything is empty
        $this->assertSame(null, $cursor->get('test1'));

        // Ensure set works
        $cursor->set('test1', 42);
        $this->assertSame(42, $cursor->get('test1'));

        // Now ensure that both hierarchy is created dynamically and that
        // the count() method only count entries
        $cursor->set('a.b.c', 13);
        $this->assertSame(13, $cursor->get('a.b.c'));
        $this->assertCount(2, $cursor);

        /*
        $cursor->delete('a');
        $this->assertSame(null, $cursor->get('a.b.c'));
        $this->assertCount(1, $cursor);
         */

        $invalidPathList = array(
            'invalid..path',
            '.invalid.path',
            'invalid.path.',
            '',
        );

        foreach ($invalidPathList as $path) {
            try {
                $cursor->get($path);
                $this->fail(sprintf(
                    "The path %s was invalid and should have raised an exception",
                    $path));
            } catch (InvalidPathException $e) {
                $this->assertTrue(true, "Got an exception on invalid path");
            }
        }
    }

    public function testArrayAccess()
    {
        $cursor = $this->backend;

        // At the beginning everything is empty
        $this->assertSame(null, $cursor['test1']);

        // Ensure set works
        $cursor['test1'] = 42;
        $this->assertSame(42, $cursor['test1']);

        // Now ensure that both hierarchy is created dynamically and that
        // the count() method only count entries
        $cursor['a.b.c'] = 13;
        $this->assertSame(13, $cursor['a.b.c']);
        $this->assertCount(2, $cursor);

        /*
        unset($cursor['a'];
        $this->assertSame(null, $cursor['a.b.c']);
        $this->assertCount(1, $cursor);
         */
    }

    public function testInternalCursor()
    {
        $cursor = $this->backend;

        $cursor['a.b.c'] = 11;

        // Internal cursor got it right
        $cursor2 = $cursor->getCursor('a.b');
        $this->assertSame(11, $cursor2['c']);

        // Modify value from second cursor and check first cursor sees it
        $cursor2['d'] = 7;
        $this->assertSame(7, $cursor['a.b.d']);

        // Reverse!
        $cursor['a.b.e'] = 21;
        $this->assertSame(21, $cursor2['e']);

        // @todo Check unset too
    }

    public function testIntrospection()
    {
        // @todo Check iterators
    }
}

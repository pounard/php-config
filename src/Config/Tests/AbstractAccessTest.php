<?php

namespace Config\Tests;

use Config\ConfigBackendInterface;
use Config\InvalidPathException;

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
                $this->fail("The path was invalid and should have raised an exception");
            } catch (InvalidPathException $e) {
                $this->assertTrue(true, "Got an exception on invalid path");
            }
        }

        /*
        $subscription = $this->channel->subscribe();
        $this->assertInstanceOf('\APubSub\SubscriptionInterface', $subscription);

        $id = $subscription->getId();
        $this->assertFalse(empty($id));

        $channel = $subscription->getChannel();
        // Depending on the implementation, the instance might not be the same
        // so only check for ids to be the same
        $this->assertSame($channel->getId(), $this->channel->getId());

        // Per definition a new subscription is always inactive
        $this->assertFalse($subscription->isActive());

        try {
            $subscription->getStartTime();
            $this->fail("Subscriber should not have a start time");
        } catch (\Exception $e) {
        }

        // Should not raise any exception
        $subscription->getStopTime();

        $loaded = $this->backend->getSubscription($subscription->getId());
        $this->assertSame(get_class($subscription), get_class($loaded));
        $this->assertSame($subscription->getId(), $loaded->getId());
        $this->assertFalse($loaded->isActive());

        $this->assertSame($this->channel->getId(), $subscription->getChannel()->getId());
        $this->assertSame($this->channel->getId(), $loaded->getChannel()->getId());

        $subscription->activate();

        try {
            $subscription->getStartTime();
        } catch (\Exception $e) {
            $this->fail("Subscriber should have a start time");
        }
         */
    }
}

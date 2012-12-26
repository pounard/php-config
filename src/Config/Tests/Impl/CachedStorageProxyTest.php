<?php

namespace Config\Tests\Impl;

use Config\Impl\CachedStorageProxy;
use Config\Impl\Memory\MemoryStorage;
use Config\Tests\AbstractStorageTest;

class CachedStorageProxyTest extends AbstractStorageTest
{
    /**
     * (non-PHPdoc)
     * @see \Config\Tests\AbstractStorageTest::createStorage()
     */
    protected function createStorage()
    {
        return new CachedStorageProxy(new MemoryStorage(), function () {}, function () {});
    }
}

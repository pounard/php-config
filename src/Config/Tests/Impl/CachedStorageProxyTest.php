<?php

namespace Config\Tests\Impl;

use Config\Impl\CachedStorageProxy;
use Config\Impl\Memory\MemoryStorage;
use Config\Tests\AbstractStorageTest;

class CachedStorageProxyTest extends AbstractStorageTest
{
    protected function createStorage()
    {
        return new CachedStorageProxy(new MemoryStorage(), function () {}, function () {});
    }
}

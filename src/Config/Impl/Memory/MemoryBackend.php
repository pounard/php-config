<?php

namespace Config\Impl\Memory;

use Config\ConfigBackendInterface;
use Config\Schema\SchemaAwareInterface;
use Config\Schema\SchemaInterface;

class MemoryBackend extends MemoryCursor implements ConfigBackendInterface
{
}

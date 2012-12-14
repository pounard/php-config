<?php

namespace Config;

use Config\Schema\SchemaAwareInterface;

/**
 * Configuration backend, this is no more than a cursor but which is root
 *
 * About configuration names: name can only contain lowercase letters, numbers
 * and '-'
 * About configuration path: a path is the join between one or more names using
 * the dot separator
 */
interface ConfigBackendInterface extends ConfigCursorInterface
{
}

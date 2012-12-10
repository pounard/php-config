<?php

namespace Config;

/**
 * Configuration reader. Configuration options are materialized by strings
 * that can contain one or more dot inside defining a path inside the
 * configuration tree.
 *
 * Valid path examples:
 *   - debug
 *   - debug.enabled
 *   - site.name
 *
 * Invalid path examples:
 *   - .debug
 *   - debug.
 */
interface ConfigBackendInterface extends ConfigCursorInterface
{
}

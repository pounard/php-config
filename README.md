Schema based configuration registry
===================================

This API provides configuration settings management for PHP applications.

It provides two components:

 * Interfaces for reading and writing configuration in very simple and
   efficient manner.

 * A full API for reading and browsing the configuration schema, allowing to
   manage your configuration in the same way as the Windows registry or the
   GSettings API.

It also gives:

 * A very simple interface for writing storage backend easily.

 * A less but still very simple interface for writing your schema browser
   implementation easily.

 * A fully working configuration cursor implementation that uses any storage
   backend and any schema browser backend.

 * A fully working storage backend proxy that caches read keys based upon
   two simple callables: a cache setter and a cache getter for working;
   Allowing you to easily add a cache layer for configuration.

Main features:

 * Every value is identified by a path, e.g. _a/b/c_.

 * Every value is typed using a primitive type or a list or hashmap of
   primitive types.

 * All paths together give a single tree in which the full configuration values
   can be read and written.

 * All path may be defined in a schema. Schema allows tree introspection.

 * Error control on read/write operations which disallows mistyping values.

Getting started
===============

Cursor basic usage
------------------

The following example will show you how to set and get values from a
configuration cursor. Note that a configuration backend is nothing more than
a cursor which is considered as being the root of the configuration tree.

    // You have to start with something
    $config = new MemoryBackend(
        parse_ini_file("config.ini", true));

    // Simple read operation
    if ($config['a/b/c']) {
        do_something();
    }

    // Set a value (schema less if unknown)
    $config['a/b/c'] = 42;

    // Get a sub tree cursor
    $cursor = $config->getCursor('a/b');
    // Works with relative path, this echoes 42
    echo $cursor['c'];

    // You can introspect easily
    foreach ($cursor as $key => $entry) {
        if ($entry instanceof ConfigCursorInterface) {
            // This is a cursor, do whatever you want to do with it
        } else {
            // This is a single value
            echo $key, " is ", $value;
        }
    }

    // And introspect schema too
    $entrySchema = $config->getSchema('a.b.c');
    echo $entrySchema->getShortDescription(), "\n",
         $entrySchema->getType(), "\n";

Instanciating a full configuration stack
----------------------------------------

If you are building a complex application that needs a full stack configuration
API, including configuration keys typing and introspection abilities, follow
the next example.

    // Create your schema and storage instances, could be anything
    // else including your own implementation
    $schema = new MemoryWritableSchema();
    $storage = new MemoryStorage();
    $root = null;

    // Optional set, we could use a caching proxy
    $cacheId = $__SERVER['REQUEST_URI'];
    $storage = new CacheStorageProxy(
        $storage,
        function () use ($cacheId) {
            return apc_fetch($cacheId);
        },
        function ($data) use ($cacheId {
            apc_store($cacheId, $data);
        });

    // Optionnaly, we could consider share the storage backend with
    // multiple applications, case in which we need to namespace them
    $root = '/my/application';

    $cursor = new StoredBackend($storage, $schema, $root);

From this point, the _$cursor_ variable is read to be used. As explained
upper, this cursor is also the _backend_. You can from this point store a
pointer to the $cursor backend anywhere, as in example a dependency injection
container, and use it in your application.

Consider this example (using a Symfony DIC interface):

    $container->set('config', $cursor);

    // Later, considering we're now in a specific ContainerAware object
    // context
    $config = $this->getContainer()->get('config');

    // If you want to read configuration
    $value = $config['/some/path'];

    // If you want to introspect schema
    $schema = $config->getSchema();

The cursor is a single point of entry for the full API.

Basic configuration stack usage
-------------------------------

@todo

History
=======

Just commiting this code somewhere I can keep it.

I made this code a while ago, I just needed to see it back for some reason.

I won't lie, this is unfinished. The main goal was to plug it on GConf for a
specific PHP application. Still doable, using php-dbus (search DBus in PECL
site). I may do it some day that's something I'd be terribly curious to
benchmark.

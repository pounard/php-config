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

Getting started
===============

Cursor basic usage
------------------

    // You have to start with something
    $config = new MemoryBackend(
        parse_ini_file("config.ini", true));

    // Simple read operation
    if ($config['a.b.c']) {
        do_something();
    }

    // Set a value (schema less if unknown)
    $config['a.b.c'] = 42;

    // Get a sub tree cursor
    $cursor = $config->getCursor('a.b');
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


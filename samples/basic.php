<?php

use Config\Impl\Memory\MemoryBackend;

if (!is_file($autoloadFile = __DIR__ . '/../vendor/autoload.php')) {
  throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install" ?');
}
require $autoloadFile;

// You have to start with something
$configArray = parse_ini_file(__DIR__ . "/config.ini", true);
$config = new MemoryBackend($configArray);

// Next 2 will echo "bar"
echo "foo: ", $config['foo'], "\n";
echo "/foo: ", $config['/foo'], "\n";

// Next 2 will echo "baz"
echo "a/foo: ", $config['a/foo'], "\n";
echo "/a/foo: ", $config['/a/foo'], "\n";

// Next 2 will echo "21"
echo "a/c: ", $config['a/c'], "\n";
echo "/a/c: ", $config['/a/c'], "\n";

// Set a value (schema less if unknown)
$config['a/b/c'] = 42;

// Get a sub tree cursor
$cursor = $config->getCursor('a/b');
// Works with relative path, this echoes 42
echo "a/b/c (cursor): ", $cursor['c'], "\n";

// You can introspect easily
foreach ($cursor as $key => $entry) {
    if ($entry instanceof ConfigCursorInterface) {
        // This is a cursor, do whatever you want to do with it
    } else {
        // This is a single value
        echo $key, " is ", $value, "\n";
    }
}

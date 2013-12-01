<?php

namespace Config\Tests\Impl\Predis;

class PhpRedisHelper
{
    /**
     * @var \Redis
     */
    static private $client;

    /**
     * Get Predis client connection
     *
     * @return \Predis\Client
     */
    static public function getClient()
    {
        if (null === self::$client) {

            if (!class_exists('\Redis')) {
                return self::$client = false;
            }

            $args = array();

            if (!empty($GLOBALS['REDIS_HOST'])) {
                $args['host'] = $GLOBALS['REDIS_HOST'];
            }
            if (!empty($GLOBALS['REDIS_PORT'])) {
                $args['port'] = $GLOBALS['REDIS_PORT'];
            }
            if (!empty($GLOBALS['REDIS_DB'])) {
                $args['database'] = $GLOBALS['REDIS_DB'];
            }
            if (!empty($GLOBALS['REDIS_PASS'])) {
                $args['password'] = $GLOBALS['REDIS_PASS'];
            }

            if (!empty($args)) {
                self::$client = new \Redis();
                if (!empty($args['host'])) {
                    if (empty($args['port'])) {
                        self::$client->connect($args['host']);
                    } else {
                        self::$client->connect($args['host'], $args['port']);
                    }
                }
            }
        }

        return self::$client;
    }
}

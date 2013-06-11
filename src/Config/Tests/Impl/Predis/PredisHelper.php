<?php

namespace Config\Tests\Impl\Predis;

class PredisHelper
{
    /**
     * @var \Predis\Client
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

            if (empty($info)) {
                self::$client = new \Predis\Client();
            } else {
                self::$client = new \Predis\Client($args);
            }
        }

        return self::$client;
    }
}

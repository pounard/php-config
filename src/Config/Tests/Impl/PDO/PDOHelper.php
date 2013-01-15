<?php

namespace Config\Tests\Impl\PDO;

class PDOHelper
{
    /**
     * @var \PDO
     */
    static private $connection;

    /**
     * Get PDO connection
     *
     * @return \Config\Tests\Impl\PDO\PDO
     */
    static public function getConnection()
    {
        if (!isset($GLOBALS['DB_DSN']) ||
            !isset($GLOBALS['DB_USER']) ||
            !isset($GLOBALS['DB_PASSWD']) ||
            !isset($GLOBALS['DB_DBNAME']))
        {
            return null;
        }

        if (null === self::$connection)  {

            self::$connection = new \PDO(
                $GLOBALS['DB_DSN'],
                $GLOBALS['DB_USER'],
                $GLOBALS['DB_PASSWD']);

            self::$connection->setAttribute(
                \PDO::ATTR_ERRMODE,
                \PDO::ERRMODE_EXCEPTION);
        }

        return self::$connection;
    }
}

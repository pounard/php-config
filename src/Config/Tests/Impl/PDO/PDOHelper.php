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
        if (empty($GLOBALS['DB_DSN']) ||
            empty($GLOBALS['DB_USER']) ||
            empty($GLOBALS['DB_PASSWD']) ||
            empty($GLOBALS['DB_DBNAME']))
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

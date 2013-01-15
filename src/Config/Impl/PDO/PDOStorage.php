<?php

namespace Config\Impl\PDO;

use Config\ConfigType;
use Config\Path;
use Config\Storage\StorageInterface;

/**
 * PDO storage that should work with most SQL backends
 *
 * It is strongly advised to use \PDO::ERRMODE_EXCEPTION as error handling
 * mode when testing
 */
class PDOStorage implements StorageInterface
{
    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var bool
     */
    private $asyncEnabled = false;

    /**
     * @var int
     */
    private $status = StorageInterface::STATUS_UNKNOWN;

    /**
     * @var \PDOStatement
     */
    private $safeReadStatement;

    /**
     * @var \PDOStatement
     */
    private $readStatement;

    /**
     * @var \PDOStatement
     */
    private $existsStatement;

    /**
     * Default constructor
     *
     * @param \PDO $connection Database connection
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;

        if (false !== strpos($connection->getAttribute(\PDO::ATTR_CLIENT_VERSION), 'mysqlnd')) {
            $this->asyncEnabled = true;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::getLastStatus()
     */
    public function getLastStatus()
    {
        return $this->status;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::read()
     */
    public function read($path, $expectedType)
    {
        $ret = null;

        if (!$path = Path::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else {

            if (ConfigType::MIXED === $expectedType) {
                if (null === $this->readStatement) {
                    $statement = $this->readStatement = $this->connection->prepare("
                        SELECT value
                        FROM php_config_storage s
                        WHERE s.path = :path");

                    $this->readStatement->setFetchMode(\PDO::FETCH_COLUMN, 0);
                }

                $this->readStatement->execute(array(
                    ':path' => (string)$path,
                ));

                $statement = $this->readStatement;
            } else {
                if (null === $this->safeReadStatement) {
                    $statement = $this->safeReadStatement = $this->connection->prepare("
                        SELECT
                            IF (s.type = :type, s.value, :typeError) AS value
                        FROM php_config_storage s
                        WHERE s.path = :path");

                    $this->safeReadStatement->setFetchMode(\PDO::FETCH_COLUMN, 0);
                }

                $this->safeReadStatement->execute(array(
                    ':type'      => (int)$expectedType,
                    ':path'      => (string)$path,
                    ':typeError' => StorageInterface::ERROR_TYPE_MISMATCH,
                ));

                $statement = $this->safeReadStatement;
            }

            foreach ($statement as $value) {

                if (StorageInterface::ERROR_TYPE_MISMATCH === (int)$value) {
                    $this->status = StorageInterface::ERROR_TYPE_MISMATCH;
                } else {
                    $this->status = StorageInterface::SUCCESS;
                    $ret = unserialize($value);
                }

                // There can be only one
                break;
            }
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::exists()
     */
    public function exists($path)
    {
        $ret = false;

        if (!$path = Path::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else {

            if (null === $this->existsStatement) {
                $this->existsStatement = $this->connection->prepare("
                    SELECT 1
                    FROM php_config_storage s
                    WHERE s.path = :path");

                $this->existsStatement->setFetchMode(\PDO::FETCH_COLUMN, 0);
            }

            $this->existsStatement->execute(array(
                ':path' => (string)$path,
            ));

            foreach ($this->existsStatement as $value) {
                $ret = true;
            }

            $this->status = StorageInterface::SUCCESS;
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::isWritable()
     */
    public function isWritable($path)
    {
        // This backend does not support key locking (yet)
        // @todo Implement it
        $this->status = StorageInterface::SUCCESS;

        return true;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::write()
     */
    public function write($path, $value, $type = ConfigType::MIXED, $safe = true)
    {
        if (!$path = Path::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else if (!$this->connection->beginTransaction()) {
            $this->status = StorageInterface::STATUS_UNKNOWN;
        } else {
            try {
                $exists = $this->exists($path);

                /*
                $this
                    ->connection
                    ->prepare("
                        SELECT FOR UPDATE
                            FROM php_config_storage
                            WHERE path = :path")
                    ->execute(array(
                        'path'  => (string)$path,
                    ));
                 */

                if ($exists) {
                    $this
                        ->connection
                        ->prepare("
                            UPDATE php_config_storage SET
                                type = :type,
                                value = :value
                            WHERE path = :path")
                        ->execute(array(
                            ':type'  => (int)$type,
                            ':value' => serialize($value),
                            ':path'  => (string)$path,
                        ));
                } else {
                    $this
                        ->connection
                        ->prepare("
                            INSERT INTO php_config_storage
                                (path, type, value) VALUES
                                (:path, :type, :value)")
                        ->execute(array(
                            ':path'  => (string)$path,
                            ':type'  => (int)$type,
                            ':value' => serialize($value),
                        ));
                }

                $this->connection->commit();
                $this->status = StorageInterface::SUCCESS;

            } catch (PDOException $e) {
                $this->connection->rollback();
                $this->status = StorageInterface::STATUS_UNKNOWN;

                throw $e;
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::delete()
     */
    public function delete($path, $safe = true)
    {
        if (!$path = Path::trim($path)) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else {

            $this
                ->connection
                ->prepare("DELETE FROM php_config_storage WHERE path = :path")
                ->execute(array(
                    ':path' => (string)$path,
                ));

            $this->status = StorageInterface::SUCCESS;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Storage\StorageInterface::getKeys()
     */
    public function getKeys($path = null)
    {
        $ret = array();

        if (null !== $path && !($path = Path::trim($path))) {
            $this->status = StorageInterface::ERROR_PATH_INVALID;
        } else {
            if (null === $path) {
                $this->status = StorageInterface::SUCCESS;

                $statement = $this
                    ->connection
                    ->prepare("SELECT path FROM php_config_storage");
                $statement->setFetchMode(\PDO::FETCH_COLUMN, 0);
                $statement->execute();
            } else {
                $statement = $this
                    ->connection
                    ->prepare("SELECT path FROM php_config_storage WHERE path like :path");
                $statement->setFetchMode(\PDO::FETCH_COLUMN, 0);
                $statement->execute(array(
                    ':path' => $path . Path::SEPARATOR . "%",
                ));
            }

            foreach ($statement as $path) {
                $ret[] = $path;
            }
        }

        return $ret;
    }
}

<?php

namespace Config\Impl\PDO;

use Config\Error\InvalidPathException;
use Config\Path;
use Config\Schema\DefaultEntrySchema;
use Config\Schema\EntrySchemaInterface;
use Config\Schema\SchemaInterface;
use Config\Schema\WritableSchemaInterface;

/**
 * PDO schema that should work with most SQL backends
 *
 * It is strongly advised to use \PDO::ERRMODE_EXCEPTION as error handling
 * mode when testing
 */
class PDOWritableSchema implements \IteratorAggregate, WritableSchemaInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var DefaultSchema
     */
    private $nullEntry;

    /**
     * Default constructor
     *
     * @param \PDO $connection Database connection
     */
    public function __construct(\PDO $connection, $id = null)
    {
        $this->connection = $connection;
        $this->nullEntry  = new DefaultEntrySchema('none', 'none');
        $this->id         = $id;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaInterface::getId()
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count()
    {
        $statement = $this
            ->connection
            ->prepare("SELECT COUNT(*) FROM php_config_schema");
        $statement->setFetchMode(\PDO::FETCH_COLUMN, 0);
        $statement->execute();

        foreach ($statement as $value) {
            return $value;
        }

        // Error can happen?
        return 0;
    }

    /**
     * (non-PHPdoc)
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        // This implementation using \IteratorAggregate instead of \Iterator
        // can cause memory problems when dealing with very large schemas,
        // note that we might need to implement \Iterator in the end
        // PHP5.5 yield keyword would have been bliss for us right here
        $ret = array();

        $statement = $this
            ->connection
            ->prepare("SELECT * FROM php_config_schema ORDER BY path");
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute();

        foreach ($statement as $record) {
            $ret[] = new DefaultEntrySchema(
                $record->path,
                $record->schema_id,
                (int)$record->type,
                empty($record->listtype)  ? null : ((int)$record->listtype),
                empty($record->shortdesc) ? null : $record->shortdesc,
                empty($record->longdesc)  ? null : $record->longdesc,
                empty($record->locale)    ? null : $record->locale,
                empty($record->default)   ? null : unserialize($record->default));
        }

        return $ret;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaInterface::getEntrySchema()
     */
    public function getEntrySchema($path)
    {
        if (!$trimedPath = Path::trim($path)) {
            throw new InvalidPathException($trimedPath);
        }

        $statement = $this
            ->connection
            ->prepare("SELECT * FROM php_config_schema WHERE path = :path");
        $statement->setFetchMode(\PDO::FETCH_OBJ);
        $statement->execute(array(
            ':path' => $path,
        ));

        foreach ($statement as $record) {
            return new DefaultEntrySchema(
                $record->path,
                $record->schema_id,
                (int)$record->type,
                empty($record->listtype)  ? null : ((int)$record->listtype),
                empty($record->shortdesc) ? null : $record->shortdesc,
                empty($record->longdesc)  ? null : $record->longdesc,
                empty($record->locale)    ? null : $record->locale,
                empty($record->default)   ? null : unserialize($record->default));
        }

        return $this->nullEntry;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\SchemaInterface::exists()
     */
    public function exists($path)
    {
        if (!$trimedPath = Path::trim($path)) {
            throw new InvalidPathException($trimedPath);
        }

        $statement = $this
            ->connection
            ->prepare("SELECT 1 FROM php_config_schema WHERE path = :path");
        $statement->setFetchMode(\PDO::FETCH_COLUMN, 0);
        $statement->execute(array(':path' => $trimedPath));

        foreach ($statement as $value) {
            // Having one result means we found the right path
            return true;
        }

        return false;
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::merge()
     */
    public function merge(SchemaInterface $schema, $overwrite = true)
    {
        return $this->relocate(null, $schema, $overwrite);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::relocate()
     */
    public function relocate($path, SchemaInterface $schema, $overwrite = true)
    {
        if (!$this->connection->beginTransaction()) {
            throw new \RuntimeException("Could not start transaction");
        }

        try {
            foreach ($schema as $entry) {

                if (!$entry instanceof EntrySchemaInterface) {
                    continue;
                }

                $localPath = $entry->getPath();

                if (null !== $path && (!$localPath = Path::join($path, $localPath))) {
                    // Let's assume that localPath is always right because the
                    // originating SchemaInterface instance has been tested right
                    throw new InvalidPathException($path);
                }

                $exists = $this->exists($localPath);

                $values = array(
                    'path'      => $localPath,
                    'schema_id' => $entry->getSchemaId(),
                    'type'      => $entry->getType(),
                    'listtype'  => $entry->getListType(),
                    'shortdesc' => $entry->getShortDescription(),
                    'longdesc'  => $entry->getLongDescription(),
                    'locale'    => $entry->getLocale(),
                );

                if (null === ($value = $entry->getDefaultValue())) {
                    $values['default'] = null;
                } else {
                    $values['default'] = serialize($value);
                }

                $args = array();

                if ($exists) {
                    if ($overwrite) {
                        $setList = array();
                        foreach ($values as $key => $value) {
                            if (null !== $value) {
                                $setList[] = "`" . $key . "` = :" . $key;
                                $args[':' . $key] = $value;
                            }
                        }

                        $this
                            ->connection
                            ->prepare("UPDATE php_config_schema SET " . implode(", ", $setList) . " WHERE path = :path")
                            ->execute($values);
                    }
                } else {

                    $fieldList = array();
                    foreach ($values as $key => $value) {
                        if (null !== $value) {
                            $args[':' . $key] = $value;
                            $fieldList[] = "`" . $key . "`";
                        }
                    }

                    $this
                        ->connection
                        ->prepare("
                            INSERT INTO php_config_schema
                            (" . implode(', ', $fieldList) . ")
                            VALUES (" . implode(', ', array_keys($args)) . ")")
                        ->execute($args);
                }
            }

            $success = $this
                ->connection
                ->commit();

        } catch (PDOException $e) {
            $this
                ->connection
                ->rollBack();

            throw $e;
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::remove()
     */
    public function remove($path)
    {
        if (!is_array($path)) {
            $pathList = array($path);
        } else {
            $pathList = $path;
        }

        $in = array();
        foreach ($pathList as $key => $path) {
            if (!$trimedPath = Path::trim($path)) {
                throw new InvalidPathException($trimedPath);
            }

            $pathList[$key] = $trimedPath;
            $in[] = "?";
        }

        $this
            ->connection
            ->prepare("DELETE FROM php_config_schema WHERE path IN (" . implode(', ', $in) . ")")
            ->execute($pathList);
    }

    /**
     * (non-PHPdoc)
     * @see \Config\Schema\WritableSchemaInterface::unmerge()
     */
    public function unmerge($schemaId)
    {
        $this
            ->connection
            ->prepare("DELETE FROM php_config_schema WHERE schema_id = :schemaId")
            ->execute(array(
                ':schemaId' => $schemaId,
            ));
    }
}

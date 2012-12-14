<?php

namespace Config;

/**
 * Describe all handled data types
 */
final class ConfigType
{
    /**
     * Type is unspecified by the schema entry (per default typing is dynamic)
     */
    const MIXED = -1;

    /**
     * Type is boolean
     */
    const BOOLEAN = 1;

    /**
     * Type is integer
     */
    const INT = 10;

    /**
     * Type is float or decimal
     *
     * Float and decimals are the same in PHP
     */
    const FLOAT = 11;

    /**
     * Type is float or decimal
     *
     * Float and decimals are the same in PHP
     */
    const DECIMAL = 11;

    /**
     * Type is string
     */
    const STRING = 30;

    /**
     * Type is ordered list (keys are not revelant)
     */
    const TUPLE = 40;

    /**
     * Type is an hashmap (keys are revelant)
     */
    const MAP = 41;

    /**
     * Get human readable string for the given type
     *
     * @param int $type Type
     *
     * @return string   String
     */
    public static function getString($type)
    {
        switch ($type) {

            case self::MIXED:
                return "any";

            case self::BOOLEAN:
                return "boolean";

            case self::INT:
                return "integer";

            case self::FLOAT:
            case self::DECIMAL:
                return "float";

            case self::STRING:
                return "string";

            case self::TUPLE:
                return "list";

            case self::MAP:
                return "map";
        }
    }

    /**
     * Dynamically get type from value
     *
     * @param mixed $value Value
     *
     * @return int         Type
     */
    public static function getType($value)
    {
        if (null === $value) {
            return self::MIXED;
        } else if (is_array($value)) {
            return self::MAP; // FIXME: Could determine if this is a list (numeric indexes)
        } else if (is_bool($value)) {
            return self::BOOLEAN;
        } else if (is_int($value)) {
            return self::INT;
        } else if (is_string($value)) {
            return self::STRING;
        } else if (is_numeric($value)) {
            return self::FLOAT;
        } else {
            return self::MIXED;
        }
    }
}

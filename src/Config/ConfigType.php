<?php

/**
 * Describe all handled data types
 */
final class ConfigType
{
    /**
     * Type is unspecified by the schema entry (per default typing is dynamic)
     */
    const UNKNOWN = -1;

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
     * Type is string
     */
    const STRING = 30;

    /**
     * Type is ordered list (keys are not revelant)
     */
    const LISTING = 10;

    /**
     * Type is an hashmap (keys are revelant)
     */
    const HASHMAP = 11;

    /**
     * Dynamically get type from value
     */
    public static function getType($value)
    {
        if (null === $value) {
            return self::UNKNOWN;
        } else if (is_array($value)) {
            return self::HASHMAP; // FIXME: Could determine if this is a list (numeric indexes)
        } else if (is_bool($value)) {
            return self::BOOLEAN;
        } else if (is_int($value)) {
            return self::INT;
        } else if (is_string($value)) {
            return self::STRING;
        } else if (is_numeric($value)) {
            return self::FLOAT;
        } else {
            return self::UNKNOWN;
        }
    }
}

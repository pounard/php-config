<?php

namespace Config\Impl;

use Config\ConfigType;

/**
 * Very simple serializer implementation that suits for most simple backends
 */
class ValueSerializer
{
    /**
     * Serialize value
     *
     * @param mixed $value
     * @param int $type
     *
     * @return string
     */
    public function serialize($value, $type = ConfigType::MIXED)
    {
        switch ($type) {

            case ConfigType::BOOLEAN:
            case ConfigType::INT:
            case ConfigType::FLOAT:
            case ConfigType::DECIMAL:
            case ConfigType::STRING:
                return $value;

            default:
               return serialize($value);
        }
    }

    /**
     * Unserialize value
     *
     * @param string $data
     * @param int $type
     *
     * @return mixed
     */
    public function unserialize($data, $type)
    {
        switch ($type) {

            case ConfigType::BOOLEAN:
                return (bool)$data;

            case ConfigType::INT:
                return (int)$data;

            case ConfigType::FLOAT:
                return (float)$data;

            case ConfigType::DECIMAL:
                return (float)$data;

            case ConfigType::STRING:
                return (string)$data;

            default:
                if ($value = unserialize($data)) {
                    return $value;
                }
                throw new \RuntimeException("Unserialization error");
        }
    }
}

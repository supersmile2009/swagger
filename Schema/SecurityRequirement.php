<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ArrayAccess;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class SecurityRequirement implements \ArrayAccess
{
    use ArrayAccess;

    private $data;

    /**
     * Each name must correspond to a security scheme which is declared in the Security Definitions.
     * If the security scheme is of type "oauth2" or "openIdConnect", then the value is a list of scope names
     * required for the execution. For other security scheme types, the array MUST be empty.
     *
     * @param $name
     * @param string[] $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @JMS\HandlerCallback("json", direction="serialization")
     */
    public function serialize(JsonSerializationVisitor $visitor)
    {
        return $this->data;
    }

    /**
     * @JMS\HandlerCallback("json", direction="deserialization")
     */
    public function deserialize(JsonDeserializationVisitor $visitor, $data)
    {
        $this->data = $data;
    }
} 

<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ArrayAccess;
use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class representing any arbitrary value for OpenApi schema. An equivalent of PHP's mixed.
 *
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Any implements \ArrayAccess
{
    use ArrayAccess;

    private $data;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @JMS\HandlerCallback("json", direction="serialization")
     *
     * @param JsonSerializationVisitor $visitor
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor)
    {
        return $this->data;
    }

    /**
     * @JMS\HandlerCallback("json", direction="deserialization")
     *
     * @param JsonDeserializationVisitor $visitor
     * @param $data
     */
    public function deserialize(JsonDeserializationVisitor $visitor, $data)
    {
        $this->data = $data;
    }

    /**
     * @param $value
     * @param bool $valueIsArray
     *
     * @return Any[]|Any
     */
    public static function convert($value, $valueIsArray = false)
    {
        if (null === $value) {
            return $value;
        }

        if ($valueIsArray && \is_array($value)) {
            foreach ($value as $key => $data) {
                $value[$key] = static::convert($data);
            }
            return $value;
        }

        if ($value instanceof self) {
            return $value;
        }

        return new static($value);
    }
}

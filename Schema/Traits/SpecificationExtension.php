<?php

namespace Draw\Swagger\Schema\Traits;

use Draw\Swagger\Schema\Any;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
trait SpecificationExtension
{
    /**
     * @var Any[]
     *
     * @JMS\Exclude()
     */
    private $customProperties = [];

    public function setCustomProperties(array $customProperties)
    {
        $this->customProperties = Any::convert($customProperties, true);
    }

    public function getCustomProperties()
    {
        return $this->customProperties;
    }

    public function setCustomProperty($key, $value)
    {
        $this->customProperties[$key] = Any::convert($value);
    }

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getCustomProperty($key)
    {
        return isset($this->customProperties[$key]) ? $this->customProperties[$key] : null;
    }

    public function removeCustomProperty($key)
    {
        unset($this->customProperties[$key]);
    }
} 

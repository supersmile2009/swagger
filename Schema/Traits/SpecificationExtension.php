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
        $this->customProperties = $customProperties;
    }

    public function getCustomProperties()
    {
        return $this->customProperties;
    }

    public function setCustomProperty($key, $value)
    {
        $this->customProperties[$key] = $value;
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

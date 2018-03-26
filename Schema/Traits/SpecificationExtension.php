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

    /**
     * @param Any[]|mixed[] $customProperties
     */
    public function setCustomProperties(array $customProperties): void
    {
        $this->customProperties = $customProperties;
    }

    public function getCustomProperties(): array
    {
        return $this->customProperties;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setCustomProperty(string $key, $value): void
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
        return $this->customProperties[$key] ?? null;
    }

    public function removeCustomProperty($key): void
    {
        unset($this->customProperties[$key]);
    }
} 

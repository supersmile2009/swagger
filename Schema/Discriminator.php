<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Discriminator implements \ArrayAccess
{
    use ClassPropertiesArrayAccess;

    /**
     * REQUIRED. The name of the property in the payload that will hold the discriminator value.
     *
     * @var string
     *
     * @Assert\NotNull()
     *
     * @JMS\Type("string")
     */
    public $propertyName;

    /**
     * An object to hold mappings between payload values and schema names or references.
     *
     * @var string[]
     *
     * @JMS\Type("array<string, string>")
     */
    public $mapping;
}

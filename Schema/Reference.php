<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ArrayAccess;
use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Reference implements \ArrayAccess
{
    use ClassPropertiesArrayAccess;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("$ref")
     */
    public $ref;
} 

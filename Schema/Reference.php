<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use JMS\Serializer\Annotation as JMS;

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

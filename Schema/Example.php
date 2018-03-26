<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Example implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $summary;

    /**
     * Long description for the example. CommonMark syntax MAY be used for rich text representation.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * Embedded literal example. The value field and externalValue field are mutually exclusive.
     * To represent examples of media types that cannot naturally represented in JSON or YAML,
     * use a string value to contain the example, escaping where necessary.
     *
     * @var Any
     *
     * @Assert\Expression("this.value === null || this.externalValue === null")
     *
     * @JMS\Type("Draw\Swagger\Schema\Any")
     */
    public $value;

    /**
     * A URL that points to the literal example.
     * This provides the capability to reference examples that cannot easily be included in JSON or YAML documents.
     * The 'value' field and 'externalValue' field are mutually exclusive.
     *
     * @var string
     *
     * @Assert\Valid()
     *
     * @JMS\Type("string")
     */
    public $externalValue;
}

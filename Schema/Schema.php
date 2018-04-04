<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Schema implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $title;

    /**
     * @var number
     *
     * @JMS\Type("double")
     */
    public $multipleOf;

    /**
     * @var number
     *
     * @JMS\Type("double")
     */
    public $maximum;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $exclusiveMaximum;

    /**
     * @var number
     *
     * @JMS\Type("double")
     */
    public $minimum;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("exclusiveMinimum")
     */
    public $exclusiveMinimum;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxLength")
     */
    public $maxLength;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minLength")
     */
    public $minLength;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $pattern;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxItems")
     */
    public $maxItems;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minItems")
     */
    public $minItems;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("uniqueItems")
     */
    public $uniqueItems;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxProperties")
     */
    public $maxProperties;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minProperties")
     */
    public $minProperties;

    /**
     * @var string[]
     *
     * @JMS\Type("array<string>")
     * @JMS\SkipWhenEmpty()
     */
    public $required = [];

    /**
     * @var Any[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Any>")
     */
    public $enum;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @var Schema[]|Reference[]
     *
     * @JMS\SerializedName("allOf")
     */
    public $allOf;

    /**
     * @var Schema[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Schema>")
     * @JMS\SerializedName("oneOf")
     */
    public $oneOf;

    /**
     * @var Schema[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Schema>")
     * @JMS\SerializedName("anyOf")
     */
    public $anyOf;

    /**
     * @var Schema[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Schema>")
     * @JMS\SerializedName("not")
     */
    public $not;

    /**
     * @var Schema|Reference
     */
    public $items;

    /**
     * @var Schema[]|Reference[]
     *
     * @JMS\SkipWhenEmpty()
     */
    public $properties = [];

    /**
     * @var Schema
     *
     * @JMS\Type("Draw\Swagger\Schema\Schema")
     * @JMS\SerializedName("additionalProperties")
     */
    public $additionalProperties;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $format;

    /**
     * @var Any
     *
     * @JMS\Type("Draw\Swagger\Schema\Any")
     */
    public $default;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("nullable")
     */
    public $nullable = false;

    /**
     * The discriminator attribute is legal only when using one of the composite keywords oneOf, anyOf, allOf.
     *
     * @var Discriminator
     *
     * @Assert\Expression(
     *     expression="value === null || this.oneOf !== null || this.anyOf !== null || this.allOf !== null",
     *     message="The discriminator attribute is legal only when using one of the composite keywords oneOf, anyOf, allOf."
     * )
     *
     * @JMS\Type("Draw\Swagger\Schema\Discriminator")
     */
    public $discriminator;

    /**
     * Relevant only for Schema "properties" definitions. Declares the property as "read only".
     * This means that it MAY be sent as part of a response but MUST NOT be sent as part of the request.
     * Properties marked as readOnly being true SHOULD NOT be in the required list of the defined schema.
     * Default value is false.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("readOnly")
     */
    public $readOnly;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $writeOnly = false;

    /**
     * This MAY be used only on properties schemas.
     * It has no effect on root schemas.
     * Adds Additional metadata to describe the XML representation format of this property.
     *
     * @var Xml
     *
     * @JMS\Type("Draw\Swagger\Schema\Xml")
     */
    public $xml;

    /**
     * Additional external documentation.
     *
     * @var ExternalDocumentation
     *
     * @JMS\Type("Draw\Swagger\Schema\ExternalDocumentation")
     * @JMS\SerializedName("externalDocs")
     */
    public $externalDocs;

    /**
     * A free-form property to include a an example of an instance for this schema.
     *
     * @var Any
     * @JMS\Type("Draw\Swagger\Schema\Any")
     */
    public $example;

    /**
     * Specifies that a schema is deprecated and SHOULD be transitioned out of usage.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $deprecated = false;

    /**
     * @JMS\PreSerialize()
     */
    public function preSerialize()
    {
        $this->default = Any::convert($this->default);
        $this->example = Any::convert($this->example);
        $this->enum = Any::convert($this->enum, true);
    }
}

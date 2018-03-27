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
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $title;

    /**
     * @var number
     *
     * @JMS\Type("double")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $multipleOf;

    /**
     * @var number
     *
     * @JMS\Type("double")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $maximum;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $exclusiveMaximum;

    /**
     * @var number
     *
     * @JMS\Type("double")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $minimum;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("exclusiveMinimum")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $exclusiveMinimum;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxLength")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $maxLength;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minLength")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $minLength;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $pattern;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxItems")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $maxItems;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minItems")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $minItems;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("uniqueItems")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $uniqueItems;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxProperties")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $maxProperties;

    /**
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minProperties")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $minProperties;

    /**
     * @var string[]
     *
     * @JMS\Type("array<string>")
     * @JMS\SkipWhenEmpty()
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $required = [];

    /**
     * @var Any[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Any>")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $enum;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Exclude(if="object.ref !== null")
     *
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @var Schema[]|Reference[]
     *
     * @JMS\SerializedName("allOf")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $allOf;

    /**
     * @var Schema[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Schema>")
     * @JMS\SerializedName("oneOf")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $oneOf;

    /**
     * @var Schema[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Schema>")
     * @JMS\SerializedName("anyOf")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $anyOf;

    /**
     * @var Schema[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Schema>")
     * @JMS\SerializedName("not")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $not;

    /**
     * @var Schema|Reference
     *
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $items;

    /**
     * @var Schema[]|Reference[]
     *
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $properties;

    /**
     * @var Schema
     *
     * @JMS\Type("Draw\Swagger\Schema\Schema")
     * @JMS\SerializedName("additionalProperties")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $additionalProperties;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $description;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $format;

    /**
     * @var Any
     *
     * @JMS\Type("Draw\Swagger\Schema\Any")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $default;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("nullable")
     * @JMS\Exclude(if="object.ref !== null")
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
     * @JMS\Exclude(if="object.ref !== null")
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
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $readOnly;

    /**
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Exclude(if="object.ref !== null")
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
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $xml;

    /**
     * Additional external documentation.
     *
     * @var ExternalDocumentation
     *
     * @JMS\Type("Draw\Swagger\Schema\ExternalDocumentation")
     * @JMS\SerializedName("externalDocs")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $externalDocs;

    /**
     * A free-form property to include a an example of an instance for this schema.
     *
     * @var Any
     * @JMS\Type("Draw\Swagger\Schema\Any")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $example;

    /**
     * Specifies that a schema is deprecated and SHOULD be transitioned out of usage.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $deprecated = false;

    /**
     * @deprecated
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("$ref")
     */
    public $ref;

    /**
     * @deprecated
     * Serializer groups extracted from annotations
     *
     * @var array
     *
     * @JMS\Type("array<string>")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $serializerGroups;

    /**
     * @deprecated
     * Parent class alias. Base class alias is stored here for child classes that use discriminator map.
     *
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Exclude(if="object.ref !== null")
     */
    public $parentAlias;

    /**
     * @deprecated
     * Description of deprecation
     *
     * @var boolean
     *
     * @JMS\Type("string")
     * @JMS\Exclude(if="object.ref !== null || object.deprecated === false")
     */
    public $deprecationDescription;

    /**
     * @deprecated
     * Description of deprecation
     *
     * @var boolean
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("x-deprecationDescription")
     * @JMS\Exclude(if="object.ref !== null || object.deprecated !== true")
     */
    public $xDeprecationDescription;

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

<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#headerObject
 *
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class Header implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * A short description of the header.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * The type of the object. The value MUST be one of "string",
     *
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\Choice({"string","number","integer","boolean","array"})
     * @JMS\Type("string")
     */
    public $type;

    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $format;

    /**
     * Required if type is "array". Describes the type of items in the array.
     *
     * @var Items
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Items")
     */
    public $items;

    /**
     * Determines the format of the array if type array is used. Possible values are:
     *
     * csv - comma separated values foo,bar.
     * ssv - space separated values foo bar.
     * tsv - tab separated values foo\tbar.
     * pipes - pipe separated values foo|bar.
     *
     * Default value is csv.
     *
     * @var string
     *
     * @Assert\Choice({"csv","ssv","tsv","pipes"})
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("collectionFormat")
     */
    public $collectionFormat;

    /**
     * Sets a default value to the data type. The type of the value depends on the defined type.
     *
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor101.
     *
     * @var Any
     *
     * @JMS\Type("Draw\Swagger\Schema\Any")
     */
    public $default;

    /**
     * @see  http://json-schema.org/latest/json-schema-validation.html#anchor17
     *
     * @var integer
     *
     * @JMS\Type("integer")
     */
    public $maximum;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor17
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("exclusiveMaximum")
     */
    public $exclusiveMaximum;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor21
     *
     * @var integer
     *
     * @JMS\Type("integer")
     */
    public $minimum;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor21
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("exclusiveMinimum")
     */
    public $exclusiveMinimum;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor26
     *
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxLength")
     */
    public $maxLength;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor29
     *
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minLength")
     */
    public $minLength;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor33
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $pattern;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor42
     *
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("maxItems")
     */
    public $maxItems;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor45
     *
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("minItems")
     */
    public $minItems;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor49
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     * @JMS\SerializedName("uniqueItems")
     */
    public $uniqueItems;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor76
     *
     * @var Any[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Any>")
     */
    public $enum;

    /**
     * @see http://json-schema.org/latest/json-schema-validation.html#anchor14
     *
     * @var integer
     *
     * @JMS\Type("integer")
     * @JMS\SerializedName("multipleOf")
     */
    public $multipleOf;

    /**
     * @JMS\PreSerialize()
     */
    public function preSerialize()
    {
        $this->default = Any::convert($this->default);
        $this->enum = Any::convert($this->enum, true);
    }
} 

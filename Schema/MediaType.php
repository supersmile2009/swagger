<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class MediaType implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * The schema defining the type used for the request body.
     *
     * @var Schema|Reference
     */
    public $schema;

    /**
     * Example of the media type.
     * The example SHOULD match the specified schema and encoding properties if present.
     * The example object is mutually exclusive of the examples object.
     * Furthermore, if referencing a schema which contains an example,
     * the example value SHALL override the example provided by the schema.
     *
     * @var Any
     *
     * @JMS\Type("Draw\Swagger\Schema\Any")
     * @JMS\SkipWhenEmpty()
     */
    public $example;

    /**
     * Examples of the media type.
     * Each example SHOULD contain a value in the correct format as specified in the parameter encoding.
     * The examples object is mutually exclusive of the example object.
     * Furthermore, if referencing a schema which contains an example,
     * the examples value SHALL override the example provided by the schema.
     *
     * @var Example[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Example>")
     * @JMS\SkipWhenEmpty()
     */
    public $examples = [];

    /**
     * A map between a property name and its encoding information.
     * The key, being the property name, MUST exist in the schema as a property.
     * The encoding object SHALL only apply to requestBody objects
     * when the media type is 'multipart' or 'application/x-www-form-urlencoded'.
     *
     * @var Encoding
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\V3\Encoding>")
     * @JMS\SkipWhenEmpty()
     */
    public $encoding = [];

    /**
     * @deprecated
     * A map between a property name and its encoding information.
     * The key, being the property name, MUST exist in the schema as a property.
     * The encoding object SHALL only apply to requestBody objects
     * when the media type is 'multipart' or 'application/x-www-form-urlencoded'.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $ref;

    /**
     * @JMS\PreSerialize()
     */
    public function preSerialize()
    {
        $this->example = Any::convert($this->example);
    }
}

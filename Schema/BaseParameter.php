<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 *
 * @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#parameterObject
 *
 * @JMS\Discriminator(
 *      field="in",
 *      map={
 *          "path":"Draw\Swagger\Schema\PathParameter",
 *          "query":"Draw\Swagger\Schema\QueryParameter",
 *          "header":"Draw\Swagger\Schema\HeaderParameter",
 *          "cookie":"Draw\Swagger\Schema\CookieParameter"
 *      }
 * )
 */
abstract class BaseParameter implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * The name of the parameter. Parameter names are case sensitive.
     *  - If in is "path", the name field MUST correspond to the associated path segment from the path field in the Paths Object.
     *    See Path Templating for further information.
     *
     *  - For all other cases, the name corresponds to the parameter name used based on the in property.
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @JMS\Type("string")
     */
    public $name;

    /**
     * A brief description of the parameter. This could contain examples of use.
     * GFM syntax can be used for rich text representation.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * Determines whether this parameter is mandatory.
     * If the parameter is in "path", this property is required and its value MUST be true.
     * Otherwise, the property MAY be included and its default value is false.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $required = false;

    /**
     * Specifies that a parameter is deprecated and SHOULD be transitioned out of usage.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $deprecated = false;

    /**
     * Describes how the parameter value will be serialized depending on the type of the parameter value.
     * Default values (based on value of in):
     * for query - form; for path - simple; for header - simple; for cookie - form.
     *
     * @var string
     *
     * @Assert\Choice({"matrix", "label", "form", "simple", "spaceDelimited", "pipeDelimited", "deepObject"})
     *
     * @JMS\Type("string")
     */
    public $style;

    /**
     * When this is true, parameter values of type array or object generate separate parameters
     * for each value of the array or key-value pair of the map.
     * For other types of parameters this property has no effect.
     * When style is form, the default value is true. For all other styles, the default value is false.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $explode;

    /**
     * The schema defining the type used for the parameter.
     *
     * @var Schema
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Schema")
     */
    public $schema;

    /**
     * Example of the media type.
     * The example SHOULD match the specified schema and encoding properties if present.
     * The example object is mutually exclusive of the examples object.
     * Furthermore, if referencing a schema which contains an example,
     * the example value SHALL override the example provided by the schema.
     * To represent examples of media types that cannot naturally be represented in JSON or YAML,
     * a string value can contain the example with escaping where necessary.
     *
     * @var Any
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Any")
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
     * A map containing the representations for the parameter.
     * The key is the media type and the value describes it. The map MUST only contain one entry.
     *
     * @var MediaType[]
     *
     * @Assert\Count(max="1")
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\MediaType>")
     */
    public $content;

    /**
     * @Assert\Callback()
     *
     * @param ExecutionContextInterface $executionContext
     */
    public function validate(ExecutionContextInterface $executionContext)
    {
        if ($this->schema !== null && $this->content !== null) {
            $executionContext
                ->buildViolation(
                    'A parameter MUST contain either a schema property, or a content property, but not both.'
                )
                ->atPath('schema')
                ->addViolation();
        }
        if ($this->schema !== null || $this->content !== null) {
            $executionContext
                ->buildViolation(
                    'A parameter doesn\'t contain no schema property nor content property. One of these parameters should be present.'
                )
                ->atPath('schema')
                ->addViolation();
        }
        if ($this->example !== null && empty($this->examples) === false) {
            $executionContext
                ->buildViolation(
                    'The example object is mutually exclusive of the examples object, but both are defined.'
                )
                ->atPath('example')
                ->addViolation();
        }
    }
} 

<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Encoding implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * The Content-Type for encoding a specific property.
     * Default value depends on the property type:
     *      for 'string' with 'format' being 'binary' – 'application/octet-stream';
     *      for other primitive types – 'text/plain';
     *      for 'object' - 'application/json';
     *      for 'array' – the default is defined based on the inner type.
     * The value can be a specific media type (e.g. 'application/json'),
     * a wildcard media type (e.g. 'image/*'), or a comma-separated list of the two types.
     *
     * @var string
     *
     * @Assert\Valid()
     *
     * @JMS\Type("string")
     */
    public $contentType;

    /**
     * A map allowing additional information to be provided as headers, for example 'Content-Disposition'.
     * 'Content-Type' is described separately and SHALL be ignored in this section.
     * This property SHALL be ignored if the request body media type is not a 'multipart'.
     *
     * @var Header[]|Reference
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Schema>")
     */
    public $headers;

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
     * Determines whether the parameter value SHOULD allow reserved characters,
     * as defined by RFC3986 `:/?#[]@!$&'()*+,;=` to be included without percent-encoding.
     * This property only applies to parameters with an in value of query. The default value is false.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $allowReserved = false;
}

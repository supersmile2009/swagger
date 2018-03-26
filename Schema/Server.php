<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Any;
use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Server implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * Required. An URL to the target host. This URL supports Server Variables and MAY be relative,
     * to indicate that the host location is relative to the location where the OpenAPI document is being served.
     * Variable substitutions will be made when a variable is named in {brackets}.
     *
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\Url()
     *
     * @JMS\Type("string")
     */
    public $url;

    /**
     * An optional string describing the host designated by the URL.
     * CommonMark syntax MAY be used for rich text representation.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * A map between a variable name and its value. The value is used for substitution in the server's URL template.
     *
     * @var ServerVariable[]
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\ServerVariable>")
     */
    public $variables;
} 

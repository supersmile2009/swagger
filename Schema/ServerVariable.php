<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An object representing a Server Variable for server URL template substitution.
 *
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class ServerVariable implements SpecificationExtensionSupportInterface
{
    use SpecificationExtension;

    /**
     * Required. An URL to the target host. This URL supports Server Variables and MAY be relative,
     * to indicate that the host location is relative to the location where the OpenAPI document is being served.
     * Variable substitutions will be made when a variable is named in {brackets}.
     *
     * @var string[]
     *
     * @JMS\Type("array<string>")
     */
    public $enum;

    /**
     * Required. An URL to the target host. This URL supports Server Variables and MAY be relative,
     * to indicate that the host location is relative to the location where the OpenAPI document is being served.
     * Variable substitutions will be made when a variable is named in {brackets}.
     *
     * @var string
     *
     * @Assert\NotNull()
     *
     * @JMS\Type("string")
     */
    public $default;

    /**
     * An optional string describing the host designated by the URL.
     * CommonMark syntax MAY be used for rich text representation.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;
} 

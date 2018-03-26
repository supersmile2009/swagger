<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * License information for the exposed API.
 *
 * @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#licenseObject
 *
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class License implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * The license name used for the API.
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @JMS\Type("string")
     */
    public $name;

    /**
     * A URL to the license used for the API. MUST be in the format of a URL.
     *
     * @var string
     *
     * @Assert\Url()
     * @JMS\Type("string")
     */
    public $url;
}

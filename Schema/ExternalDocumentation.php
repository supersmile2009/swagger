<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class ExternalDocumentation implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * A short description of the target documentation. GFM syntax can be used for rich text representation.
     *
     * @see https://help.github.com/articles/github-flavored-markdown/
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * The URL for the target documentation. Value MUST be in the format of a URL.
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Url()
     * @JMS\Type("string")
     */
    public $url;
} 

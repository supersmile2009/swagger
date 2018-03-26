<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ArrayAccess;
use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @Annotation
 * @Target({"METHOD"})
 *
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class Tag implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * The name of the tag.
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @JMS\Type("string")
     */
    public $name;

    /**
     * A short description for the tag.
     * GFM syntax can be used for rich text representation.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * Additional external documentation for this tag.
     *
     * @var ExternalDocumentation
     *
     * @JMS\Type("Draw\Swagger\Schema\ExternalDocumentation")
     * @JMS\SerializedName("externalDocs")
     */
    public $externalDocs;
}

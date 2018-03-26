<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class Response implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * @var Header[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string,Draw\Swagger\Schema\Header>")
     */
    public $headers;

    /**
     * @var MediaType[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\MediaType>")
     */
    public $content;

    /**
     * @var Link[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Link>")
     */
    public $links;
} 

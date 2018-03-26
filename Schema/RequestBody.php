<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 *
 * @see https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md#requestBodyObject
 */
class RequestBody implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * A brief description of the request body. This could contain examples of use.
     * CommonMark syntax MAY be used for rich text representation.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description = '';

    /**
     * REQUIRED. The content of the request body.
     * The key is a media type or media type range and the value describes it.
     * For requests that match multiple keys, only the most specific key is applicable. e.g. text/plain overrides text/*
     *
     * @var MediaType[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\MediaType>")
     */
    public $content;

    /**
     * Specifies that a parameter is deprecated and SHOULD be transitioned out of usage.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $required = false;
}

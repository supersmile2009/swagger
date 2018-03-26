<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class OpenApi implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * REQUIRED. This string MUST be the semantic version number of the OpenAPI Specification version
     * that the OpenAPI document uses. The openapi field SHOULD be used by tooling specifications and clients
     * to interpret the OpenAPI document. This is not related to the API "info.version" string.
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @JMS\Type("string")
     */
    public $openapi = '3.0.0';

    /**
     * REQUIRED. Provides metadata about the API. The metadata MAY be used by tooling as required.
     *
     * @var Info
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     * @JMS\Type("Draw\Swagger\Schema\Info")
     */
    public $info;

    /**
     * An array of Server Objects, which provide connectivity information to a target server.
     * If the servers property is not provided, or is an empty array, the default value would be
     * a Server Object with a url value of "/".
     *
     * @var Server[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Server>")
     */
    public $servers;

    /**
     * The available paths and operations for the API.
     *
     * @var PathItem[]
     *
     * @Assert\NotBlank()
     * @Assert\Valid()
     * @JMS\Type("array<string,Draw\Swagger\Schema\PathItem>")
     */
    public $paths;

    /**
     * An object to hold data types produced and consumed by operations.
     *
     * @var Components
     *
     * @Assert\Valid()
     * @JMS\Type("Draw\Swagger\Schema\Components")
     */
    public $components;

    /**
     * A declaration of which security schemes are applied for the API as a whole.
     * The list of values describes alternative security schemes that can be used
     * (that is, there is a logical OR between the security requirements).
     * Individual operations can override this definition.
     *
     * @var SecurityRequirement[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<Draw\Swagger\Schema\SecurityRequirement>")
     */
    public $security;

    /**
     * A list of tags used by the specification with additional metadata.
     * The order of the tags can be used to reflect on their order by the parsing tools.
     * Not all tags that are used by the Operation Object must be declared.
     * The tags that are not declared may be organized randomly or based on the tools' logic.
     * Each tag name in the list MUST be unique.
     *
     * @var Tag[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Tag>")
     */
    public $tags;

    /**
     * Additional external documentation.
     *
     * @var ExternalDocumentation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\ExternalDocumentation")
     * @JMS\SerializedName("externalDocs")
     */
    public $externalDocs;

    public function __construct()
    {
        $this->components = new Components();
    }

}

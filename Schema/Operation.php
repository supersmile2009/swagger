<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Operation implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * A list of tags for API documentation control.
     * Tags can be used for logical grouping of operations by resources or any other qualifier.
     *
     * @var string[]
     *
     * @JMS\Type("array<string>")
     */
    public $tags;

    /**
     * A short summary of what the operation does.
     * For maximum readability in the swagger-ui, this field SHOULD be less than 120 characters.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $summary;

    /**
     * A verbose explanation of the operation behavior. GFM syntax can be used for rich text representation.
     *
     * @see https://help.github.com/articles/github-flavored-markdown/
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * Additional external documentation for this operation.
     *
     * @var ExternalDocumentation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\ExternalDocumentation")
     */
    public $externalDocs;

    /**
     * A friendly name for the operation.
     * The id MUST be unique among all operations described in the API.
     * Tools and libraries MAY use the operation id to uniquely identify an operation.
     *
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("operationId")
     */
    public $operationId;

    /**
     * A list of parameters that are applicable for this operation.
     * If a parameter is already defined at the Path Item, the new definition will override it, but can never remove it.
     * The list MUST NOT include duplicated parameters.
     * A unique parameter is defined by a combination of a name and location.
     * The list can use the Reference Object to link to parameters that are defined at the Swagger Object's parameters.
     * There can be one "body" parameter at most.
     *
     * @var BaseParameter[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<Draw\Swagger\Schema\BaseParameter>")
     */
    public $parameters = [];

    /**
     * The request body applicable for this operation. The requestBody is only supported in HTTP methods
     * where the HTTP 1.1 specification RFC7231 has explicitly defined semantics for request bodies.
     * In other cases where the HTTP spec is vague, requestBody SHALL be ignored by consumers.
     *
     * @var RequestBody|Reference
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\RequestBody")
     */
    public $requestBody;

    /**
     * REQUIRED. The list of possible responses as they are returned from executing this operation.
     *
     * @var Response[]
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     *
     * @JMS\Type("array<string,Draw\Swagger\Schema\Response>")
     */
    public $responses = [];

    /**
     * A map of possible out-of band callbacks related to the parent operation.
     * The key is a unique identifier for the Callback Object. Each value in the mapis a Callback Object
     * that describes a request that may be initiated by the API provider and the expected responses.
     * The key value used to identify the callback object is an expression, evaluated at runtime,
     * that identifies a URL to use for the callback operation.
     *
     * @var Callback
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Callback>")
     */
    public $callbacks;

    /**
     * Declares this operation to be deprecated.
     * Usage of the declared operation should be refrained.
     * Default value is false.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $deprecated = false;

    /**
     * @deprecated
     * Deprecation description
     *
     * @var boolean
     *
     * @JMS\Type("string")
     * @JMS\Exclude(if="object.deprecated === false")
     */
    public $deprecationDescription;

    /**
     * Deprecation description
     *
     * @var boolean
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("x-DeprecationDescription")
     * @JMS\Exclude(if="object.deprecated === false")
     */
    public $xDeprecationDescription;

    /**
     * A declaration of which security schemes are applied for this operation.
     * The list of values describes alternative security schemes that can be used
     * (that is, there is a logical OR between the security requirements).
     * This definition overrides any declared top-level security.
     * To remove a top-level security declaration, an empty array can be used.
     *
     * @var SecurityRequirement[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<Draw\Swagger\Schema\SecurityRequirement>")
     */
    public $security;

    /**
     * An alternative server array to service this operation.
     * If an alternative server object is specified at the Path Item Object or Root level,
     * it will be overridden by this value.
     *
     * @var Server[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Server>")
     */
    public $servers;
} 

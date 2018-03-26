<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Link implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * A relative or absolute reference to an OAS operation.
     * This field is mutually exclusive of the operationId field, and MUST point to an Operation Object.
     * Relative operationRef values MAY be used to locate an existing Operation Object in the OpenAPI definition.
     *
     * @var string
     *
     * @Assert\Expression(
     *     expression="this.operationRef === null || this.operationId === null",
     *     message="OperationRef and operationId properties are mutually exclusive. Only one field should be defined."
     * )
     *
     * @JMS\Type("string")
     */
    public $operationRef;

    /**
     * The name of an existing, resolvable OAS operation, as defined with a unique operationId.
     * This field is mutually exclusive of the operationRef field.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $operationId;

    /**
     * A map representing parameters to pass to an operation
     * as specified with operationId or identified via operationRef.
     * The key is the parameter name to be used, whereas the value can be a constant or
     * an expression to be evaluated and passed to the linked operation.
     * The parameter name can be qualified using the parameter location [{in}.]{name} for operations that use
     * the same parameter name in different locations (e.g. path.id).
     *
     * @var string[]|Any[]
     */
    public $parameters = [];

    /**
     * A literal value or {expression} to use as a request body when calling the target operation.
     *
     * @var Any[]|string
     */
    public $requestBody = [];

    /**
     * A description of the link. CommonMark syntax MAY be used for rich text representation.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * A server object to be used by the target operation.
     *
     * @var Server
     *
     * @JMS\Type("Draw\Swagger\Schema\V3\Server")
     */
    public $server;
} 

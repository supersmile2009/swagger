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
class PathItem implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\SerializedName("$ref")
     */
    public $ref;

    /**
     * An optional, string summary, intended to apply to all operations in this path.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $summary;

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
     * A definition of a GET operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Operation")
     */
    public $get;

    /**
     * A definition of a PUT operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Operation")
     */
    public $put;

    /**
     * A definition of a POST operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Operation")
     */
    public $post;

    /**
     * A definition of a DELETE operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Operation")
     */
    public $delete;

    /**
     * A definition of a OPTIONS operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Operation")
     */
    public $options;

    /**
     * A definition of a HEAD operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Operation")
     */
    public $head;

    /**
     * A definition of a PATCH operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Operation")
     */
    public $patch;

    /**
     * A definition of a TRACE operation on this path.
     *
     * @var Operation
     *
     * @Assert\Valid()
     *
     * @JMS\Type("Draw\Swagger\Schema\Operation")
     */
    public $trace;

    /**
     * An alternative server array to service all operations in this path.
     *
     * @var Server[]
     *
     * @JMS\Type("array<Draw\Swagger\Schema\Server>")
     */
    public $servers;

    /**
     * A list of parameters that are applicable for all the operations described under this path.
     * These parameters can be overridden at the operation level, but cannot be removed there.
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
    public $parameters;

    /**
     * @return Operation[]
     */
    public function getOperations()
    {
        static $methods = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];
        $operations = [];
        foreach($methods as $method) {
            if ($this->{$method} instanceof Operation) {
                $operations[$method] = $this->{$method};
            }
        }

        return $operations;
    }
} 

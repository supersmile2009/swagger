<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Holds a set of reusable objects for different aspects of the OAS.
 * All objects defined within the components object will have no effect on the API
 * unless they are explicitly referenced from properties outside the components object.
 *
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class Components implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * An object to hold reusable Schema Objects.
     *
     * @var Schema[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Schema>")
     * @JMS\SkipWhenEmpty()
     */
    public $schemas = [];

    /**
     * An object to hold reusable Response Objects.
     *
     * @var Response[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Response>")
     * @JMS\SkipWhenEmpty()
     */
    public $responses = [];

    /**
     * An object to hold reusable Parameter Objects.
     *
     * @var BaseParameter[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\BaseParameter>")
     * @JMS\SkipWhenEmpty()
     */
    public $parameters = [];

    /**
     * An object to hold reusable Example Objects.
     *
     * @var Example[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Example>")
     * @JMS\SkipWhenEmpty()
     */
    public $examples = [];

    /**
     * An object to hold reusable Request Body Objects.
     *
     * @var RequestBody[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\RequestBody>")
     * @JMS\SkipWhenEmpty()
     */
    public $requestBodies = [];

    /**
     * An object to hold reusable Header Objects.
     *
     * @var Header[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Header>")
     * @JMS\SkipWhenEmpty()
     */
    public $headers = [];

    /**
     * An object to hold reusable Security Scheme Objects.
     *
     * @var SecurityScheme[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\SecurityScheme>")
     * @JMS\SkipWhenEmpty()
     */
    public $securitySchemes = [];

    /**
     * An object to hold reusable Link Objects.
     *
     * @var Link[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Link>")
     * @JMS\SkipWhenEmpty()
     */
    public $links = [];

    /**
     * An object to hold reusable Callback Objects.
     *
     * @var Callback[]|Reference[]
     *
     * @Assert\Valid()
     *
     * @JMS\Type("array<string, Draw\Swagger\Schema\Callback>")
     * @JMS\SkipWhenEmpty()
     */
    public $callbacks = [];

    public function hasSchema($name)
    {
        $name = $this->sanitizeReferenceName($name);

        return array_key_exists($name, $this->schemas);
    }

    public function addSchema($name, Schema $schema)
    {
        $name = $this->sanitizeReferenceName($name);
        $this->schemas[$name] = $schema;
        return $this->getSchemaReference($name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getSchemaReference($name)
    {
        return '#/components/schemas/' . $this->sanitizeReferenceName($name);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function sanitizeReferenceName($name)
    {
        return trim(str_replace('\\', '/', $name), '/');
    }
}

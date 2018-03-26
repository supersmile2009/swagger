<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ClassPropertiesArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * TODO: Create child classes with relevant properties and discriminator map
 */
class SecurityScheme implements SpecificationExtensionSupportInterface, \ArrayAccess
{
    use SpecificationExtension;
    use ClassPropertiesArrayAccess;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @Assert\Choice({"apiKey", "http", "oauth2", "openIdConnect"})
     *
     * @JMS\Type("string")
     */
    public $type;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    public $description;

    /**
     * REQUIRED. The name of the header, query or cookie parameter to be used.
     *
     * @var string
     *
     * @Assert\Choice({"apiKey", "http", "oauth2", "openIdConnect"})
     * @Assert\Expression(
     *     expression="this.type === 'apiKey' && value === null",
     *     message="Name is required when type is 'apiKey'."
     * )
     * @Assert\Expression(
     *     expression="this.type !== 'apiKey' && value !== null",
     *     message="Name is only valid for 'apiKey' security scheme type."
     * )
     *
     * @JMS\Type("string")
     */
    public $name;

    /**
     * @var string
     *
     * @Assert\Choice({"query", "header", "cookie"})
     * @Assert\Expression(
     *     expression="this.type === 'apiKey' && value === null",
     *     message="In property is required when type is 'apiKey'."
     * )
     * @Assert\Expression(
     *     expression="this.type !== 'apiKey' && value !== null",
     *     message="In property is only valid for 'apiKey' security scheme type."
     * )
     *
     * @JMS\Type("string")
     */
    public $in;

    /**
     * @var string
     *
     * @Assert\Expression(
     *     expression="this.type === 'http' && value === null",
     *     message="Scheme property is required when type is 'http'."
     * )
     * @Assert\Expression(
     *     expression="this.type !== 'http' && value !== null",
     *     message="Scheme property is only valid for 'http' security scheme type."
     * )
     *
     * @JMS\Type("string")
     */
    public $scheme;

    /**
     * @var string
     *
     * @Assert\Expression(
     *     expression="(this.type !== 'http' || this.scheme !== 'bearer') && value !== null",
     *     message="BearerFormat property is only valid for 'http' security scheme type with 'bearer' scheme."
     * )
     *
     * @JMS\Type("string")
     */
    public $bearerFormat;

    /**
     * @var string
     *
     * TODO: Create OAuth Flows Object
     * @JMS\Type("string")
     */
    public $flows;

    /**
     * @var string
     *
     * @Assert\Url()
     * @Assert\Expression(
     *     expression="this.type === 'openIdConnect' && value === null",
     *     message="OpenIdConnectUrl property is required when type is 'openIdConnect'."
     * )
     * @Assert\Expression(
     *     expression="this.type !== 'openIdConnect' && value !== null",
     *     message="OpenIdConnectUrl property is only valid for 'openIdConnect' security scheme type."
     * )
     *
     * @JMS\Type("string")
     */
    public $openIdConnectUrl;
} 

<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ArrayAccess;
use Draw\Swagger\Schema\Traits\SpecificationExtension;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * Contact information for the exposed API.
 *
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class Contact implements SpecificationExtensionSupportInterface
{
    use SpecificationExtension;

    /**
     * The identifying name of the contact person/organization.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    public $name;

    /**
     * The URL pointing to the contact information. MUST be in the format of a URL.
     *
     * @var string
     *
     * @Assert\Url()
     * @JMS\Type("string")
     */
    public $url;

    /**
     * The email address of the contact person/organization. MUST be in the format of an email address.
     *
     * @var string
     *
     * @Assert\Email()
     * @JMS\Type("string")
     */
    public $email;
} 

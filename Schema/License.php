<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ArrayAccess;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 *
 * @Annotation
 */
class License implements \ArrayAccess
{
    use ArrayAccess;

    /**
     * The license name used for the API.
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @JMS\Type("string")
     */
    public $name;

    /**
     * A URL to the license used for the API. MUST be in the format of a URL.
     *
     * @var string
     *
     * @Assert\Url()
     * @JMS\Type("string")
     */
    public $url;
} 
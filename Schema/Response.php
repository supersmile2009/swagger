<?php

namespace Draw\Swagger\Schema;

use Draw\Swagger\Schema\Traits\ArrayAccess;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class Response
{
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

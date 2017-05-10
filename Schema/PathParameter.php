<?php

namespace Draw\Swagger\Schema;

use JMS\Serializer\Annotation as JMS;

class PathParameter extends Parameter
{
    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     *
     * @var string
     * @JMS\Type("boolean")
     */
    public $required = true;
}
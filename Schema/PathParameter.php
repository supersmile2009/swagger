<?php

namespace Draw\Swagger\Schema;

use JMS\Serializer\Annotation as JMS;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class PathParameter extends BaseParameter
{
    /**
     * The extending format for the previously mentioned type. See Data Type Formats for further details.
     *
     * @var string
     * @JMS\Type("boolean")
     */
    public $required = true;
}

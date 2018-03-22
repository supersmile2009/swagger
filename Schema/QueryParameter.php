<?php

namespace Draw\Swagger\Schema;

use JMS\Serializer\Annotation as JMS;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class QueryParameter extends BaseParameter
{

    /**
     * Sets the ability to pass empty-valued parameters. This is valid only for query parameters and allows
     * sending a parameter with an empty value.
     * Default value is false. If style is used, and if behavior is n/a (cannot be serialized),
     * the value of allowEmptyValue SHALL be ignored.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $allowEmptyValue = false;

    /**
     * Determines whether the parameter value SHOULD allow reserved characters,
     * as defined by RFC3986 `:/?#[]@!$&'()*+,;=` to be included without percent-encoding.
     * This property only applies to parameters with an in value of query. The default value is false.
     *
     * @var boolean
     *
     * @JMS\Type("boolean")
     */
    public $allowReserved = false;

}

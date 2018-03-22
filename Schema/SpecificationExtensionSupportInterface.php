<?php

namespace Draw\Swagger\Schema;

/**
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
interface SpecificationExtensionSupportInterface
{
    /**
     * @param array|Any[] $customProperties
     */
    public function setCustomProperties(array $customProperties);

    /**
     * @return Any[]
     */
    public function getCustomProperties();

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setCustomProperty($key, $value);

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function getCustomProperty($key);

    /**
     * @param string $key
     */
    public function removeCustomProperty($key);
}

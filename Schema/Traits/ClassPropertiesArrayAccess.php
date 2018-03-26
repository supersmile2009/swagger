<?php

namespace Draw\Swagger\Schema\Traits;

/**
 * Class ClassPropertiesArrayAccess
 */
trait ClassPropertiesArrayAccess
{
    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset An offset to check for.
     *
     * @return bool          Returns TRUE on success or FALSE on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed         Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset.
     *
     * @throws \Exception
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }
}
